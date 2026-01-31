<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;
use App\Models\GovernanceLog;
use App\Models\TaskConsensus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GovernanceSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $expert;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an expert user with initial trust score
        $this->expert = User::factory()->create(['trust_score' => 100]);
    }

    /** @test */
    public function it_validates_correct_gold_standard_answer()
    {
        // 1. Create Gold Standard Task
        $task = AiTask::factory()->create([
            'is_gold_standard' => true,
            'gold_answer' => 'Correct Answer',
            'status' => 'pending'
        ]);

        // 2. Submit Correct Answer via Workbench Action
        $response = $this->actingAs($this->expert)
            ->post(route('dashboard.expert.workbench.action'), [
                'action' => 'submit_correction',
                'task_id' => $task->id,
                'corrected_data' => 'Correct Answer', // Matches Gold
                'confidence_level' => 10
            ]);

        // 3. Assertions
        $response->assertJson(['success' => true]);
        
        // Check Governance Log
        $this->assertDatabaseHas('governance_logs', [
            'expert_id' => $this->expert->id,
            'task_id' => $task->id,
            'event_type' => 'gold_task_passed'
        ]);

        // Check Trust Score (should remain 100 or increment if logic allows)
        $this->assertEquals(100, $this->expert->fresh()->trust_score);
    }

    /** @test */
    public function it_penalizes_incorrect_gold_standard_answer()
    {
        // 1. Create Gold Standard Task
        $task = AiTask::factory()->create([
            'is_gold_standard' => true,
            'gold_answer' => 'Correct Answer',
            'status' => 'pending'
        ]);

        // 2. Submit INCORRECT Answer
        $this->actingAs($this->expert)
            ->post(route('dashboard.expert.workbench.action'), [
                'action' => 'submit_correction',
                'task_id' => $task->id,
                'corrected_data' => 'Wrong Answer',
                'confidence_level' => 10
            ]);

        // 3. Assertions
        // Check Governance Log for failure
        $this->assertDatabaseHas('governance_logs', [
            'expert_id' => $this->expert->id,
            'event_type' => 'gold_task_failed'
        ]);

        // Check Trust Score Deduction (100 - 10 = 90)
        $this->assertEquals(90, $this->expert->fresh()->trust_score);
    }

    /** @test */
    public function it_evaluates_consensus_for_normal_tasks()
    {
        // 1. Create Consensus Task (needs 3 responses)
        $task = AiTask::factory()->create([
            'is_gold_standard' => false,
            'required_responses' => 3,
            'current_responses' => 0,
            'status' => 'pending'
        ]);

        $expert1 = User::factory()->create();
        $expert2 = User::factory()->create();
        $expert3 = User::factory()->create();

        // 2. Submit 3 Responses (2 Agree, 1 Disagree)
        // Expert 1: "Answer A"
        $this->actingAs($expert1)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Answer A', 'confidence_level' => 9
        ]);

        // Expert 2: "Answer A" (Agreement)
        $this->actingAs($expert2)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Answer A', 'confidence_level' => 9
        ]);

        // Expert 3: "Answer B" (Disagreement)
        // This last one triggers the EvaluateConsensus listener because count reaches 3
        $this->actingAs($expert3)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Answer B', 'confidence_level' => 8
        ]);

        // 3. Assertions
        // Check Task Consensus Record
        $this->assertDatabaseHas('task_consensus', [
            'task_id' => $task->id,
            'consensus_type' => 'majority_vote', // 2 vs 1
            'final_answer' => '"Answer A"' // JSON encoded string by Eloquent usually, but array in logic
        ]);

        // Check Task Status
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals('consensus_reached', $task->fresh()->consensus_status);
    }
}
