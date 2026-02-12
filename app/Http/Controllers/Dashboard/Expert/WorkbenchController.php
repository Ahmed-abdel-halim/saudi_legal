<?php

namespace App\Http\Controllers\Dashboard\Expert;

use App\Http\Controllers\Controller;
use App\Models\AiTask;
use App\Models\AiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Services\TaskAssignmentService;

class WorkbenchController extends Controller
{
    public function __construct(
        protected TaskAssignmentService $assignmentService
    ) {}
    /**
     * Display the workbench with current or selected task
     */
    public function index(Request $request)
    {
        $expert = Auth::user();
        
        // --- 1. Check for active/pending Sentiment Task (LinguisticTask) ---
        // A. Check if expert has an in-progress sentiment task
        $sentimentTask = \App\Models\LinguisticTask::where('expert_id', $expert->id)
            ->where('status', 'in_progress')
            ->first();

        // B. If no active task, try to assign a new one matching domain
        if (!$sentimentTask) {
            $sentimentTask = \App\Models\LinguisticTask::where('task_type', 'sentiment')
                ->where('status', 'pending')
                ->whereNull('expert_id')
                ->where('domain', $expert->expert_domain) // Domain match
                ->lockForUpdate()
                ->first();
            
            if ($sentimentTask) {
                $sentimentTask->update([
                    'expert_id' => $expert->id,
                    'status' => 'in_progress',
                    'assigned_at' => now(),
                ]);
            }
        }

        // C. If found, show sentiment workbench
        if ($sentimentTask) {
            $completedToday = \App\Models\LinguisticTask::where('expert_id', $expert->id)
                ->where('task_type', 'sentiment')
                ->whereDate('completed_at', Carbon::today())
                ->count();

            return view('dashboard.expert.sentiment_workbench', [
                'task' => $sentimentTask,
                'completed_today' => $completedToday
            ]);
        }

        // --- 2. Fallback to existing Governance/AiTask Logic ---
        $taskId = $request->query('task_id');

        /**
         * Load current task (AiTask)
         */
        if ($taskId) {
            $currentTask = AiTask::where('id', $taskId)->first();
        } else {
            $currentTask = $this->assignmentService->assignNextTask($expert);
        }

        /**
         * Determine if any previous completed task exists
         */
        $hasPreviousTask = AiResponse::where('expert_id', $expert->id)
            ->exists();

        /**
         * Stats
         */
        $tasksToday = AiResponse::where('expert_id', $expert->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $earningsToday = AiResponse::where('expert_id', $expert->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('reward_amount') ?? 0;

        /**
         * Return view
         */
        return view('dashboard.expert.workbench', [
            'currentTask'     => $currentTask,
            'hasPreviousTask' => $hasPreviousTask,
            'tasks_today'     => $tasksToday,
            'earnings_today'  => $earningsToday,
        ]);
    }


    /**
     * Handle task actions
     */
    public function action(Request $request)
    {
        $expert = Auth::user();
        $action = $request->input('action');
        $taskId = $request->input('task_id');

        $task = AiTask::where('id', $taskId)
            ->firstOrFail();

        try {
            return match ($action) {
                'mark_correct'      => $this->markCorrect($task),
                'submit_correction' => $this->submitCorrection($task, $request, $expert),
                'skip_task'         => $this->skipTask($task),
                'load_previous'     => $this->loadPrevious($task, $expert),
                default             => response()->json(['success' => false, 'message' => 'إجراء غير معروف']),
            };
        } catch (\Throwable $e) {
            \Log::error('Workbench Action Error', [
                'action' => $action,
                'task_id' => $taskId,
                'expert_id' => $expert->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ]);
        }
    }

    /**
     * Submit Sentiment Analysis Task
     */
    public function submitSentiment(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:linguistic_tasks,id',
            'is_correct' => 'required|boolean',
            'correct_classification' => 'required_if:is_correct,false|nullable|string|in:إيجابي,سلبي,محايد'
        ]);

        $task = \App\Models\LinguisticTask::where('id', $request->task_id)
            ->where('expert_id', Auth::id())
            ->firstOrFail();

        $task->update([
            'is_correct' => $request->is_correct,
            'correct_classification' => $request->is_correct ? $task->proposed_classification : $request->correct_classification,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الإجابة بنجاح',
            'redirect' => route('dashboard.expert.workbench') // Will load next task
        ]);
    }


