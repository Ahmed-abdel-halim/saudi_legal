<?php

namespace App\Listeners;

use App\Events\ExpertAnswerSubmitted;
use App\Models\TaskConsensus;
use App\Models\GovernanceLog;
use App\Models\AiResponse;
use Illuminate\Support\Facades\DB;

class EvaluateConsensus
{
    /**
     * Handle the event.
     */
    public function handle(ExpertAnswerSubmitted $event): void
    {
        $response = $event->response;
        $task = $response->task;

        // Skip gold standard tasks (already validated)
        if ($task->is_gold_standard) {
            return;
        }

        // Increment response count
        $task->increment('current_responses');
        $task->refresh();

        // Wait until we have required responses
        if ($task->current_responses < $task->required_responses) {
            $task->update(['consensus_status' => 'in_progress']);
            return;
        }

        // Collect all responses
        $responses = $task->responses()
            ->with('expert')
            ->get()
            ->map(fn($r) => [
                'expert_id' => $r->expert_id,
                'answer' => $r->corrected_data,
                'confidence' => $r->confidence_level,
                'notes' => $r->correction_notes
            ])
            ->toArray();

        DB::transaction(function () use ($task, $responses) {
            $consensus = $this->calculateConsensus($responses);

            TaskConsensus::create([
                'task_id' => $task->id,
                'expert_answers' => $responses,
                'final_answer' => $consensus['final_answer'],
                'confidence_level' => $consensus['confidence'],
                'consensus_type' => $consensus['type'],
                'conflict_notes' => $consensus['notes'] ?? null
            ]);

            $task->update([
                'consensus_status' => $consensus['type'] === 'conflict' ? 'conflict' : 'consensus_reached',
                'status' => $consensus['type'] === 'conflict' ? 'pending_review' : 'completed'
            ]);

            // Distribute rewards based on consensus
            $this->distributeRewards($task, $responses, $consensus);

            if ($consensus['type'] === 'conflict') {
                // Log conflict for admin review
                foreach ($responses as $resp) {
                    GovernanceLog::create([
                        'expert_id' => $resp['expert_id'],
                        'task_id' => $task->id,
                        'event_type' => 'consensus_conflict',
                        'event_data' => [
                            'all_answers' => $responses,
                            'conflict_reason' => 'total_disagreement'
                        ]
                    ]);
                }

                // TODO: Notify super admin
            }
        });
    }

    /**
     * Calculate consensus from expert responses
     */
    private function calculateConsensus(array $responses): array
    {
        $answers = array_column($responses, 'answer');
        $answerCounts = [];

        // Count identical answers
        foreach ($answers as $answer) {
            $key = json_encode($answer);
            $answerCounts[$key] = ($answerCounts[$key] ?? 0) + 1;
        }

        arsort($answerCounts);
        $maxCount = reset($answerCounts);
        $mostCommonAnswer = json_decode(key($answerCounts), true);

        // Perfect match (all 3 agree)
        if ($maxCount === 3) {
            return [
                'type' => 'perfect_match',
                'final_answer' => $mostCommonAnswer,
                'confidence' => 100.00,
                'notes' => 'All experts agreed'
            ];
        }

        // Majority vote (2 agree)
        if ($maxCount === 2) {
            return [
                'type' => 'majority_vote',
                'final_answer' => $mostCommonAnswer,
                'confidence' => 66.67,
                'notes' => '2 out of 3 experts agreed'
            ];
        }

        // Total conflict (all different)
        return [
            'type' => 'conflict',
            'final_answer' => null,
            'confidence' => 0.00,
            'notes' => 'All experts provided different answers - requires admin review'
        ];
    }

    /**
     * Distribute rewards based on consensus results
     */
    private function distributeRewards($task, array $responses, array $consensus): void
    {
        $finalAnswer = $consensus['final_answer'];

        foreach ($responses as $responseData) {
            $response = AiResponse::where('task_id', $task->id)
                ->where('expert_id', $responseData['expert_id'])
                ->first();

            if (!$response) continue;

            // Check if answer matches consensus
            $answerMatches = json_encode($responseData['answer']) === json_encode($finalAnswer);

            if ($consensus['type'] === 'conflict') {
                // No rewards for conflicted tasks until admin resolves
                $response->update([
                    'reward_status' => 'pending',
                    'final_reward_amount' => null
                ]);
            } elseif ($answerMatches) {
                // Full reward for matching consensus
                $response->update([
                    'reward_status' => 'full',
                    'final_reward_amount' => $response->reward_amount
                ]);
            } else {
                // Partial reward for non-matching answers
                $response->update([
                    'reward_status' => 'partial',
                    'final_reward_amount' => $response->reward_amount * 0.5 // 50%
                ]);
            }
        }
    }
}
