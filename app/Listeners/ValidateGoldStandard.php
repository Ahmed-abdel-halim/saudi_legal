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
        
        if ($task->legalTask) {
            // For legal tasks, the trap is the generated answer itself.
            // If the expert edited/corrected it, they passed.
            // If they accepted it, they failed.
            $isCorrect = ($response->action === 'edited');
            $source = 'legal_workbench';
            $expertAnswerData = $isCorrect ? $response->corrected_data : 'accepted_wrong_answer';
        } else {
            // For general tasks, compare the corrected data with the gold answer after normalizing
            $normalizedResponse = $this->normalizeText($response->corrected_data);
            $normalizedGold = $this->normalizeText($goldAnswer);
            $isCorrect = ($normalizedResponse === $normalizedGold);
            $source = 'general_workbench';
            $expertAnswerData = $response->corrected_data;
        }

        $trustScoreBefore = $expert->trust_score;

        if ($isCorrect) {
            $expert->increment('gold_tasks_completed');
            GovernanceLog::create([
                'expert_id' => $expert->id,
                'task_id' => $task->id,
                'event_type' => 'gold_task_passed',
                'event_data' => json_encode([
                    'expert_answer' => $expertAnswerData,
                    'gold_answer' => $goldAnswer,
                    'source' => $source
                ], JSON_UNESCAPED_UNICODE),
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
                'event_data' => json_encode([
                    'expert_answer' => $expertAnswerData,
                    'gold_answer' => $goldAnswer,
                    'source' => $source
                ], JSON_UNESCAPED_UNICODE),
                'trust_score_before' => $trustScoreBefore,
                'trust_score_after' => $trustScoreAfter,
            ]);

            // Check for ban (Temporarily disabled by admin request to let lawyer complete auditing)
            /*
             if ($expert->trust_score < 60 && !$expert->is_banned) {
                $banReason = $source === 'legal_workbench'
                    ? 'انخفض مؤشر الثقة عن 60 بسبب الفشل في أسئلة الاختبار.'
                    : 'Trust score fell below 60 due to failed gold tasks.';

                $expert->update([
                    'is_banned' => true,
                    'banned_at' => now(),
                    'ban_reason' => $banReason,
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
            */
        }
    }

    /**
     * Normalize text for robust comparison.
     */
    private function normalizeText($text): string
    {
        if (is_array($text)) {
            $text = json_encode($text, JSON_UNESCAPED_UNICODE);
        }
        $text = (string)$text;
        // Convert to lowercase and trim
        $text = mb_strtolower(trim($text));
        // Remove multiple spaces/newlines
        $text = preg_replace('/\s+/', ' ', $text);
        // Normalize Arabic letters:
        $text = str_replace('ة', 'ه', $text);
        $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);
        $text = str_replace('ى', 'ي', $text);
        // Remove common punctuation: . , ; : ? ! ، ؛ ؟
        $text = str_replace(['.', ',', ';', ':', '?', '!', '،', '؛', '؟', '-', '_'], '', $text);
        // Remove all remaining whitespace for a tight comparison
        $text = preg_replace('/\s+/', '', $text);
        return $text;
    }
}