    /**
     * Mark task as correct (no edits)
     */
    /**
     * Mark task as correct (no edits)
     */
    private function markCorrect(AiTask $task)
    {
        $expert = Auth::user();

        // Check if expert already has a response for this task
        $existingResponse = AiResponse::where('task_id', $task->id)
            ->where('expert_id', $expert->id)
            ->first();

        if ($existingResponse) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بالفعل بالإجابة على هذه المهمة. سيتم تحميل المهمة التالية.',
                'redirect' => route('dashboard.expert.workbench')
            ]);
        }

        // Wrap in transaction to ensure response creation and task update happen together
        DB::transaction(function () use ($task, $expert) {
            // Create response record for "Accepted" action
            $response = AiResponse::create([
                'task_id'          => $task->id,
                'expert_id'        => $expert->id,
                'corrected_data'   => $task->ai_suggestion ?? '', // Handle nullable suggestion
                'correction_notes' => null,
                'confidence_level' => 10, // Max confidence for accepted tasks
                'action'           => 'accepted',
                'reward_amount'    => $this->calculateReward(10), // Full reward for correct acceptance
            ]);

            $task->update([
                // 'status' => 'completed', // Governance Listener handles status update via Consensus
                'completed_at' => Carbon::now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم اعتماد المهمة بنجاح',
            'redirect' => route('dashboard.expert.workbench') // Redirect to next task
        ]);
    }

    /**
     * Submit correction and complete task
     */
    private function submitCorrection(AiTask $task, Request $request, $expert)
    {
        // Check if expert already has a response for this task
        $existingResponse = AiResponse::where('task_id', $task->id)
            ->where('expert_id', $expert->id)
            ->first();

        if ($existingResponse) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بالفعل بالإجابة على هذه المهمة. سيتم تحميل المهمة التالية.',
                'redirect' => route('dashboard.expert.workbench')
            ]);
        }

        $validated = $request->validate([
            'corrected_data'    => 'required|string',
            'correction_notes' => 'nullable|string|max:1000',
            'confidence_level' => 'required|integer|min:1|max:10',
        ]);

        DB::transaction(function () use ($task, $validated, $expert) {

            $response = AiResponse::create([
                'task_id'          => $task->id,
                'expert_id'        => $expert->id,
                'corrected_data'   => $validated['corrected_data'],
                'correction_notes' => $validated['correction_notes'] ?? null,
                'confidence_level' => (int) $validated['confidence_level'],
                'action'           => 'edited',
                'reward_amount'    => $this->calculateReward((int) $validated['confidence_level']),
            ]);

            $task->update([
                // 'status' => 'completed', // Governance Listener handles status update via Consensus
                'completed_at' => Carbon::now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التعديل وإنهاء المهمة',
            'redirect' => route('dashboard.expert.workbench') // Redirect to next task
        ]);
    }

    /**
     * Skip task (mark as skipped)
     */
    private function skipTask(AiTask $task)
    {
        $task->update([
            'status' => 'skipped',
        ]);

        // Find the absolute next task by ID (regardless of status)
        $nextTask = AiTask::where('id', '>', $task->id)
            ->orderBy('id')
            ->first();

        if ($nextTask) {
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard.expert.workbench', ['task_id' => $nextTask->id])
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard.expert.workbench') // No more tasks, fall back to default logic
        ]);
    }

    /**
     * Load previous task that this expert has responded to
     */
    private function loadPrevious(AiTask $currentTask, $expert)
    {
        // Get the current task's response timestamp (if it exists)
        $currentResponse = AiResponse::where('task_id', $currentTask->id)
            ->where('expert_id', $expert->id)
            ->first();

        // Build query to find previous tasks this expert has responded to
        $query = AiTask::whereHas('responses', function ($q) use ($expert) {
                $q->where('expert_id', $expert->id);
            })
            ->with(['responses' => function ($q) use ($expert) {
                $q->where('expert_id', $expert->id);
            }])
            ->where('id', '!=', $currentTask->id); // Exclude current task

        // If current task has a response, get tasks responded to before it
        if ($currentResponse) {
            $query->whereHas('responses', function ($q) use ($expert, $currentResponse) {
                $q->where('expert_id', $expert->id)
                  ->where('created_at', '<', $currentResponse->created_at);
            });
        }

        // Order by response timestamp (most recent first)
        $previousTask = $query->get()
            ->sortByDesc(function ($task) use ($expert) {
                return $task->responses->first()->created_at;
            })
            ->first();

        if (!$previousTask) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد مهام سابقة',
            ]);
        }

        return response()->json([
            'success'  => true,
            'redirect' => route('dashboard.expert.workbench', [
                'task_id' => $previousTask->id
            ]),
        ]);
    }


    /**
     * Reward calculation
     */
    private function calculateReward(int $confidence): float
    {
        $baseReward = 5; // SAR
        $confidenceBonus = ($confidence / 10) * 2;

        return round($baseReward + $confidenceBonus, 2);
    }
}
