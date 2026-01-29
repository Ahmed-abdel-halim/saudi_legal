<?php

namespace App\Services;

use App\Models\AiTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskAssignmentService
{
    /**
     * Assign the next available task to an expert
     * 
     * @param User $expert
     * @return AiTask|null
     */
    public function assignNextTask(User $expert): ?AiTask
    {
        // Check if expert is banned
        if ($expert->is_banned) {
            return null;
        }

        // Get tasks expert hasn't answered yet
        $task = AiTask::where('status', 'pending')
            ->where(function ($query) use ($expert) {
                // Either unassigned or needs more responses
                $query->whereNull('assigned_expert_id')
                    ->orWhere('current_responses', '<', DB::raw('required_responses'));
            })
            ->whereDoesntHave('responses', function ($query) use ($expert) {
                // Expert hasn't answered this task
                $query->where('expert_id', $expert->id);
            })
            ->where(function ($query) {
                // Respect per-client gold standard ratio
                $query->where('is_gold_standard', false)
                    ->orWhereRaw('RAND() < (
                        SELECT COALESCE(cs.gold_standard_ratio, 10.00) / 100 
                        FROM client_settings cs 
                        WHERE cs.client_id = ai_tasks_v2.client_id
                        LIMIT 1
                    )');
            })
            ->inRandomOrder()
            ->first();

        if ($task) {
            $task->update([
                'assigned_expert_id' => $expert->id,
                'assigned_at' => now()
            ]);
        }

        return $task;
    }

    /**
     * Get available task count for an expert
     * 
     * @param User $expert
     * @return int
     */
    public function getAvailableTaskCount(User $expert): int
    {
        if ($expert->is_banned) {
            return 0;
        }

        return AiTask::where('status', 'pending')
            ->where(function ($query) use ($expert) {
                $query->whereNull('assigned_expert_id')
                    ->orWhere('current_responses', '<', DB::raw('required_responses'));
            })
            ->whereDoesntHave('responses', function ($query) use ($expert) {
                $query->where('expert_id', $expert->id);
            })
            ->count();
    }
}
