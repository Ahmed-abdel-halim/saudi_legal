<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Models\WalletTransaction;
use App\Services\Workflow\WorkflowOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected WorkflowOrchestrator $orchestrator;
    protected Company $hospital;
    protected Company $payer;
    protected User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orchestrator = $this->app->make(WorkflowOrchestrator::class);

        // Seed demo entities
        $this->hospital = Company::create([
            'name' => 'King Faisal Specialist Hospital (Submitter)',
            'industry' => 'healthcare',
            'size' => 'large',
            'is_supplier' => true,
            'is_verified_provider' => true,
            'cr_number' => 'HOSP-445899',
        ]);

        $this->payer = Company::create([
            'name' => 'Tawuniya Insurance Company (Payer)',
            'industry' => 'insurance',
            'size' => 'large',
            'is_requester' => true,
            'is_verified_provider' => true,
            'cr_number' => 'INS-202688',
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Sarah (HITL Clinical Auditor)',
            'email' => 'doctor@radiif.com',
            'password' => bcrypt('password'),
            'role' => 'expert',
            'expert_domain' => 'healthcare',
            'expert_specialization' => 'doctor',
            'is_active' => true,
            'wallet_balance' => 0.00,
        ]);
    }

    /**
     * Test Green Path: Clean claim, within price cap, compatible gender -> status 1 (Approved).
     */
    public function test_green_path_auto_adjudication()
    {
        $payload = [
            'patient_name' => 'Fatima Al-Harbi',
            'patient_gender' => 'Female',
            'patient_age' => 34,
            'patient_national_id' => '1099887766',
            'patient_phone' => '+966551234567',
            'patient_email' => 'fatima@gmail.com',
            'cpt_code' => '93000', // ECG
            'claimed_amount' => 120.00, // Under CPT limit of 150
            'icd_10_code' => 'I10', // Hypertension
            'clinical_notes' => 'Routine trace.',
            'simulated_semantic_score' => 0.95,
            'simulated_llm_score' => 0.95,
        ];

        $task = $this->orchestrator->ingest(
            'MEDICAL_CLAIM',
            $this->hospital->company_id,
            $this->payer->company_id,
            $payload
        );

        $this->assertEquals(1, $task->status_code); // GREEN
        $this->assertGreaterThanOrEqual(0.90, $task->confidence_score);
        $this->assertNotNull($task->audit_trail['financial_ledger'] ?? null);
        $this->assertEquals('fatima@gmail.com', $task->payload['patient_email']); // No redaction in Green path
    }

    /**
     * Test Yellow Path: Clean claim with mid confidence -> status 2 (Auditing), PHI scrubbed.
     */
    public function test_yellow_path_phi_sanitizer()
    {
        $payload = [
            'patient_name' => 'Yousef Al-Otaibi',
            'patient_gender' => 'Male',
            'patient_age' => 45,
            'patient_national_id' => '1033445566',
            'patient_phone' => '+966509876543',
            'patient_email' => 'yousef@outlook.com',
            'cpt_code' => '70450', // Head CT
            'claimed_amount' => 1400.00,
            'icd_10_code' => 'G44', // Headaches
            'clinical_notes' => 'Headache check.',
            'simulated_semantic_score' => 0.70, // Forces Yellow Path
            'simulated_llm_score' => 0.70,
        ];

        $task = $this->orchestrator->ingest(
            'MEDICAL_CLAIM',
            $this->hospital->company_id,
            $this->payer->company_id,
            $payload
        );

        $this->assertEquals(2, $task->status_code); // YELLOW
        
        // Assert PHI is sanitized in payload
        $this->assertEquals('[REDACTED NAME]', $task->payload['patient_name']);
        $this->assertEquals('[REDACTED NATIONAL_ID]', $task->payload['patient_national_id']);
        $this->assertEquals('[REDACTED PHONE]', $task->payload['patient_phone']);
        $this->assertEquals('[REDACTED EMAIL]', $task->payload['patient_email']);

        // Assert original unscrubbed payload is archived for SIU reference
        $this->assertEquals('Yousef Al-Otaibi', $task->original_payload['patient_name']);
    }

    /**
     * Test Red Path: Hard rules violation (gender conflict) -> status 3 (Fraud Escalation), unscrubbed.
     */
    public function test_red_path_gender_incompatibility()
    {
        $payload = [
            'patient_name' => 'Fahad Al-Harbi',
            'patient_gender' => 'Male', // MALE patient
            'patient_age' => 31,
            'patient_national_id' => '1065432109',
            'patient_phone' => '+966547654321',
            'patient_email' => 'fahad@yahoo.com',
            'cpt_code' => '59400', // Vaginal Delivery procedure (OB/GYN)
            'claimed_amount' => 5000.00,
            'icd_10_code' => 'O30', // Gestation code
            'clinical_notes' => 'OB check.',
            'simulated_semantic_score' => 0.90,
            'simulated_llm_score' => 0.90,
        ];

        $task = $this->orchestrator->ingest(
            'MEDICAL_CLAIM',
            $this->hospital->company_id,
            $this->payer->company_id,
            $payload
        );

        $this->assertEquals(3, $task->status_code); // RED (Fraud)
        $this->assertLessThan(0.60, $task->confidence_score);
        $this->assertEquals('Fahad Al-Harbi', $task->payload['patient_name']); // Unscrubbed for SIU
    }

    /**
     * Test Doctor HITL resolve and micro-wallet crediting.
     */
    public function test_doctor_resolve_adjudication_and_wallet_credit()
    {
        // 1. Create a Yellow path task
        $task = WorkflowTask::create([
            'task_id' => \Illuminate\Support\Str::uuid(),
            'task_type' => 'MEDICAL_CLAIM',
            'status_code' => 2, // Yellow
            'confidence_score' => 0.75,
            'hospital_id' => $this->hospital->company_id,
            'insurance_id' => $this->payer->company_id,
            'payload' => ['patient_name' => '[REDACTED NAME]', 'claimed_amount' => 500.00],
            'original_payload' => ['patient_name' => 'Khalid Al-Dosari', 'claimed_amount' => 500.00],
            'audit_trail' => ['rule_engine_logs' => []]
        ]);

        $this->actingAs($this->doctor);

        // 2. Resolve via controller post route
        $response = $this->post(route('workflow.doctor_resolve'), [
            'task_id' => (string) $task->task_id,
            'action' => 'Approve',
            'comment' => 'Clean diagnosis records confirm valid outpatient consult.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $task->refresh();
        $this->assertEquals(1, $task->status_code); // Transitioned to Green/Approved
        $this->assertEquals('Approve', $task->doctor_response);
        $this->assertEquals('Clean diagnosis records confirm valid outpatient consult.', $task->doctor_comment);
        $this->assertEquals(75.00, $task->reward_amount);

        // Assert doctor wallet balance increased
        $this->doctor->refresh();
        $this->assertEquals(75.00, $this->doctor->wallet_balance);

        // Assert wallet transaction is recorded
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->doctor->id,
            'type'    => 'credit',
            'amount'  => 75.00,
        ]);
    }
}
