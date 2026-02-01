<?php

namespace App\Listeners;

use App\Events\AnswerSubmitted;
use App\Models\GovernanceLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ValidateGoldStandard implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AnswerSubmitted $event): void
    {
        $response = $event->response;
        $task = $response->task;
        $expert = $response->expert;

        // Only validate if it's a gold standard task
        if (!$task->is_gold_standard) {
            return;
        }

        $goldAnswer = $task->gold_answer; // Assuming array or string
        // Simple comparison logic - can be enhanced for complex types
        // Ensure consistent format (e.g., trim, lowercase) if text
        // For array/json, robust comparison needed
        
        // This is a simplified check. Adjust based on actual data structure.
        $isCorrect = $response->corrected_data == json_encode($goldAnswer) || 
                     $response->corrected_data == $goldAnswer ||
                     json_decode($response->corrected_data, true) == $goldAnswer;

        $trustScoreBefore = $expert->trust_score;

        if ($isCorrect) {
            $expert->increment('gold_tasks_completed');
            GovernanceLog::create([
                'expert_id' => $expert->id,
                'task_id' => $task->id,
                'event_type' => 'gold_task_passed',
                'event_data' => json_encode(['expert_answer' => $response->corrected_data, 'gold_answer' => $goldAnswer]),
                'trust_score_before' => $trustScoreBefore,
                'trust_score_after' => $trustScoreBefore,
            ]);
        } else {
            $expert->increment('gold_tasks_failed');
            $expert->decrement('trust_score', 10);
            
            // Reload expert to get updated trust score
            $expert->refresh();
            $trustScoreAfter = $expert->trust_score;

            GovernanceLog::create([
                'expert_id' => $expert->id,
                'task_id' => $task->id,
                'event_type' => 'gold_task_failed',
                'event_data' => json_encode(['expert_answer' => $response->corrected_data, 'gold_answer' => $goldAnswer]),
                'trust_score_before' => $trustScoreBefore,
                'trust_score_after' => $trustScoreAfter,
            ]);

            // Check for ban
             if ($expert->trust_score < 60 && !$expert->is_banned) {
                $expert->update([
                    'is_banned' => true,
                    'banned_at' => now(),
                    'ban_reason' => 'Trust score fell below 60 due to failed gold tasks.',
                    'is_active' => false,
                    'is_active_for_hire' => false,
                ]);

                GovernanceLog::create([
                    'expert_id' => $expert->id,
                    'event_type' => 'expert_banned',
                    'event_data' => json_encode(['reason' => 'Trust score < 60']),
                    'trust_score_before' => $trustScoreAfter,
                    'trust_score_after' => $trustScoreAfter,
                ]);
             }
        }
    }
}
