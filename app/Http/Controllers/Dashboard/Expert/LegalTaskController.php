<?php

namespace App\Http\Controllers\Dashboard\Expert;

use App\Http\Controllers\Controller;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;
use App\Models\LegalCitation;
use App\Models\GovernanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegalTaskController extends Controller
{
    /**
     * Display the current legal QA pair for review.
     * Transitioned from LegalTask to LegalQaPair (Structured Data).
     */
    public function index(Request $request)
    {
        $expert = Auth::user();

        // 1. Look for a QA pair currently being processed by this expert
        $currentQA = LegalQaPair::where('reviewer_id', $expert->id)
            ->where('review_status', 'Processing')
            ->with(['record', 'citations.article'])
            ->first();

        // 2. If no active task, fetch a new Pending one
        if (!$currentQA) {
            $currentQA = LegalQaPair::where('review_status', 'Pending')
                ->whereNull('reviewer_id')
                ->with(['record', 'citations.article'])
                ->first();

            if ($currentQA) {
                $currentQA->update([
                    'reviewer_id' => $expert->id,
                    'review_status' => 'Processing',
                ]);
            }
        }

        // 3. Data mapping for the view (keeping the variable names compatible if possible)
        $task = null;
        $mentionedArticles = collect();

        if ($currentQA) {
            // Get citations from the new relational table (specifically for this QA pair)
            $citations = $currentQA->citations->count() > 0 
                ? $currentQA->citations 
                : $currentQA->record->citations;

            $firstCitation = $citations->first();
            $lawSystemName = $firstCitation ? $firstCitation->system_name : 'نظام قانوني';
            $lawArticleNumber = $firstCitation ? $firstCitation->article_number : '';

            // Map LegalQaPair + LegalRecord to a virtual "Task" object for the Blade view
            $task = (object) [
                'id'              => $currentQA->id,
                'qa_id'           => $currentQA->qa_id,
                'question'        => $currentQA->question,
                'proposed_answer' => $currentQA->generated_answer,
                'case_text'       => $currentQA->record->full_text ?? '',
                'case_reference'  => $currentQA->record->source_reference ?? 'مرجع قضائي',
                'tags'            => $currentQA->record->tags ?? [],
                'sub_domain'      => $currentQA->record->sub_domain ?? 'قانون عام',
                // Keep these for compatibility even if empty, as we now use citations table
                'law_system_name'   => $lawSystemName, 
                'law_article_number'=> $lawArticleNumber,
            ];

            $mentionedArticles = $citations->map(function($c) {
                // If linked to a real DB article, return it directly
                if ($c->article) return $c->article;
                
                $system = trim($c->system_name ?? '');

                // Only skip empty or extremely short entries (e.g. noise or empty fields)
                if (mb_strlen($system) < 3) return null;

                // حالة خاصة: مادة قانونية بدون اسم نظام محدد (مثل "المادة 84" في الـ JSONL)
                if ($c->citation_source === 'law' && ($system === 'نظام غير محدد' || empty($system))) {
                    $artLabel = $c->article_number ? "المادة {$c->article_number}" : 'مادة قانونية';
                    return (object) [
                        'id' => 'temp-' . $c->id,
                        'legislation_title' => 'مرجع قانوني',
                        'article_title' => $artLabel,
                        'content' => 'نص المادة غير متوفر حالياً في قاعدة البيانات. المرجع: ' . $artLabel
                    ];
                }

                $isPrinciple = str_contains($system, 'مبدأ قضائي')
                    || str_contains($system, 'المبدأ')
                    || str_contains($system, 'قاعدة قضائية')
                    || str_contains($system, 'مستنبط')
                    || str_contains($system, 'القضائية')
                    || str_contains($system, 'أحكام الاستئناف');
                    
                $isSharia = str_contains($system, 'القاعدة الشرعية')
                    || str_contains($system, 'قاعدة فقهية')
                    || str_contains($system, 'الفقهاء')
                    || str_contains($system, 'قوله تعالى')
                    || str_contains($system, 'قال تعالى')
                    || str_contains($system, 'الحديث')
                    || str_contains($system, 'الآية')
                    || str_contains($system, 'شرعاً')
                    || str_contains($system, 'فقهاً');
                    
                $isContract = str_contains($system, 'العقد')
                    || str_contains($system, 'اتفاقية')
                    || str_contains($system, 'البند')
                    || str_contains($system, 'بند')
                    || str_contains($system, 'ملحق')
                    || str_contains($system, 'تعاقد');
                    
                $isEvidence = str_contains($system, 'محضر')
                    || str_contains($system, 'إفادة')
                    || str_contains($system, 'خطاب')
                    || str_contains($system, 'تقرير')
                    || str_contains($system, 'سند')
                    || str_contains($system, 'فاتورة')
                    || str_contains($system, 'كشف')
                    || str_contains($system, 'بينة')
                    || str_contains($system, 'قرينة')
                    || str_contains($system, 'الخبير');

                if ($isPrinciple) {
                    return (object) [
                        'id' => 'temp-' . $c->id,
                        'legislation_title' => 'مبدأ قضائي مرتبط',
                        'article_title' => '',
                        'content' => $system
                    ];
                } elseif ($isSharia) {
                    return (object) [
                        'id' => 'temp-' . $c->id,
                        'legislation_title' => 'مستند شرعي / فقهي',
                        'article_title' => '',
                        'content' => $system
                    ];
                } elseif ($isContract) {
                    return (object) [
                        'id' => 'temp-' . $c->id,
                        'legislation_title' => 'مستند تعاقدي / العقد المبرم',
                        'article_title' => '',
                        'content' => $system
                    ];
                } elseif ($isEvidence) {
                    return (object) [
                        'id' => 'temp-' . $c->id,
                        'legislation_title' => 'بيّنة / مستند إثبات',
                        'article_title' => '',
                        'content' => $system
                    ];
                } else {
                    // التحقق من الكلمات الدلالية للأنظمة والقوانين مع تجنب التطابق الخاطئ مع كلمات مثل "النظامية" أو "قانوني" في الجمل الطويلة
                    $isLawWord = (
                            str_contains($system, 'نظام ') 
                            || str_contains($system, 'لنظام ') 
                            || str_contains($system, 'بالنظام ')
                            || str_contains($system, 'الأنظمة')
                            || $system === 'نظام'
                        )
                        || (str_contains($system, 'قانون ') || str_contains($system, 'القانون ') || $system === 'قانون')
                        || str_contains($system, 'لائحة')
                        || str_contains($system, 'مرسوم')
                        || str_contains($system, 'قرار');

                    // تقليل الحد الأقصى لطول اسم النظام إلى 120 حرفاً لتجنب الجمل الطويلة التي تسرد الوقائع
                    if ($isLawWord && mb_strlen($system) < 120) {
                        $artTitle = 'مادة غير محددة';
                        $artSuffix = '';
                        if ($c->article_number) {
                            if (is_numeric($c->article_number)) {
                                $ordinal = $this->arabicOrdinal((int) $c->article_number);
                                $artTitle = "المادة {$ordinal}";
                                $artSuffix = "، المادة {$ordinal}";
                            } else {
                                $artTitle = "المادة {$c->article_number}";
                                $artSuffix = "، المادة {$c->article_number}";
                            }
                        }
                        return (object) [
                            'id' => 'temp-' . $c->id,
                            'legislation_title' => $system,
                            'article_title' => $artTitle,
                            'content' => 'نص المادة غير متوفر حالياً في قاعدة البيانات. المرجع: ' . $system . $artSuffix
                        ];
                    } else {
                        return (object) [
                            'id' => 'temp-' . $c->id,
                            'legislation_title' => 'مستند وقائع / أسباب الحكم',
                            'article_title' => '',
                            'content' => $system
                        ];
                    }
                }
            })->filter();

            // دمج المصادر المتكررة (مثل وقائع الدعوى أو المبادئ المستنبطة) في بطاقة واحدة لتجنب التكرار ولتسهيل القراءة
            $groupedArticles = collect();
            foreach ($mentionedArticles as $art) {
                $key = trim($art->legislation_title) . '|||' . trim($art->article_title ?? '');
                if (!$groupedArticles->has($key)) {
                    $groupedArticles->put($key, (object) [
                        'id' => $art->id,
                        'legislation_title' => $art->legislation_title,
                        'article_title' => $art->article_title ?? '',
                        'content' => $art->content
                    ]);
                } else {
                    $existing = $groupedArticles->get($key);
                    $existing->content = $existing->content . "\n\n• " . $art->content;
                }
            }

            $mentionedArticles = $groupedArticles->values()->map(function($art) {
                if (str_contains($art->content, "\n\n• ")) {
                    $art->content = "• " . $art->content;
                }
                return $art;
            });
        }

        $stats = $this->getExpertStats($expert);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
                'mentioned_articles' => $mentionedArticles,
                'stats' => $stats
            ]);
        }

        return view('dashboard.expert.legal_workbench', [
            'task' => $task,
            'mentioned_articles' => $mentionedArticles,
            'stats' => $stats,
            'earnings_today' => $stats['earnings_today'] ?? 0
        ]);
    }

    /**
     * Display all legal records reviewed by this expert.
     */
    public function history(Request $request)
    {
        $expert = Auth::user();

        $reviews = LegalQaPair::where('reviewer_id', $expert->id)
            ->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])
            ->with(['record', 'citations.article'])
            ->orderBy('reviewed_at', 'desc')
            ->paginate(15);

        return view('dashboard.expert.legal_history', compact('reviews'));
    }

    /**
     * Submit human review results (Approved / Modified / Rejected)
     */
    public function submit(Request $request)
    {
        $request->validate([
            'task_id'         => 'required|exists:legal_qa_pairs,id',
            'is_correct'      => 'required|boolean',
            'correct_answer'  => 'required_if:is_correct,false|nullable|string',
            'expert_comment'  => 'nullable|string|max:1000',
            'tags'            => 'nullable|array',
        ]);

        $qa = LegalQaPair::where('id', $request->task_id)
            ->where('reviewer_id', Auth::id())
            ->firstOrFail();

        $status = $request->is_correct ? 'Approved' : 'Modified';

        DB::transaction(function () use ($qa, $status, $request) {
            $qa->update([
                'review_status'    => $status,
                'corrected_answer' => $request->is_correct ? null : $request->correct_answer,
                'reviewed_at'      => now(),
                'time_spent'       => $request->input('time_spent'),
            ]);

            // Optional: Update record tags if they changed
            if ($request->has('tags')) {
                $qa->record->update(['tags' => $request->tags]);
            }

            // الربط مع نظام الحوكمة الأساسي (Governance System)
            $legalTask = \App\Models\LegalTask::where('source_id', $qa->id)
                ->where('source_type', 'legal_qa_pair')
                ->first();

            if ($legalTask) {
                // updateOrCreate لتجنب Duplicate entry عند إعادة تصحيح مهمة سابقة
                $aiResponse = \App\Models\AiResponse::updateOrCreate(
                    [
                        'task_id'   => $legalTask->task_id,
                        'expert_id' => Auth::id(),
                    ],
                    [
                        'corrected_data'   => $request->is_correct ? ($qa->generated_answer ?: '') : $request->correct_answer,
                        'correction_notes' => $request->expert_comment,
                        'confidence_level' => 10,
                        'action'           => $request->is_correct ? 'accepted' : 'edited',
                        'reward_amount'    => 2.00,
                        'time_spent'       => $request->input('time_spent'),
                    ]
                );

                // تفعيل تقييم Gold Standard — لو المهمة اختبارية
                $aiTask = \App\Models\AiTask::find($legalTask->task_id);
                if ($aiTask && $aiTask->is_gold_standard) {
                    // لما المحامي يقول "صحيحة" على سؤال gold standard
                    // ده يعني إنه وافق على الإجابة الخاطئة المقصودة → فشل الاختبار
                    // لما يقول "تعديل/تصحيح" → نجح (اكتشف الخطأ)
                    $expertPassedGold = !$request->is_correct; // نجح لو صحح الإجابة
                    $expert = \App\Models\User::find(Auth::id());

                    if ($expert) {
                        $trustScoreBefore = $expert->trust_score;

                        if ($expertPassedGold) {
                            // نجح: اكتشف إن الإجابة خاطئة وصححها
                            $expert->increment('gold_tasks_completed');
                            \App\Models\GovernanceLog::create([
                                'expert_id'          => $expert->id,
                                'task_id'            => $aiTask->id,
                                'event_type'         => 'gold_task_passed',
                                'event_data'         => json_encode([
                                    'expert_answer' => $request->correct_answer,
                                    'gold_answer'   => $aiTask->gold_answer,
                                    'source'        => 'legal_workbench',
                                ]),
                                'trust_score_before' => $trustScoreBefore,
                                'trust_score_after'  => $trustScoreBefore,
                            ]);
                        } else {
                            // فشل: قال "صحيحة" على إجابة خاطئة مقصودة
                            $expert->increment('gold_tasks_failed');
                            $expert->decrement('trust_score', 10);
                            $expert->refresh();

                            \App\Models\GovernanceLog::create([
                                'expert_id'          => $expert->id,
                                'task_id'            => $aiTask->id,
                                'event_type'         => 'gold_task_failed',
                                'event_data'         => json_encode([
                                    'expert_answer' => 'accepted_wrong_answer',
                                    'gold_answer'   => $aiTask->gold_answer,
                                    'source'        => 'legal_workbench',
                                ]),
                                'trust_score_before' => $trustScoreBefore,
                                'trust_score_after'  => $expert->trust_score,
                            ]);

                            // حظر تلقائي لو الـ trust score أقل من 60
                            if ($expert->trust_score < 60 && !$expert->is_banned) {
                                $expert->update([
                                    'is_banned'           => true,
                                    'banned_at'           => now(),
                                    'ban_reason'          => 'انخفض مؤشر الثقة عن 60 بسبب الفشل في أسئلة الاختبار.',
                                    'is_active'           => false,
                                    'is_active_for_hire'  => false,
                                ]);
                            }
                        }
                    }
                }

                // تحديث حالة المهمة القانونية الفرعية
                $legalTask->update([
                    'status'         => 'completed',
                    'expert_id'      => Auth::id(),
                    'completed_at'   => now(),
                    'correct_answer' => $request->is_correct ? null : $request->correct_answer,
                    'expert_comment' => $request->expert_comment,
                    'is_correct'     => $request->is_correct,
                    'time_spent'     => $request->input('time_spent'),
                ]);
            }
        });

        return response()->json([
            'success'  => true,
            'message'  => 'تم حفظ المراجعة بنجاح، شكراً لك.',
            'next_url' => route('dashboard.expert.legal_workbench')
        ]);
    }

    /**
     * Skip the current task (Return to Pending and find another)
     */
    public function skip(Request $request)
    {
        $expert = Auth::user();
        $currentId = $request->task_id;

        // 1. حفظ الـ ID الحالي في الـ session stack قبل التخطي
        $history = session()->get('legal_task_history_' . $expert->id, []);
        if ($currentId && !in_array($currentId, $history)) {
            $history[] = (int) $currentId;
            // نحتفظ بآخر 20 مهمة فقط
            if (count($history) > 20) {
                $history = array_slice($history, -20);
            }
            session()->put('legal_task_history_' . $expert->id, $history);
        }

        // 2. Return current task to Pending
        $qa = LegalQaPair::where('id', $currentId)
            ->where('reviewer_id', $expert->id)
            ->first();

        if ($qa) {
            $qa->update([
                'review_status' => 'Pending',
                'reviewer_id'   => null
            ]);
        }

        // 3. Try to find the NEXT task (ID > currentId) to avoid showing the same one
        $nextQA = LegalQaPair::where('review_status', 'Pending')
            ->whereNull('reviewer_id')
            ->where('id', '>', $currentId)
            ->first();

        // 4. If no "Next" one, just take any available Pending one that isn't the one we just skipped
        if (!$nextQA) {
            $nextQA = LegalQaPair::where('review_status', 'Pending')
                ->whereNull('reviewer_id')
                ->where('id', '!=', $currentId)
                ->first();
        }

        // 5. Lock the new one for this expert
        if ($nextQA) {
            $nextQA->update([
                'reviewer_id'   => $expert->id,
                'review_status' => 'Processing',
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Previous task navigation — يرجع للمهمة السابقة من الـ session stack
     */
    public function previous(Request $request)
    {
        $expert = Auth::user();

        // 1. Return current Processing task to Pending (بدون مسح reviewer_id من الـ stats)
        $currentQA = LegalQaPair::where('reviewer_id', $expert->id)
            ->where('review_status', 'Processing')
            ->first();

        $currentId = $currentQA?->id;

        if ($currentQA) {
            $currentQA->update([
                'review_status' => 'Pending',
                'reviewer_id'   => null,
            ]);
        }

        // 2. جيب آخر ID من الـ session stack (مع إزالته)
        $history = session()->get('legal_task_history_' . $expert->id, []);

        // إزالة الـ ID الحالي من الـ history لو موجود
        $history = array_values(array_filter($history, fn($id) => $id !== $currentId));

        $prevId = !empty($history) ? array_pop($history) : null;
        session()->put('legal_task_history_' . $expert->id, $history);

        if ($prevId) {
            $prevQA = LegalQaPair::find($prevId);
            if ($prevQA) {
                // نحتفظ بالـ reviewed_at لو كانت مراجعة سابقة (عشان الرصيد ما يتأثرش)
                $prevQA->update([
                    'review_status' => 'Processing',
                    'reviewer_id'   => $expert->id,
                    // لا نمسح reviewed_at — نتركه كما هو
                ]);
                return response()->json(['success' => true, 'found' => true]);
            }
        }

        // 3. Fallback: آخر مهمة راجعها المحامي
        $lastReviewed = LegalQaPair::where('reviewer_id', $expert->id)
            ->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id')
            ->first();

        if ($lastReviewed) {
            $lastReviewed->update([
                'review_status' => 'Processing',
                'reviewer_id'   => $expert->id,
            ]);
            return response()->json(['success' => true, 'found' => true]);
        }

        return response()->json(['success' => true, 'found' => false]);
    }

    /**
     * Expert statistics for today
     */
    private function getExpertStats($expert)
    {
        // نعد المهام اللي اتراجعت اليوم بناءً على reviewed_at بغض النظر عن الحالة الحالية
        // (حتى لو رجعت للـ Processing بعد الضغط على السابقة)
        $completedToday = LegalQaPair::where('reviewer_id', $expert->id)
            ->whereNotNull('reviewed_at')
            ->whereDate('reviewed_at', Carbon::today())
            ->count();

        return [
            'completed_today' => $completedToday,
            'earnings_today'  => $completedToday * 2.00,
            'pending_tasks'   => LegalQaPair::where('review_status', 'Pending')->count(),
        ];
    }

    /**
     * Convert an integer to its written Arabic ordinal (feminine).
     */
    private function arabicOrdinal(int $number): string
    {
        $ones = [
            1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة',
            6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة', 10 => 'العاشرة',
            11 => 'الحادية عشرة', 12 => 'الثانية عشرة', 13 => 'الثالثة عشرة', 14 => 'الرابعة عشرة',
            15 => 'الخامسة عشرة', 16 => 'السادسة عشرة', 17 => 'السابعة عشرة', 18 => 'الثامنة عشرة', 19 => 'التاسعة عشرة'
        ];
        $tens = [
            20 => 'العشرون', 30 => 'الثلاثون', 40 => 'الأربعون', 50 => 'الخمسون',
            60 => 'الستون', 70 => 'السبعون', 80 => 'الثمانون', 90 => 'التسعون'
        ];

        if ($number <= 19) return $ones[$number] ?? '';
        
        if ($number < 100) {
            $ten = (int) floor($number / 10) * 10;
            $one = $number % 10;
            if ($one === 0) return $tens[$ten];
            if ($one === 1) return 'الحادية و' . $tens[$ten];
            return $ones[$one] . ' و' . $tens[$ten];
        }

        if ($number === 100) return 'المائة';
        if ($number < 200) {
            return $this->arabicOrdinal($number - 100) . ' بعد المائة';
        }

        if ($number === 200) return 'المائتين';
        if ($number < 300) {
            return $this->arabicOrdinal($number - 200) . ' بعد المائتين';
        }

        if ($number === 300) return 'الثلاثمائة';
        if ($number < 400) {
            return $this->arabicOrdinal($number - 300) . ' بعد الثلاثمائة';
        }

        return (string) $number;
    }
}
