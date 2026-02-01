<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiTask;
use App\Models\TaskAssignment;
use App\Services\TaskAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskAssignmentService();
    }

    /** @test */
    public function it_assigns_task_to_eligible_expert()
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $task = AiTask::factory()->create([
            'status' => 'pending',
            'required_responses' => 3,
            'current_responses' => 0
        ]);

        $assignedTask = $this->service->assignNextTask($expert);

        $this->assertNotNull($assignedTask);
        $this->assertEquals($task->id, $assignedTask->id);
        
        // Use active scope on model
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'expert_id' => $expert->id
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_assignment_to_same_expert()
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $task = AiTask::factory()->create(['required_responses' => 3]);

        // First assignment
        $assigned1 = $this->service->assignNextTask($expert);
        $this->assertNotNull($assigned1);

        // Second request should return the SAME active task (idempotency)
        $assigned2 = $this->service->assignNextTask($expert);
        $this->assertEquals($assigned1->id, $assigned2->id);

        // Count should be 1
        $this->assertEquals(1, TaskAssignment::count());
    }

    /** @test */
    public function it_stops_assigning_when_max_locks_reached()
    {
        $task = AiTask::factory()->create([
            'status' => 'pending',
            'required_responses' => 2 // Only 2 allowed
        ]);

        $expert1 = User::factory()->create(['role' => 'expert']);
        $expert2 = User::factory()->create(['role' => 'expert']);
        $expert3 = User::factory()->create(['role' => 'expert']);

        // Lock for Expert 1
        $this->service->assignNextTask($expert1);
        // Lock for Expert 2
        $this->service->assignNextTask($expert2);

        // Try Expert 3 (Should fail to get THIS task)
        // Since we only have one task, it should return null
        $assigned3 = $this->service->assignNextTask($expert3);

        $this->assertNull($assigned3);
        $this->assertEquals(2, TaskAssignment::count());
    }

    /** @test */
    public function it_respects_gold_standard_injection()
    {
        // Create 100 pending tasks (90 normal, 10 gold)
        // This is a probabilistic test, so we just check logic execution basically.
        // We'll skip complex statistical assertions and just ensure gold CAN be picked.
        
        $expert = User::factory()->create(['role' => 'expert']);
        $goldTask = AiTask::factory()->create([
            'is_gold_standard' => true, 
            'status' => 'pending'
        ]);

        // If only gold task exists, it must be picked eventually.
        // Since probability is ~20%, loop until picked or timeout.
        $assigned = null;
        for ($i = 0; $i < 50; $i++) {
            $assigned = $this->service->assignNextTask($expert);
            if ($assigned) break;
        }

        $this->assertNotNull($assigned, "Failed to assign gold task within 50 attempts");
        $this->assertTrue((bool)$assigned->is_gold_standard);
    }
}
