<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Models\WalletTransaction;
use App\Services\Workflow\WorkflowOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowController extends Controller
{
    protected WorkflowOrchestrator $orchestrator;

    public function __construct(WorkflowOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * Display the B2B SaaS Workflow Dashboard Portal.
     */
    public function index(Request $request)
    {
        // 1. Setup Demo State if no users/companies exist
        $this->ensureDemoEntitiesSeeded();

        // 2. Determine current active tenant / perspective
        $activeRole = $request->query('role', session('workflow_role', 'hospital'));
        session(['workflow_role' => $activeRole]);

        // Get demo entities
        $hospital = Company::where('industry', 'healthcare')->first();
        $payer = Company::where('industry', 'insurance')->first();
        $doctor = User::where('expert_specialization', 'doctor')->first();

        // Simulate acting user
        $actingUser = null;
        if ($activeRole === 'doctor') {
            $actingUser = $doctor;
        } elseif ($activeRole === 'hospital') {
            $actingUser = $hospital ? $hospital->users()->first() : null;
        } else {
            $actingUser = $payer ? $payer->users()->first() : null;
        }

        // Stats calculation
        $stats = [
            'total' => WorkflowTask::count(),
            'green' => WorkflowTask::where('status_code', 1)->count(),
            'yellow' => WorkflowTask::where('status_code', 2)->count(),
            'red' => WorkflowTask::where('status_code', 3)->count(),
            'clearing_pool' => WorkflowTask::where('status_code', 1)
                ->get()
                ->sum(fn($t) => (float)($t->payload['claimed_amount'] ?? 0)),
        ];

        // Lists
        $allTasks = WorkflowTask::orderBy('created_at', 'desc')->get();
        
        // Filter doctor's active queue (Yellow claims)
        $doctorQueue = WorkflowTask::where('status_code', 2)
            ->whereNull('doctor_response')
            ->orderBy('created_at', 'asc')
            ->get();

        // Filter Payer SIU claims (Red claims)
        $siuClaims = WorkflowTask::where('status_code', 3)
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter Hospital's submitted claims
        $hospitalClaims = WorkflowTask::where('hospital_id', $hospital->company_id ?? '')
            ->orderBy('created_at', 'desc')
            ->get();

        // Custom template rule caps
        $ruleCaps = [
            '99213' => \App\Models\SiteSetting::get('cpt_cap_99213', 250.00),
            '70450' => \App\Models\SiteSetting::get('cpt_cap_70450', 1500.00),
            '93000' => \App\Models\SiteSetting::get('cpt_cap_93000', 150.00),
        ];

        return view('workflow.b2b_portal', compact(
            'activeRole',
            'stats',
            'hospital',
            'payer',
            'doctor',
            'actingUser',
            'allTasks',
            'doctorQueue',
            'siuClaims',
            'hospitalClaims',
            'ruleCaps'
        ));
    }

    /**
     * Submit a claim transaction (Hospital uploads/sends).
     */
    public function uploadClaim(Request $request)
    {
        $hospital = Company::where('industry', 'healthcare')->first();
        $payer = Company::where('industry', 'insurance')->first();

        if (!$hospital || !$payer) {
            return back()->with('error', 'Demo tenants are not properly seeded.');
        }

        // Support both JSON file upload or form inputs
        $payload = [];

        if ($request->hasFile('claim_json')) {
            $jsonContent = file_get_contents($request->file('claim_json')->path());
            $parsed = json_decode($jsonContent, true);
            if (!$parsed || !isset($parsed['payload'])) {
                return back()->with('error', 'Invalid JSON structure. Must contain a payload block.');
            }
            $payload = $parsed['payload'];
        } else {
            $request->validate([
                'patient_name' => 'required|string|max:100',
                'patient_gender' => 'required|string|in:Male,Female',
                'patient_age' => 'required|integer|min:0|max:120',
                'patient_national_id' => 'required|string|max:20',
                'patient_phone' => 'required|string|max:20',
                'patient_email' => 'required|email|max:100',
                'cpt_code' => 'required|string',
                'claimed_amount' => 'required|numeric|min:0',
                'icd_10_code' => 'required|string',
                'clinical_notes' => 'nullable|string',
                'simulated_semantic_score' => 'nullable|numeric|min:0|max:1',
                'simulated_llm_score' => 'nullable|numeric|min:0|max:1',
                'is_duplicate_flag' => 'nullable|boolean',
            ]);

            $payload = [
                'patient_name' => $request->patient_name,
                'patient_gender' => $request->patient_gender,
                'patient_age' => (int)$request->patient_age,
                'patient_national_id' => $request->patient_national_id,
                'patient_phone' => $request->patient_phone,
                'patient_email' => $request->patient_email,
                'cpt_code' => $request->cpt_code,
                'claimed_amount' => (float)$request->claimed_amount,
                'icd_10_code' => $request->icd_10_code,
                'clinical_notes' => $request->clinical_notes ?? '',
                'is_duplicate_flag' => (bool)$request->is_duplicate_flag,
                'simulated_semantic_score' => $request->simulated_semantic_score !== null ? (float)$request->simulated_semantic_score : 0.88,
                'simulated_llm_score' => $request->simulated_llm_score !== null ? (float)$request->simulated_llm_score : 0.93,
            ];
        }

        try {
            $task = $this->orchestrator->ingest(
                'MEDICAL_CLAIM',
                $hospital->company_id,
                $payer->company_id,
                $payload
            );

            $statusText = $task->status_code == 1 ? 'Auto-Approved (Green)' : ($task->status_code == 2 ? 'Routed to Auditing (Yellow)' : 'Escalated to Payer SIU (Red)');
            
            return redirect()->route('workflow.portal', ['role' => 'hospital'])
                ->with('success', "Claim submitted successfully. Routed path: {$statusText} (Confidence Score: {$task->confidence_score})");
        } catch (\Exception $e) {
            return back()->with('error', 'Ingestion failure: ' . $e->getMessage());
        }
    }

    /**
     * Resolve a Yellow Path claim (Auditor Doctor action).
     */
    public function doctorResolve(Request $request)
    {
        $request->validate([
            'task_id' => 'required|uuid|exists:workflow_tasks,task_id',
            'action' => 'required|string|in:Approve,Deny',
            'comment' => 'required|string|min:5|max:1000',
        ]);

        $task = WorkflowTask::findOrFail($request->task_id);
        $doctor = User::where('expert_specialization', 'doctor')->first();

        if ($task->status_code !== 2) {
            return back()->with('error', 'This task is not in the Yellow (Auditing) path.');
        }

        $reward = 75.00; // 75 SAR micro-wallet credit

        DB::transaction(function () use ($task, $request, $doctor, $reward) {
            $newStatusCode = $request->action === 'Approve' ? 1 : 3;
            $auditTrail = $task->audit_trail;

            // Generate financial ledger if approved
            if ($request->action === 'Approve') {
                $auditTrail['financial_ledger'] = [
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                    'hospital_id' => $task->hospital_id,
                    'insurance_id' => $task->insurance_id,
                    'amount_approved' => (float)($task->original_payload['claimed_amount'] ?? 0),
                    'settlement_clearing_status' => 'AUTHORIZED_CREDIT_QUEUED',
                    'authorized_at' => now()->toIso8601String(),
                ];
            }

            $auditTrail['human_in_the_loop_resolution'] = [
                'doctor_id'    => $doctor->id ?? null,
                'doctor_name'  => $doctor->name ?? 'Dr. Sarah (Auditor)',
                'response'     => $request->action,
                'comment'      => $request->comment,
                'reward_earned' => $reward,
                'resolved_at'  => now()->toIso8601String(),
            ];

            $task->update([
                'status_code'        => $newStatusCode,
                'assigned_doctor_id' => $doctor->id ?? null,
                'doctor_response'    => $request->action,
                'doctor_comment'     => $request->comment,
                'reward_amount'      => $reward,
                'doctor_completed_at' => now(),
                'audit_trail'        => $auditTrail,
            ]);

            // Credit the auditor doctor's micro-wallet (ENUM must be 'credit')
            if ($doctor) {
                WalletTransaction::create([
                    'user_id'     => $doctor->id,
                    'amount'      => $reward,
                    'type'        => 'credit',
                    'description' => "Audit reward for claim task {$task->task_id} — Decision: {$request->action}",
                ]);
                // Keep the wallet_balance column in sync (same pattern as ServiceController)
                $doctor->increment('wallet_balance', $reward);
            }
        });

        return redirect()->route('workflow.portal', ['role' => 'doctor'])
            ->with('success', "Claim task resolved. Decision committed: {$request->action}. Micro-wallet reward of {$reward} SAR awarded to auditor.");
    }

    /**
     * Configure Payer policy rules.
     */
    public function updatePolicyRules(Request $request)
    {
        $request->validate([
            'cpt_cap_99213' => 'required|numeric|min:0',
            'cpt_cap_70450' => 'required|numeric|min:0',
            'cpt_cap_93000' => 'required|numeric|min:0',
        ]);

        \App\Models\SiteSetting::set('cpt_cap_99213', (float)$request->cpt_cap_99213);
        \App\Models\SiteSetting::set('cpt_cap_70450', (float)$request->cpt_cap_70450);
        \App\Models\SiteSetting::set('cpt_cap_93000', (float)$request->cpt_cap_93000);

        return redirect()->route('workflow.portal', ['role' => 'payer'])
            ->with('success', 'Payer policy limits updated successfully in the rules engine.');
    }

    /**
     * Reset database workflow logs to fresh state.
     */
    public function resetDemo()
    {
        WorkflowTask::truncate();
        WalletTransaction::truncate();
        
        $doctor = User::where('expert_specialization', 'doctor')->first();
        if ($doctor) {
            $doctor->update(['wallet_balance' => 0.00]);
        }

        // Seed some starter tasks
        $this->seedStarterTasks();

        return redirect()->route('workflow.portal', ['role' => 'hospital'])
            ->with('success', 'Workspace reset completed. Demo tasks have been re-seeded.');
    }

    /**
     * Helper: Ensure B2B companies and Doctor exists.
     */
    private function ensureDemoEntitiesSeeded()
    {
        // 1. Healthcare Hospital (Submitter)
        $hospital = Company::where('industry', 'healthcare')->first();
        if (!$hospital) {
            $hospital = Company::create([
                'name' => 'King Faisal Specialist Hospital (Submitter)',
                'industry' => 'healthcare',
                'size' => 'large',
                'is_supplier' => true,
                'is_verified_provider' => true,
                'cr_number' => 'HOSP-445899',
            ]);
            
            User::create([
                'name' => 'Dr. Khalid (Hospital Admin)',
                'email' => 'hospital@radiif.com',
                'password' => bcrypt('password'),
                'role' => 'supplier',
                'company_id' => $hospital->company_id,
                'is_active' => true,
            ]);
        }

        // 2. Insurance Company (Payer)
        $payer = Company::where('industry', 'insurance')->first();
        if (!$payer) {
            $payer = Company::create([
                'name' => 'Tawuniya Insurance Company (Payer)',
                'industry' => 'insurance',
                'size' => 'large',
                'is_requester' => true,
                'is_verified_provider' => true,
                'cr_number' => 'INS-202688',
            ]);
            
            User::create([
                'name' => 'Ahmed (Payer Admin)',
                'email' => 'insurance@radiif.com',
                'password' => bcrypt('password'),
                'role' => 'requester',
                'company_id' => $payer->company_id,
                'is_active' => true,
            ]);
        }

        // 3. HITL Auditor Doctor
        $doctor = User::where('expert_specialization', 'doctor')->first();
        if (!$doctor) {
            User::create([
                'name' => 'Dr. Sarah (HITL Clinical Auditor)',
                'email' => 'doctor@radiif.com',
                'password' => bcrypt('password'),
                'role' => 'expert',
                'expert_domain' => 'healthcare',
                'expert_specialization' => 'doctor',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Starter tasks seeder for demonstration.
     */
    private function seedStarterTasks()
    {
        $hospital = Company::where('industry', 'healthcare')->first();
        $payer = Company::where('industry', 'insurance')->first();

        if (!$hospital || !$payer) return;

        // Task 1: Auto-approved Green Path claim
        $payloadGreen = [
            'patient_name' => 'Fatima Al-Harbi',
            'patient_gender' => 'Female',
            'patient_age' => 34,
            'patient_national_id' => '1099887766',
            'patient_phone' => '+966551234567',
            'patient_email' => 'fatima@gmail.com',
            'cpt_code' => '93000', // ECG
            'claimed_amount' => 120.00, // Under cap
            'icd_10_code' => 'I10', // Hypertension
            'clinical_notes' => 'Routine follow up check, minor headache.',
            'simulated_semantic_score' => 0.95,
            'simulated_llm_score' => 0.97,
        ];

        // Task 2: Yellow Auditing Path claim (ambiguity/high cost)
        $payloadYellow = [
            'patient_name' => 'Yousef Al-Otaibi',
            'patient_gender' => 'Male',
            'patient_age' => 45,
            'patient_national_id' => '1033445566',
            'patient_phone' => '+966509876543',
            'patient_email' => 'yousef@outlook.com',
            'cpt_code' => '70450', // Head CT
            'claimed_amount' => 1400.00, // Near limit
            'icd_10_code' => 'G44', // Headaches
            'clinical_notes' => 'Patient complaining of severe recurring migraines. Requesting Head CT scan.',
            'simulated_semantic_score' => 0.72,
            'simulated_llm_score' => 0.78,
        ];

        // Task 3: Red Fraud Path claim (gender conflict)
        $payloadRed = [
            'patient_name' => 'Abdulrahman Al-Dosari',
            'patient_gender' => 'Male',
            'patient_age' => 29,
            'patient_national_id' => '1022338877',
            'patient_phone' => '+966544332211',
            'patient_email' => 'abdulrahman@yahoo.com',
            'cpt_code' => '59400', // Vaginal Delivery
            'claimed_amount' => 5500.00,
            'icd_10_code' => 'O30', // Multiple gestation
            'clinical_notes' => 'Emergency delivery billing code error test.',
            'simulated_semantic_score' => 0.40,
            'simulated_llm_score' => 0.45,
        ];

        // Ingest them
        $this->orchestrator->ingest('MEDICAL_CLAIM', $hospital->company_id, $payer->company_id, $payloadGreen);
        $this->orchestrator->ingest('MEDICAL_CLAIM', $hospital->company_id, $payer->company_id, $payloadYellow);
        $this->orchestrator->ingest('MEDICAL_CLAIM', $hospital->company_id, $payer->company_id, $payloadRed);
    }
}
