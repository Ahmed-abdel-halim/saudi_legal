<?php

namespace App\Listeners;

use App\Events\AnswerSubmitted;
use App\Models\GovernanceLog;
use App\Models\TaskConsensus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class EvaluateConsensus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AnswerSubmitted $event): void
    {
        $task = $event->response->task;

        // Atomic check to avoid race conditions via transaction lock could be better, 
        // but here we rely on the event processing.
        // Re-query task to ensure fresh data
        $task->refresh();
        
        $responseCount = $task->responses()->count();
        $task->update(['current_responses' => $responseCount]);

        if ($responseCount < $task->required_responses) {
            return;
        }

        // Check if consensus already reached to avoid duplicates
        if ($task->consensus) {
            return;
        }

        // Start Consensus Logic
        $responses = $task->responses;
        $answers = $responses->pluck('corrected_data')->toArray();
        
        // Count frequency of each answer
        // Note: This logic depends on exact string matching. For JSON/Complex objects, serialization is key.
        $counts = array_count_values($answers);
        arsort($counts);
        
        $mostCommonAnswer = array_key_first($counts);
        $count = $counts[$mostCommonAnswer];
        
        $consensusType = 'conflict';
        $finalAnswer = null;
        $confidence = 0;
        $status = 'Conflict';

        if ($count === 3) {
            $consensusType = 'perfect_match';
            $finalAnswer = $mostCommonAnswer;
            $confidence = 100;
            $status = 'Consensus_Reached';
        } elseif ($count === 2) {
            $consensusType = 'majority_vote';
            $finalAnswer = $mostCommonAnswer;
            $confidence = 66; // Fixed per requirement
            $status = 'Consensus_Reached'; // Or 'Review_Required' if majority is considered shaky
        } else {
            $consensusType = 'conflict';
            $finalAnswer = null; // No consensus
            $confidence = 0;
            $status = 'Conflict';
            
            // Log conflict
            GovernanceLog::create([
                'task_id' => $task->id,
                'expert_id' => $responses->first()->expert_id, // Logging against one expert or generic log?
                // Better to just log the conflict event related to task
                'expert_id' => $responses->first()->expert_id, // Placeholder, requires non-null expert_id in schema usually
                'event_type' => 'consensus_conflict',
                'event_data' => json_encode(['answers' => $answers]),
                'trust_score_before' => null,
                'trust_score_after' => null,
            ]);
        }

        DB::transaction(function () use ($task, $responses, $finalAnswer, $confidence, $consensusType, $status) {
            TaskConsensus::create([
                'task_id' => $task->id,
                'expert_answers' => json_encode($responses->map(function($r) {
                    return [
                        'expert_id' => $r->expert_id,
                        'answer' => $r->corrected_data,
                        'confidence' => $r->confidence_level
                    ];
                })),
                'final_answer' => $finalAnswer ? json_encode($finalAnswer) : null,
                'confidence_level' => $confidence,
                'consensus_type' => $consensusType,
            ]);

            $task->update([
                'status' => $status,
                'consensus_status' => strtolower($status), // 'Consensus_Reached' -> 'consensus_reached', 'Conflict' -> 'conflict'
                'completed_at' => now(),
            ]);
        });
    }
}
