<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    protected SubmissionService $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Submit a new task
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:10|max:5000',
            'proposed_classification' => 'nullable|string|in:positive,negative,neutral',
        ]);

        $user = Auth::user();

        try {
            $task = $this->submissionService->create(
                $user,
                $request->text,
                $request->proposed_classification
            );

            return response()->json([
                'success' => true,
                'message' => 'Submission created successfully.',
                'task' => [
                    'id' => $task->id,
                    'category' => $task->category,
                    'status' => $task->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
