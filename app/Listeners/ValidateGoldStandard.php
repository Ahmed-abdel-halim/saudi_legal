<?php

namespace App\Listeners;

use App\Events\ExpertAnswerSubmitted;
use App\Models\GovernanceLog;
use Illuminate\Support\Facades\DB;

class ValidateGoldStandard
{
    private const TRUST_SCORE_PENALTY = 10; // Points deducted per failure
    private const WARNING_THRESHOLD = 65; // Internal warning at this score
    private const AUTO_BAN_THRESHOLD = 60; // Auto-ban below this score

    /**
     * Handle the event.
     */
    public function handle(ExpertAnswerSubmitted $event): void
    {
        $response = $event->response;
        $task = $response->task;

        // Only process gold standard tasks
        if (!$task->is_gold_standard) {
            return;
        }

        $expert = $response->expert;
        $isCorrect = $this->compareAnswers($response->corrected_data, $task->gold_answer);

        DB::transaction(function () use ($expert, $task, $response, $isCorrect) {
            $oldScore = $expert->trust_score;

            if ($isCorrect) {
                $expert->increment('gold_tasks_completed');

                // Approve reward for passed gold standard
                $response->update([
                    'reward_status' => 'full',
                    'final_reward_amount' => $response->reward_amount
                ]);

                GovernanceLog::create([
                    'expert_id' => $expert->id,
                    'task_id' => $task->id,
                    'event_type' => 'gold_task_passed',
                    'event_data' => [
                        'task_type' => $task->task_type,
                        'confidence' => $response->confidence_level
                    ],
                    'trust_score_before' => $oldScore,
                    'trust_score_after' => $oldScore
                ]);
            } else {
                // Failed gold standard
                $expert->increment('gold_tasks_failed');
                $newScore = max(0, $oldScore - self::TRUST_SCORE_PENALTY);
                $expert->trust_score = $newScore;

                // Set reward to zero for gold standard failures
                $response->update([
                    'reward_status' => 'denied',
                    'final_reward_amount' => 0
                ]);

                GovernanceLog::create([
                    'expert_id' => $expert->id,
                    'task_id' => $task->id,
                    'event_type' => 'gold_task_failed',
                    'event_data' => [
                        'task_type' => $task->task_type,
                        'expected_answer' => $task->gold_answer,
                        'expert_answer' => $response->corrected_data
                    ],
                    'trust_score_before' => $oldScore,
                    'trust_score_after' => $newScore
                ]);

                // Check for warning threshold
                if ($newScore <= self::WARNING_THRESHOLD && !$expert->trust_warning_issued) {
                    $this->issueWarning($expert, $task);
                }

                // Check for auto-ban
                if ($newScore < self::AUTO_BAN_THRESHOLD) {
                    $this->banExpert($expert, $task);
                }
            }

            $expert->save();
        });
    }

    /**
     * Compare expert answer with gold standard answer
     */
    private function compareAnswers($expertAnswer, $goldAnswer): bool
    {
        // For JSON data, use deep comparison
        return json_encode($expertAnswer) === json_encode($goldAnswer);
    }

    /**
     * Issue internal warning when trust score reaches threshold
     */
    private function issueWarning($expert, $task): void
    {
        $expert->update(['trust_warning_issued' => true]);

        GovernanceLog::create([
            'expert_id' => $expert->id,
            'task_id' => $task->id,
            'event_type' => 'trust_score_warning',
            'event_data' => [
                'threshold' => self::WARNING_THRESHOLD,
                'current_score' => $expert->trust_score,
                'message' => 'Expert approaching auto-ban threshold'
            ]
        ]);

        // TODO: Notify super admin of warning
    }

    /**
     * Ban expert and freeze all pending rewards
     */
    private function banExpert($expert, $task): void
    {
        $expert->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => "Trust score dropped below threshold after failing gold task #{$task->id}"
        ]);

        GovernanceLog::create([
            'expert_id' => $expert->id,
            'task_id' => $task->id,
            'event_type' => 'expert_banned',
            'event_data' => [
                'reason' => 'trust_score_threshold',
                'final_trust_score' => $expert->trust_score
            ]
        ]);

        // Freeze all pending rewards
        $expert->responses()
            ->where('reward_status', 'pending')
            ->update(['reward_status' => 'denied']);

        // TODO: Notify super admin of ban
    }
}
