<?php

namespace App\Services;

use App\Models\LinguisticTask;
use App\Models\LinguisticTaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoutingService
{
    /**
     * Get the next available task for an expert
     *
     * @param User $expert
     * @return LinguisticTask|null
     */
    public function getNextTask(User $expert): ?LinguisticTask
    {
        return DB::transaction(function () use ($expert) {
            // Determine expert's eligible categories
            $eligibleCategories = $this->getEligibleCategories($expert);

            // Find an available task
            $task = LinguisticTask::availableForAssignment()
                ->whereIn('category', $eligibleCategories)
                ->whereDoesntHave('assignments', function ($query) use ($expert) {
                    $query->where('user_id', $expert->id);
                })
                ->orderBy('priority', 'desc')
                ->orderBy('assigned_count', 'asc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$task) {
                return null;
            }

            // Create assignment with 2-minute timeout
            LinguisticTaskAssignment::create([
                'linguistic_task_id' => $task->id,
                'user_id' => $expert->id,
                'status' => 'reserved',
                'reserved_at' => now(),
                'expires_at' => now()->addMinutes(2),
            ]);

            // Increment assigned count
            $task->increment('assigned_count');

            // Check if task should be closed (reached max assignments)
            if ($task->assigned_count >= 5) {
                $task->update(['status' => 'closed']);
            }

            return $task->fresh();
        });
    }

    /**
     * Determine which categories an expert is eligible for
     *
     * @param User $expert
     * @return array
     */
    private function getEligibleCategories(User $expert): array
    {
        // Map expert domains to task categories
        $domainMapping = [
            'medical' => ['medical'],
            'legal' => ['legal'],
            'engineering' => ['engineering'],
            'business' => ['business'],
            'general' => ['general'],
        ];

        $expertDomain = $expert->expert_domain ?? 'general';

        // If expert has a specialization, use it; otherwise default to general
        return $domainMapping[$expertDomain] ?? ['general'];
    }

    /**
     * Release expired assignments
     * This should be called by a scheduled command
     *
     * @return int Number of released assignments
     */
    public function releaseExpiredAssignments(): int
    {
        $expiredAssignments = LinguisticTaskAssignment::expired()->get();

        $count = 0;
        foreach ($expiredAssignments as $assignment) {
            DB::transaction(function () use ($assignment) {
                // Mark assignment as expired
                $assignment->update(['status' => 'expired']);

                // Decrement task assigned count
                $task = $assignment->task;
                $task->decrement('assigned_count');

                // Reopen task if it was closed
                if ($task->status === 'closed' && $task->assigned_count < 5) {
                    $task->update(['status' => 'pending']);
                }
            });

            $count++;
        }

        return $count;
    }

    /**
     * Check if an expert can answer a specific task
     *
     * @param User $expert
     * @param LinguisticTask $task
     * @return bool
     */
    public function canAnswer(User $expert, LinguisticTask $task): bool
    {
        // Check if expert has an active (non-expired) assignment for this task
        $assignment = LinguisticTaskAssignment::where('linguistic_task_id', $task->id)
            ->where('user_id', $expert->id)
            ->where('status', 'reserved')
            ->where('expires_at', '>', now())
            ->first();

        return $assignment !== null;
    }
}
