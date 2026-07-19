<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;
use App\Models\GovernanceLog;
use App\Models\TaskConsensus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaudiLegalConsensusTest extends TestCase
{
    use RefreshDatabase;

    protected $expert1;
    protected $expert2;
    protected $expert3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expert1 = User::factory()->create(['trust_score' => 100, 'role' => 'expert']);
        $this->expert2 = User::factory()->create(['trust_score' => 100, 'role' => 'expert']);
        $this->expert3 = User::factory()->create(['trust_score' => 100, 'role' => 'expert']);
    }

    /** @test */
    public function it_reaches_consensus_immediately_if_two_experts_agree()
    {
        // 1. Create Consensus Task requiring 2 responses
        $task = AiTask::factory()->create([
            'is_gold_standard' => false,
            'required_responses' => 2,
            'current_responses' => 0,
            'status' => 'pending'
        ]);

        // 2. Submit 2 identical responses
        $this->actingAs($this->expert1)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Match Answer', 'confidence_level' => 10
        ]);

        $this->actingAs($this->expert2)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Match Answer', 'confidence_level' => 10
        ]);

        // 3. Assertions
        $task->refresh();
        $this->assertEquals(2, $task->current_responses);
        $this->assertEquals(2, $task->required_responses);
        $this->assertEquals('Consensus_Reached', $task->status->value ?? $task->status);
        
        $this->assertDatabaseHas('task_consensus', [
            'task_id' => $task->id,
            'consensus_type' => 'perfect_match',
            'confidence_level' => 100
        ]);
    }

    /** @test */
    public function it_increases_required_responses_to_three_if_two_experts_differ_and_resolves_with_third_expert()
    {
        // 1. Create Consensus Task requiring 2 responses
        $task = AiTask::factory()->create([
            'is_gold_standard' => false,
            'required_responses' => 2,
            'current_responses' => 0,
            'status' => 'pending'
        ]);

        // 2. Submit 2 differing responses
        $this->actingAs($this->expert1)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Answer A', 'confidence_level' => 10
        ]);

        $this->actingAs($this->expert2)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Answer B', 'confidence_level' => 10
        ]);

        // 3. Assertions after 2 responses
        $task->refresh();
        $this->assertEquals(2, $task->current_responses);
        $this->assertEquals(3, $task->required_responses); // Has been increased to 3
        $this->assertEquals('pending', $task->status->value ?? $task->status); // Still pending/in-progress

        // 4. Submit 3rd response matching Answer A
        $this->actingAs($this->expert3)->post(route('dashboard.expert.workbench.action'), [
            'action' => 'submit_correction', 'task_id' => $task->id, 'corrected_data' => 'Answer A', 'confidence_level' => 10
        ]);

        // 5. Assertions after 3rd response
        $task->refresh();
        $this->assertEquals(3, $task->current_responses);
        $this->assertEquals(3, $task->required_responses);
        $this->assertEquals('Consensus_Reached', $task->status->value ?? $task->status);

        $this->assertDatabaseHas('task_consensus', [
            'task_id' => $task->id,
            'consensus_type' => 'majority_vote',
            'confidence_level' => 66
        ]);
    }
}
