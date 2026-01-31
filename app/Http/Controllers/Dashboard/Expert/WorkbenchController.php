<?php

namespace App\Http\Controllers\Dashboard\Expert;

use App\Http\Controllers\Controller;
use App\Models\AiTask;
use App\Models\AiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkbenchController extends Controller
{
    /**
     * Display the workbench with current or selected task
     */
    public function index(Request $request)
    {
        $expert = Auth::user();
        $taskId = $request->query('task_id');

        /**
         * 1️⃣ Load current task
         * - If task_id exists → load that task (Previous / Review)
         * - Else → load next pending task
         */
        if ($taskId) {
            $currentTask = AiTask::where('id', $taskId)
                ->first();
        } else {
            $currentTask = AiTask::where('status', 'pending')
                ->orderBy('id')
                ->first();
        }

        /**
         * 2️⃣ Determine if any previous completed task exists
         * - Independent from current task (safe)
         */
        $hasPreviousTask = AiTask::whereIn('status', ['completed', 'skipped'])
            ->exists();

        /**
         * 3️⃣ Stats
         */
        $tasksToday = AiResponse::where('expert_id', $expert->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $earningsToday = AiResponse::where('expert_id', $expert->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('reward_amount') ?? 0;

        /**
         * 4️⃣ Return view
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع',
            ]);
        }
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
                'status'       => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            // Trigger Governance Event
            event(new \App\Events\ExpertAnswerSubmitted($response));
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
                'status'       => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            // Trigger Governance Event
            event(new \App\Events\ExpertAnswerSubmitted($response));
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
     * Load previous completed task
     */
    private function loadPrevious(AiTask $currentTask, $expert)
    {
        $query = AiTask::whereIn('status', ['completed', 'skipped'])
            ->orderByDesc('updated_at');

        // Only filter by time if we are navigating strictly within history (not from a new/pending task)
        // If current task is pending, we just want the absolute latest completed/skipped task
        if ($currentTask->exists && $currentTask->status !== 'pending' && $currentTask->updated_at) {
             $query->where('updated_at', '<', $currentTask->updated_at);
        }

        $previousTask = $query->first();

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
