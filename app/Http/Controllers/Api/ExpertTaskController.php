<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoutingService;
use App\Services\TaskAnswerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpertTaskController extends Controller
{
    protected RoutingService $routingService;
    protected TaskAnswerService $answerService;

    public function __construct(RoutingService $routingService, TaskAnswerService $answerService)
    {
        $this->routingService = $routingService;
        $this->answerService = $answerService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get the next available task for the authenticated expert
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function nextTask()
    {
        $expert = Auth::user();

        $task = $this->routingService->getNextTask($expert);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'No tasks available at the moment.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'comment_text' => $task->comment_text,
                'proposed_classification' => $task->proposed_classification,
                'category' => $task->category,
                'expires_at' => now()->addMinutes(2)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Submit an answer for a task
     *
     * @param Request $request
     * @param int $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswer(Request $request, int $taskId)
    {
        $request->validate([
            'is_claim_correct' => 'required|boolean',
            'selected_label' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:1000',
        ]);

        $expert = Auth::user();
        $task = \App\Models\LinguisticTask::findOrFail($taskId);

        try {
            $answer = $this->answerService->submitAnswer(
                $expert,
                $task,
                $request->is_claim_correct,
                $request->selected_label,
                $request->comment
            );

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully.',
                'answer' => $answer,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get expert statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $expert = Auth::user();
        $stats = $this->answerService->getExpertStatistics($expert);

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
