<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Carbon\Carbon;

class WorkbenchPreviousTaskTest extends TestCase
{
    use RefreshDatabase;

    protected $expert;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an expert user
        $this->expert = User::factory()->create();
        
        // Mock events to avoid actual listeners firing
        Event::fake();
    }

    /** @test */
    public function it_loads_the_most_recently_processed_task()
    {
        // 1. Create two completed tasks
        $task1 = AiTask::factory()->create([
            'status' => 'completed', 
            'updated_at' => Carbon::now()->subMinutes(10)
        ]);
        
        $task2 = AiTask::factory()->create([
            'status' => 'completed', 
            'updated_at' => Carbon::now()->subMinutes(5)
        ]);
        
        // Current pending task (what the user is looking at now)
        // CRITICAL: Make this OLDER than the completed task to test the bug fix
        $currentTask = AiTask::factory()->create([
            'status' => 'pending',
            'updated_at' => Carbon::now()->subMinutes(100) 
        ]);

        // 2. Act: Call 'load_previous' from the current task
        $response = $this->actingAs($this->expert)
            ->post(route('dashboard.expert.workbench.action'), [
                'action' => 'load_previous',
                'task_id' => $currentTask->id,
            ]);

        // 3. Assert: It should redirect to task2 (the most recent one)
        $response->assertJson(['success' => true]);
        // Extract redirect URL to check ID
        $redirectUrl = $response->json('redirect');
        $this->assertStringContainsString("task_id={$task2->id}", $redirectUrl);
    }

    /** @test */
    public function it_loads_skipped_tasks()
    {
        // 1. Create a skipped task
        $skippedTask = AiTask::factory()->create([
            'status' => 'skipped',
            'updated_at' => Carbon::now()->subMinutes(2)
        ]);

        $currentTask = AiTask::factory()->create(['status' => 'pending']);

        // 2. Act
        $response = $this->actingAs($this->expert)
            ->post(route('dashboard.expert.workbench.action'), [
                'action' => 'load_previous',
                'task_id' => $currentTask->id,
            ]);

        // 3. Assert
        $response->assertJson(['success' => true]);
        $redirectUrl = $response->json('redirect');
        $this->assertStringContainsString("task_id={$skippedTask->id}", $redirectUrl);
    }

    /** @test */
    public function it_navigates_backwards_through_history()
    {
        // History: Task A -> Task B -> Task C (Current)
        
        $taskA = AiTask::factory()->create([
            'status' => 'completed',
            'updated_at' => Carbon::now()->subMinutes(15)
        ]);

        $taskB = AiTask::factory()->create([
            'status' => 'skipped',
            'updated_at' => Carbon::now()->subMinutes(10)
        ]);

        $taskC_current = AiTask::factory()->create([
            'status' => 'completed',
            'updated_at' => Carbon::now()->subMinutes(5)
        ]);

        // 1. Act: Looking at Task C (completed), want previous (Task B)
        // Note: load_previous logic uses the current task's updated_at as pivot
        $response = $this->actingAs($this->expert)
            ->post(route('dashboard.expert.workbench.action'), [
                'action' => 'load_previous',
                'task_id' => $taskC_current->id,
            ]);

        // 2. Assert: Should go to Task B
        $response->assertJson(['success' => true]);
        $this->assertStringContainsString("task_id={$taskB->id}", $response->json('redirect'));
    }
}
