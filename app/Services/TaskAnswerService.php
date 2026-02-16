<?php

namespace App\Services;

use App\Models\LinguisticTask;
use App\Models\LinguisticTaskAnswer;
use App\Models\LinguisticTaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskAnswerService
{
    /**
     * Submit an answer for a task
     *
     * @param User $expert
     * @param LinguisticTask $task
     * @param bool $isClaimCorrect
     * @param string|null $selectedLabel
     * @param string|null $comment
     * @return LinguisticTaskAnswer
     * @throws \Exception
     */
    public function submitAnswer(
        User $expert,
        LinguisticTask $task,
        bool $isClaimCorrect,
        ?string $selectedLabel = null,
        ?string $comment = null
    ): LinguisticTaskAnswer {
        return DB::transaction(function () use ($expert, $task, $isClaimCorrect, $selectedLabel, $comment) {
            // Verify expert has an active assignment
            $assignment = LinguisticTaskAssignment::where('linguistic_task_id', $task->id)
                ->where('user_id', $expert->id)
                ->where('status', 'reserved')
                ->where('expires_at', '>', now())
                ->first();

            if (!$assignment) {
                throw new \Exception('No active assignment found or assignment has expired.');
            }

            // Create the answer
            $answer = LinguisticTaskAnswer::create([
                'linguistic_task_id' => $task->id,
                'user_id' => $expert->id,
                'is_claim_correct' => $isClaimCorrect,
                'selected_label' => $selectedLabel,
                'comment' => $comment,
            ]);

            // Mark assignment as completed
            $assignment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Update task correct_classification if expert provided correction
            if (!$isClaimCorrect && $selectedLabel) {
                $task->update(['correct_classification' => $selectedLabel]);
            }

            // Check if all assignments are completed
            $completedCount = $task->assignments()->where('status', 'completed')->count();
            if ($completedCount >= 5) {
                $task->update(['status' => 'completed']);
            }

            return $answer;
        });
    }

    /**
     * Get statistics for an expert
     *
     * @param User $expert
     * @return array
     */
    public function getExpertStatistics(User $expert): array
    {
        $totalAnswers = LinguisticTaskAnswer::where('user_id', $expert->id)->count();
        
        $correctAnswers = LinguisticTaskAnswer::where('user_id', $expert->id)
            ->where('is_claim_correct', true)
            ->count();

        $wrongAnswers = $totalAnswers - $correctAnswers;

        $accuracy = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;

        return [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'wrong_answers' => $wrongAnswers,
            'accuracy_percentage' => round($accuracy, 2),
            'points' => $correctAnswers * 10, // 10 points per correct answer
        ];
    }
}
