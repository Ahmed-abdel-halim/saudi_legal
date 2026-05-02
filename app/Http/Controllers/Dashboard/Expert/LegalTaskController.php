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

        // استخدام محرك الربط الذكي الجديد لاستخراج المواد القانونية 
        $mentionedArticles = collect();
        if ($currentTask) {
            $referenceService = new \App\Services\LegalReferenceService();
            
            // 1. البحث في الإجابة المقترحة أولاً (لأنها الأهم بالنسبة للخبير)
            $mentionedArticles = $referenceService->getMentionedArticles(
                $currentTask->proposed_answer ?? '',
                $currentTask->law_system_name,
                $currentTask->law_article_number
            );

            // 2. البحث في نص الحكم
            $caseArticles = $referenceService->getMentionedArticles(
                $currentTask->case_text ?? '',
                $currentTask->law_system_name,
                $currentTask->law_article_number
            );
            $mentionedArticles = $mentionedArticles->merge($caseArticles);
            
            // 3. البحث في السؤال
            $questionArticles = $referenceService->getMentionedArticles(
                $currentTask->question ?? '',
                $currentTask->law_system_name,
                $currentTask->law_article_number
            );
            $mentionedArticles = $mentionedArticles->merge($questionArticles);

            // إزالة التكرارات
            $mentionedArticles = $mentionedArticles->unique('id')->values();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $currentTask,
                'mentioned_articles' => $mentionedArticles,
                'stats' => $this->getExpertStats($expert)
            ]);
        }

        return view('dashboard.expert.legal_workbench', [
            'task' => $currentTask,
            'mentioned_articles' => $mentionedArticles,
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
            'expert_comment' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'correct_law_system' => 'nullable|string|max:255',
            'correct_law_article' => 'nullable|string|max:255'
        ]);

        $task = LegalTask::where('id', $request->task_id)
            ->where('expert_id', Auth::id())
            ->firstOrFail();

        $updateData = [
            'is_correct' => $request->is_correct,
            'correct_answer' => $request->is_correct ? $task->proposed_answer : $request->correct_answer,
            'status' => 'completed',
            'completed_at' => now(),
        ];

        // التعامل مع الوسوم (Tags)
        $tags = $request->tags ?? [];
        
        // التحقق من وجود العمود في قاعدة البيانات
        if (\Illuminate\Support\Facades\Schema::hasColumn('legal_tasks', 'tags')) {
            $updateData['tags'] = $tags;
            $updateData['expert_comment'] = $request->expert_comment;
        } else {
            // حل بديل: تخزين الأوسمة داخل الملاحظات إذا لم يوجد العمود بعد
            $tagsString = !empty($tags) ? "[Tags: " . implode(', ', $tags) . "] " : "";
            $updateData['expert_comment'] = $tagsString . $request->expert_comment;
        }

        // إضافة المرجع القانوني الجديد (إذا لم نقم بعمل migrate بعد نحفظها في الملاحظات)
        if (\Illuminate\Support\Facades\Schema::hasColumn('legal_tasks', 'correct_law_system')) {
            $updateData['correct_law_system'] = $request->correct_law_system;
            $updateData['correct_law_article'] = $request->correct_law_article;
        } else {
            if ($request->correct_law_system || $request->correct_law_article) {
                $refString = "[Reference: " . ($request->correct_law_system ?? 'غير محدد') . " - " . ($request->correct_law_article ?? '') . "] \n";
                // Append to comment if not a proper column
                $updateData['expert_comment'] = $refString . ($updateData['expert_comment'] ?? '');
            }
        }

        $task->update($updateData);

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
     * حذف المهمة الحالية (للمهام الفارغة أو غير الصالحة)
     */
    public function delete(Request $request)
    {
        $task = LegalTask::where('id', $request->task_id)
            ->where('expert_id', Auth::id())
            ->first();

        if ($task) {
            $task->delete();
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
            // ملاحظة: لا نمسح completed_at هنا لنحافظ على الرصيد ثابتاً أثناء المراجعة
            $lastTask->update([
                'status' => 'in_progress'
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
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhere(function($sq) {
                          $sq->where('status', 'in_progress')
                            ->whereNotNull('completed_at');
                      });
                })
                ->whereDate('completed_at', Carbon::today())
                ->count(),
            'pending_tasks' => LegalTask::where('status', 'pending')
                ->where('domain', 'law')
                ->count(),
        ];
    }
}
