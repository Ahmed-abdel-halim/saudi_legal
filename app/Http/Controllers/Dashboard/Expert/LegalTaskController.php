<?php

namespace App\Http\Controllers\Dashboard\Expert;

use App\Http\Controllers\Controller;
use App\Models\LegalTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LegalTaskController extends Controller
{
    /**
     * عرض المهمة القانونية الحالية للخبير
     */
    public function index(Request $request)
    {
        $expert = Auth::user();
        
        // البحث عن مهمة قيد التنفيذ لهذا الخبير
        $currentTask = LegalTask::where('expert_id', $expert->id)
            ->where('status', 'in_progress')
            ->first();

        // إذا لم توجد مهمة، نقوم بتعيين واحدة جديدة من المهام المتاحة (بناءً على مجال المحاماة)
        if (!$currentTask) {
            $currentTask = LegalTask::where('status', 'pending')
                ->whereNull('expert_id')
                ->where('domain', 'law')
                ->first();
            
            if ($currentTask) {
                $currentTask->update([
                    'expert_id' => $expert->id,
                    'status' => 'in_progress',
                    'assigned_at' => now(),
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $currentTask,
                'stats' => $this->getExpertStats($expert)
            ]);
        }

        return view('dashboard.expert.legal_workbench', [
            'task' => $currentTask,
            'stats' => $this->getExpertStats($expert)
        ]);
    }

    /**
     * تقديم نتائج تنقيح البيانات (RLHF)
     */
    public function submit(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:legal_tasks,id',
            'is_correct' => 'required|boolean',
            'correct_answer' => 'required_if:is_correct,false|nullable|string',
            'expert_comment' => 'nullable|string|max:1000'
        ]);

        $task = LegalTask::where('id', $request->task_id)
            ->where('expert_id', Auth::id())
            ->firstOrFail();

        $task->update([
            'is_correct' => $request->is_correct,
            'correct_answer' => $request->is_correct ? $task->proposed_answer : $request->correct_answer,
            'expert_comment' => $request->expert_comment,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التنقيح بنجاح، شكراً لك.',
            'next_url' => route('dashboard.expert.legal_workbench')
        ]);
    }

    /**
     * تخطي المهمة الحالية
     */
    public function skip(Request $request)
    {
        $task = LegalTask::where('id', $request->task_id)
            ->where('expert_id', Auth::id())
            ->first();

        if ($task) {
            // تحويل الحالة إلى متخطاة حتى لا تعود لنفس الشخص
            $task->update([
                'status' => 'skipped',
            ]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * العودة للمهمة السابقة
     */
    public function previous(Request $request)
    {
        $expert = Auth::user();
        
        // البحث عن آخر مهمة تم إنجازها أو تخطيها
        $lastTask = LegalTask::where('expert_id', $expert->id)
            ->whereIn('status', ['completed', 'skipped'])
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($lastTask) {
            // إرجاع المهمة الحالية (التي لم تنجز بعد) لقائمة الانتظار
            LegalTask::where('expert_id', $expert->id)
                ->where('status', 'in_progress')
                ->update(['status' => 'pending', 'expert_id' => null]);
            
            // إعادة المهمة السابقة لتكون قيد التنفيذ
            $lastTask->update([
                'status' => 'in_progress',
                'completed_at' => null
            ]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * إحصائيات الخبير لليوم
     */
    private function getExpertStats($expert)
    {
        return [
            'completed_today' => LegalTask::where('expert_id', $expert->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', Carbon::today())
                ->count(),
            'pending_tasks' => LegalTask::where('status', 'pending')
                ->where('domain', 'law')
                ->count(),
        ];
    }
}
