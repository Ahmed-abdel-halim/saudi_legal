<?php

namespace App\Services;

use App\Models\AiTask;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class TaskAssignmentService
{
    /**
     * Assign a task to an expert with strict locking and validation.
     *
     * @param int $taskId
     * @param int $expertId
     * @return TaskAssignment
     * @throws Exception
     */
    public function assignTaskToExpert(int $taskId, int $expertId): TaskAssignment
    {
        return DB::transaction(function () use ($taskId, $expertId) {
            // Lock the task row for update
            $task = AiTask::lockForUpdate()->find($taskId);

            if (!$task) {
                throw new Exception("Task not found.");
            }

            // prevent assignment if task is completed or cancelled
            if (in_array($task->status, ['Consensus_Reached', 'Conflict'])) {
                 throw new Exception("Task is already finalized.");
            }

            // Check if expert exists and is active
            $expert = User::find($expertId);
            if (!$expert || !$expert->is_active || !$expert->is_active_for_hire || $expert->is_banned) {
                throw new Exception("Expert is not eligible for assignment.");
            }

            // Check if expert already has this task assigned
            $existingAssignment = TaskAssignment::where('task_id', $taskId)
                ->where('expert_id', $expertId)
                ->first();

            if ($existingAssignment) {
                throw new Exception("Expert is already assigned to this task.");
            }

            // Check max 3 experts per task
            $currentAssignments = TaskAssignment::where('task_id', $taskId)
                ->active()
                ->count();

            if ($currentAssignments >= 3) {
                 throw new Exception("Task already has maximum number of experts.");
            }

            // Create assignment
            $assignment = TaskAssignment::create([
                'task_id' => $taskId,
                'expert_id' => $expertId,
                'assigned_at' => now(),
                'expires_at' => now()->addHours(24), // Configurable expiration
            ]);

            // Update task status if it's the first assignment
            if ($task->status === 'Pending') {
                $task->update(['status' => 'In_Progress']);
            }

            return $assignment;
        });
    }
    /**
     * Assign the next available task to the expert.
     * Checks for existing active assignments first.
     *
     * @param User $expert
     * @return AiTask|null
     */
    public function assignNextTask(User $expert): ?AiTask
    {
        // 1. Check for existing active assignment that hasn't been completed by this expert
        // We check if there's an assignment where the task is still open suitable for work
        // AND the expert hasn't already submitted a response
        $existingAssignment = TaskAssignment::where('expert_id', $expert->id)
            ->active()
            ->whereHas('task', function ($q) {
                $q->whereIn('status', ['Pending', 'In_Progress']);
            })
            ->whereDoesntHave('task.responses', function ($q) use ($expert) {
                $q->where('expert_id', $expert->id);
            })
            ->with('task')
            ->first();

        if ($existingAssignment) {
            return $existingAssignment->task;
        }

        // 2. Find a suitable new task
        // - Pending or In_Progress
        // - Fewer than required responses
        // - Not already assigned to this expert (active or expired)
        // - Not completed by this expert (via response check? No, assignment check covers it mostly, 
        //   but let's be safe: expert shouldn't have a response for it either)
        
        $candidateTask = AiTask::whereIn('status', ['Pending', 'In_Progress'])
            ->whereColumn('current_responses', '<', 'required_responses')
            ->whereDoesntHave('assignments', function ($q) use ($expert) {
                $q->where('expert_id', $expert->id);
            })
            ->whereDoesntHave('responses', function ($q) use ($expert) {
                $q->where('expert_id', $expert->id);
            })
            ->orderBy('id', 'asc') // FIFO strategy
            ->first();

        if (!$candidateTask) {
            return null;
        }

        try {
            // Attempt to assign
            $assignment = $this->assignTaskToExpert($candidateTask->id, $expert->id);
            return $assignment->task;
        } catch (Exception $e) {
            // Logging failure or race condition handling could go here
            return null;
        }
    }
}
