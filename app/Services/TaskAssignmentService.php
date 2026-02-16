<?php

namespace App\Services;

use App\Models\AiTask;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class TaskAssignmentService
{
    public function assignTaskToExpert(int $taskId, int $expertId): TaskAssignment
    {
        return DB::transaction(function () use ($taskId, $expertId) {
            $task = AiTask::lockForUpdate()->find($taskId);
            if (!$task) throw new Exception("Task not found.");
            if (in_array($task->status, ['Consensus_Reached','Conflict'])) throw new Exception("Task is already finalized.");

            $expert = User::find($expertId);
            
            // Strict eligibility check
            if (!$expert || !$expert->is_active || !$expert->is_active_for_hire || $expert->is_banned) {
                throw new Exception("Expert not eligible.");
            }

            // Strict Domain/Role Check
            if (!$expert->expert_domain || !$expert->expert_specialization) {
                 throw new Exception("Expert profile incomplete (missing domain/specialization).");
            }

            $existing = TaskAssignment::where('task_id',$taskId)->where('expert_id',$expertId)->first();
            if ($existing) throw new Exception("Already assigned.");

            $currentAssignments = TaskAssignment::where('task_id',$taskId)->active()->count();
            if ($currentAssignments >= 3) throw new Exception("Max experts assigned.");

            $assignment = TaskAssignment::create([
                'task_id'=>$taskId,
                'expert_id'=>$expertId,
                'assigned_at'=>now(),
                'expires_at'=>now()->addHours(24)
            ]);

            if ($task->status==='pending') $task->update(['status'=>'in_progress']);

            return $assignment;
        });
    }

    public function assignNextTask(User $expert): ?AiTask
    {
        $existing = TaskAssignment::where('expert_id',$expert->id)
            ->active()
            ->whereHas('task',fn($q)=>$q->whereIn('status',['pending','in_progress']))
            ->whereDoesntHave('task.responses',fn($q)=>$q->where('expert_id',$expert->id))
            ->with('task')
            ->first();

        if ($existing) return $existing->task;

        // STRICT FILTERING: Require domain and specialization
        if (!$expert->expert_domain || !$expert->expert_specialization) {
            \Log::info('Expert missing domain or specialization', [
                'expert_id' => $expert->id,
                'expert_domain' => $expert->expert_domain,
                'expert_specialization' => $expert->expert_specialization,
            ]);
            return null;
        }

        $task = AiTask::whereIn('status',['pending','in_progress'])
            ->whereColumn('current_responses','<','required_responses')
            // STRICT: Only tasks matching expert's exact domain
            ->where('task_domain', $expert->expert_domain)
            // Role Validation
            ->where(function($query) use ($expert) {
                $role = trim($expert->expert_specialization);
                $query->where('allow_all_roles', true)
                      ->orWhere('allowed_roles', 'LIKE', '%"' . $role . '"%')
                      ->orWhere('allowed_roles', 'LIKE', '%' . str_replace('\\', '\\\\', substr(json_encode($role), 1, -1)) . '%');
            })
            ->whereDoesntHave('assignments',fn($q)=>$q->where('expert_id',$expert->id))
            ->whereDoesntHave('responses',fn($q)=>$q->where('expert_id',$expert->id))
            ->orderBy('id','asc')
            ->first();

        if (!$task) {
            \Log::info('No task found for expert', [
                'expert_id' => $expert->id,
                'expert_domain' => $expert->expert_domain,
                'expert_specialization' => $expert->expert_specialization,
                'total_pending' => AiTask::whereIn('status',['pending','in_progress'])->count(),
                'domain_specific' => AiTask::whereIn('status',['pending','in_progress'])
                    ->where('task_domain', $expert->expert_domain)->count(),
            ]);
            return null;
        }

        try {
            $assignment = $this->assignTaskToExpert($task->id,$expert->id);
            return $assignment->task;
        } catch (Exception $e) {
            return null;
        }
    }
}
