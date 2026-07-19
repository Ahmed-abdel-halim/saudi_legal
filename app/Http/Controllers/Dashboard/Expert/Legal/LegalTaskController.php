<?php

namespace App\Http\Controllers\Dashboard\Expert\Legal;

use App\Http\Controllers\Controller;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;
use App\Models\LegalCitation;
use App\Models\GovernanceLog;
use App\Models\TaskAssignment;
use App\Models\LegalTask;
use App\Models\AiTask;
use App\Models\AiResponse;
use App\Services\Legal\GoldStandardEnrichmentService;
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
        // Dynamic Schema Patch
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('legal_qa_pairs', 'has_custom_citations')) {
                \Illuminate\Support\Facades\Schema::table('legal_qa_pairs', function ($table) {
                    $table->boolean('has_custom_citations')->default(false);
                });
            }
        } catch (\Exception $e) {}

        $expert = Auth::user();

        // 1. Look for a QA pair currently being processed by this expert (via active TaskAssignment)
        $assignment = TaskAssignment::where('expert_id', $expert->id)
            ->active()
            ->whereHas('task.legalTask')
            ->first();

        $currentQA = null;
        if ($assignment) {
            $legalTask = $assignment->task->legalTask;
            if ($legalTask && $legalTask->source_type === 'legal_qa_pair') {
                $currentQA = LegalQaPair::with(['record', 'citations.article'])->find($legalTask->source_id);
            }
        }

        // 2. If no active task, fetch a new Pending one using consensus routing
        if (!$currentQA) {
            $skippedIds = session()->get('legal_task_history_' . $expert->id, []);

            // التحقق مما إذا كان المحامي خبيراً ذا خبرة أكبر (Senior Expert)
            $completedCount = \App\Models\AiResponse::where('expert_id', $expert->id)
                ->whereHas('task.legalTask')
                ->count();
            $isSeniorExpert = $expert->trust_score >= 90 && $completedCount >= 10;

            $nextLegalTask = LegalTask::where('source_type', 'legal_qa_pair')
                ->whereHas('task', function ($query) use ($expert, $isSeniorExpert) {
                    $query->whereIn('status', ['pending', 'in_progress'])
                        ->whereColumn('current_responses', '<', 'required_responses')
                        ->whereDoesntHave('responses', function ($q) use ($expert) {
                            $q->where('expert_id', $expert->id);
                        })
                        ->whereDoesntHave('assignments', function ($q) use ($expert) {
                            $q->where('expert_id', $expert->id);
                        });

                    // إذا لم يكن المحامي خبيراً كبيراً، يتم استبعاد المهام التي تحتاج لفك الارتباط/الترجيح (Tie-breaker)
                    if (!$isSeniorExpert) {
                        $query->where(function ($q) {
                            $q->where('required_responses', '!=', 3)
                              ->orWhere('current_responses', '!=', 2);
                        });
                    }
                })
                ->whereNotIn('source_id', $skippedIds)
                ->orderBy('id', 'asc')
                ->get()
                ->first(function ($lt) {
                    $activeCount = TaskAssignment::where('task_id', $lt->task_id)->active()->count();
                    $aiTask = $lt->task;
                    return ($activeCount + $aiTask->current_responses) < $aiTask->required_responses;
                });

            if ($nextLegalTask) {
                // Lock the task for this expert
                TaskAssignment::create([
                    'task_id'     => $nextLegalTask->task_id,
                    'expert_id'   => $expert->id,
                    'assigned_at' => now(),
                    'expires_at'  => now()->addHours(2), // Lock for 2 hours
                ]);

                // Update AiTask status if it is pending
                $aiTask = $nextLegalTask->task;
                if ($aiTask && $aiTask->status === \App\Enums\TaskStatus::Pending) {
                    $aiTask->update(['status' => \App\Enums\TaskStatus::InProgress]);
                }

                // Update the LegalQaPair status for backwards compatibility snapshot
                $currentQA = LegalQaPair::with(['record', 'citations.article'])->find($nextLegalTask->source_id);
                if ($currentQA) {
                    $currentQA->update([
                        'reviewer_id'   => $expert->id,
                        'review_status' => 'Processing',
                    ]);
                }
            }
        }

        // 3. Data mapping for the view (keeping the variable names compatible if possible)
        $task = null;
        $mentionedArticles = collect();

        if ($currentQA) {
            // Get citations from the new relational table (specifically for this QA pair)
            $citations = ($currentQA->has_custom_citations || $currentQA->citations->count() > 0)
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

            // ── Gold Standard Enrichment ──────────────────────────────────────────
            // لو سؤال اختبار: نجيب المواد ونص القضية من الـ JSONL الكبير
            $goldEnrichment = null;
            try {
                $enrichmentService = new GoldStandardEnrichmentService();
                $goldEnrichment = $enrichmentService->enrich($currentQA);
            } catch (\Exception $e) {
                // تجاهل الأخطاء — نكمل بالبيانات العادية
            }

            // لو عندنا enrichment: نحدّث الـ task بالبيانات الجديدة
            if ($goldEnrichment) {
                $task->case_text    = $goldEnrichment['case_text'] ?: $task->case_text;
                $task->wrong_answer = $goldEnrichment['wrong_answer'];
                $task->is_gold      = true;
            } else {
                $task->wrong_answer = null;
                $task->is_gold      = false;
            }
            // ─────────────────────────────────────────────────────────────────────

            $mentionedArticles = $citations->map(function($c) use ($currentQA) {
                
                $system = trim($c->system_name ?? '');

                // Only skip empty or extremely short entries (e.g. noise or empty fields)
                if (mb_strlen($system) < 3) return null;

                // استخدام article_text accessor من الـ model (يرجع النص من legal_articles أو system_name)
                $articleText = $c->article_text ?? $c->system_name;

                // حالة خاصة: مادة قانونية بدون اسم نظام محدد (مثل "المادة 84" في الـ JSONL)
                if ($c->citation_source === 'law' && ($system === 'نظام غير محدد' || empty($system))) {
                    $artLabel = $c->article_number ? "المادة {$c->article_number}" : 'مادة قانونية';
                    return (object) [
                        'id' => 'temp-' . $c->id,
                        'legislation_title' => 'مرجع قانوني',
                        'article_title' => $artLabel,
                        'content' => $articleText ?: ('نص المادة غير متوفر حالياً في قاعدة البيانات. المرجع: ' . $artLabel)
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
                            || str_contains($system, 'المحاكم التجارية')
                            || str_contains($system, 'المرافعات')
                            || str_contains($system, 'الإثبات')
                            || str_contains($system, 'الشركات')
                            || str_contains($system, 'التحكيم')
                            || str_contains($system, 'العمل')
                        )
                        || (str_contains($system, 'قانون ') || str_contains($system, 'القانون ') || $system === 'قانون')
                        || str_contains($system, 'لائحة')
                        || str_contains($system, 'مرسوم')
                        || str_contains($system, 'قرار');

                    // تقليل الحد الأقصى لطول اسم النظام إلى 120 حرفاً لتجنب الجمل الطويلة التي تسرد الوقائع
                    if ($isLawWord && mb_strlen($system) < 120) {
                        $artTitle = '';
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
                        } else {
                            // لا يوجد رقم مادة — محاولة تحديد المادة تلقائياً من محتوى السؤال والإجابة
                            $questionText = $currentQA->question ?? '';
                            $answerText = $currentQA->generated_answer ?? '';
                            $searchText = $questionText . ' ' . $answerText;
                            
                            // البحث في legal_articles عن مواد من نفس النظام
                            $matchedArticle = $this->findBestMatchingArticle($system, $searchText);
                            
                            if ($matchedArticle) {
                                return (object) [
                                    'id' => 'temp-' . $c->id,
                                    'legislation_title' => $system,
                                    'article_title' => $matchedArticle->article_title,
                                    'content' => $matchedArticle->content
                                ];
                            }
                            
                            // لم نجد مادة مطابقة — نعرضه كنص مستنبط من الحكم
                            return (object) [
                                'id' => 'temp-' . $c->id,
                                'legislation_title' => 'نص مستنبط من الحكم',
                                'article_title' => '',
                                'content' => "📌 المصدر القانوني: {$system}\n\n" . ($articleText ?: $system)
                            ];
                        }
                        
                        // إذا كان هناك نص كامل من legal_articles، استخدمه
                        // وإلا، اعرض رسالة توضيحية
                        $content = $articleText;
                        $isGeminiRetrieved = false;

                        if (false && (!$c->legal_article_id || !$c->article?->content)) { // Disabled Gemini auto-retrieval to allow manual entry
                            // محاولة استرداد المادة عبر Gemini تلقائياً
                            if (!empty($system) && $system !== 'نظام غير محدد' && $c->article_number) {
                                try {
                                    $apiKey = trim(config('services.gemini.key'));
                                    if (!empty($apiKey)) {
                                        $prompt = "أنت خبير قانوني سعودي محترف.
المطلوب منك هو إيجاد وجلب النص الكامل والرسمي للمادة القانونية التالية من الأنظمة السعودية الرسمية بأحدث تعديلاتها.

اسم النظام: {$system}
رقم المادة: {$c->article_number}

شروط هامة جداً:
1. اكتب نص المادة فقط بشكل مباشر دون أي شروحات، ودون أي مقدمات (مثال: لا تكتب 'تفضل النص' أو 'إليك المادة').
2. يجب أن يكون النص دقيقاً ومطابقاً للرسمي.
3. إذا لم تكن متأكداً بنسبة 100% من النص الدقيق للمادة، أو إذا كان النظام غير معروف، اكتب كلمة 'غير متوفر' فقط ولا تكتب أي شيء آخر.

اكتب نص المادة الآن:";

                                        $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                                            ->timeout(15)
                                            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, [
                                                'contents' => [['parts' => [['text' => $prompt]]]],
                                            ]);

                                        if ($response->successful()) {
                                            $retrieved = trim($response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '');
                                            
                                            if (!empty($retrieved) && mb_strtolower($retrieved) !== 'غير متوفر' && mb_strlen($retrieved) > 15) {
                                                // 1. جلب أو توليد legislation_id لنفس النظام
                                                $existingArticle = \App\Models\LegalArticle::where('legislation_title', $system)->first();
                                                $legislationId = $existingArticle ? $existingArticle->legislation_id : 'sa-law-' . md5($system);

                                                // 2. حفظ المادة في قاعدة البيانات
                                                $newArticle = \App\Models\LegalArticle::create([
                                                    'legislation_id'    => $legislationId,
                                                    'legislation_title' => $system,
                                                    'article_title'     => $artTitle,
                                                    'content'           => $retrieved,
                                                    'reference_id'      => 'art' . $c->article_number,
                                                ]);

                                                // 3. تحديث الإحالة لربطها بالمادة الجديدة
                                                $c->update(['legal_article_id' => $newArticle->id]);
                                                $c->setRelation('article', $newArticle);

                                                // 4. تحديث النص
                                                $content = $retrieved;
                                                $isGeminiRetrieved = true;
                                            }
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // تجاهل الأخطاء والمتابعة بالسلوك الافتراضي لضمان استقرار التطبيق
                                }
                            }
                        }

                        if (!$isGeminiRetrieved && (!$c->legal_article_id || !$c->article?->content)) {
                            $content = "⚠️ نص هذه المادة غير متوفر حالياً في قاعدة البيانات.\n\n📌 المرجع: {$system} {$artSuffix}\n\n💡 يمكنك الرجوع للنص الرسمي للنظام أو استخدام نص القضية أدناه للمراجعة.";
                        }
                        
                        return (object) [
                            'id' => 'temp-' . $c->id,
                            'legislation_title' => $system,
                            'article_title' => $artTitle,
                            'content' => $content
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

            // دمج المصادر المتكررة تماماً فقط (نفس النظام ونفس المادة)
            // مواد مختلفة من نفس النظام تظهر كبطاقات منفصلة
            // استثناء: كل مستندات "وقائع / أسباب الحكم" تُدمج في بطاقة واحدة
            $groupedArticles = collect();
            foreach ($mentionedArticles as $art) {
                // دمج كل مستندات الوقائع في key ثابت واحد
                if (trim($art->legislation_title) === 'مستند وقائع / أسباب الحكم') {
                    $key = 'وقائع|||مجمع';
                } else {
                    // الـ key يشمل الـ id عشان المواد المختلفة من نفس النظام ما تتدمجش
                    $key = trim($art->legislation_title) . '|||' . trim($art->article_title ?? '') . '|||' . $art->id;
                }

                if (!$groupedArticles->has($key)) {
                    $groupedArticles->put($key, (object) [
                        'id' => $art->id,
                        'legislation_title' => $art->legislation_title,
                        'article_title' => $key === 'وقائع|||مجمع' ? '' : ($art->article_title ?? ''),
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

            // ── إضافة المواد من الـ JSONL لو gold standard ──────────────────────
            // ملاحظة: المواد في الـ JSONL غالباً مجرد references (مثل "المادة 20 من نظام...")
            // نتحقق إذا كانت نصوص كاملة (أكثر من 100 حرف) قبل إضافتها
            if ($goldEnrichment && !empty($goldEnrichment['legal_articles'])) {
                $jsonlArticles = collect($goldEnrichment['legal_articles'])
                    ->filter(function($articleText) {
                        // نتجاهل الـ references القصيرة (أقل من 100 حرف)
                        // ونعرض فقط النصوص الكاملة
                        return mb_strlen(trim($articleText)) > 100;
                    })
                    ->map(function($articleText, $i) {
                        return (object) [
                            'id'                => 'jsonl-' . $i,
                            'legislation_title' => 'مادة قانونية (من نص القضية)',
                            'article_title'     => '',
                            'content'           => $articleText,
                        ];
                    });
                
                if ($jsonlArticles->isNotEmpty()) {
                    $mentionedArticles = $mentionedArticles->concat($jsonlArticles);
                }
            }
            // ─────────────────────────────────────────────────────────────────────
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

        return view('dashboard.expert.legal.workbench', [
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

        // جلب ردود هذا الخبير لمهام التحقق القانونية
        $responses = AiResponse::where('expert_id', $expert->id)
            ->whereHas('task.legalTask')
            ->with(['task.legalTask.qaPair.record.citations', 'task.legalTask.qaPair.citations'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // تحويل الردود إلى كائنات متوافقة مع العرض الفردي للـ Blade
        $reviews = $responses->through(function ($response) {
            $legalTask = $response->task->legalTask;
            $qa = null;
            if ($legalTask && $legalTask->source_type === 'legal_qa_pair') {
                $qa = $legalTask->qaPair;
            }
            
            if (!$qa) {
                $qa = new LegalQaPair();
                $qa->id = $legalTask->id ?? 0;
            }

            // تخصيص القيم للخبير الحالي لتفادي التداخل عند مراجعة محامين آخرين
            $qa->reviewed_at = $response->created_at;
            $qa->review_status = $response->action === 'accepted' ? 'Approved' : 'Modified';
            $qa->question = $legalTask->question ?? $qa->question;
            $qa->generated_answer = $legalTask->proposed_answer ?? $qa->generated_answer;
            $qa->corrected_answer = $response->corrected_data;

            return $qa;
        });

        return view('dashboard.expert.legal.history', compact('reviews'));
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
            'correct_law_references' => 'nullable|array',
            'correct_law_references.*.system' => 'nullable|string|max:65000',
            'correct_law_references.*.article' => 'nullable|string|max:65000',
            'correct_law_references.*.article_id' => 'nullable|integer',
        ]);

        $qa = LegalQaPair::findOrFail($request->task_id);

        $legalTask = \App\Models\LegalTask::where('source_id', $qa->id)
            ->where('source_type', 'legal_qa_pair')
            ->firstOrFail();

        // التحقق من التخصيص النشط للخبير الحالي
        $assignment = TaskAssignment::where('task_id', $legalTask->task_id)
            ->where('expert_id', Auth::id())
            ->active()
            ->first();

        if (!$assignment) {
            // التحقق مما إذا كان قد تم إرسال الرد بالفعل وتحديث المهمة (لتفادي أخطاء نقر الزر المتكرر أو انتهاء الجلسة المؤقت)
            $alreadySubmitted = \App\Models\AiResponse::where('task_id', $legalTask->task_id)
                ->where('expert_id', Auth::id())
                ->exists();

            if ($alreadySubmitted) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'تم حفظ المراجعة بنجاح، شكراً لك.',
                    'next_url' => route('dashboard.expert.legal_workbench')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'عذراً، لم يتم تخصيص هذه المهمة لك أو انتهى وقت صلاحيتها.'
            ], 403);
        }

        $status = $request->is_correct ? 'Approved' : 'Modified';

        // تجميع المراجع المتعددة لحفظها كقيم مفصولة بفواصل للرجوع التاريخي
        $systems = [];
        $articles = [];
        if ($request->has('correct_law_references')) {
            foreach ($request->correct_law_references as $ref) {
                if (!empty($ref['system'])) {
                    $systems[] = trim($ref['system']);
                }
                if (!empty($ref['article'])) {
                    $articles[] = trim($ref['article']);
                }
            }
        }
        $correctLawSystem = !empty($systems) ? implode('، ', $systems) : null;
        $correctLawArticle = !empty($articles) ? implode('، ', $articles) : null;

        DB::transaction(function () use ($qa, $legalTask, $status, $request, $assignment, $correctLawSystem, $correctLawArticle) {
            $qa->update([
                'review_status'    => $status,
                'reviewer_id'      => Auth::id(), // تحديث الخبير الحالي كآخر لقطة مراجعة
                'corrected_answer' => $request->is_correct ? null : $request->correct_answer,
                'reviewed_at'      => now(),
                'time_spent'       => $request->input('time_spent'),
            ]);

            // حفظ المراجع القانونية الجديدة في جدول الإحالات لربطها بالسؤال
            if (!$request->is_correct && $request->has('correct_law_references')) {
                if (!$qa->has_custom_citations) {
                    $fallbackCitations = \App\Models\LegalCitation::where('legal_record_id', $qa->legal_record_id)->get();
                    foreach ($fallbackCitations as $other) {
                        \App\Models\LegalCitation::create([
                            'legal_record_id'  => $qa->legal_record_id,
                            'legal_qa_pair_id' => $qa->id,
                            'system_name'      => $other->system_name,
                            'article_number'   => $other->article_number,
                            'citation_source'  => $other->citation_source,
                            'legal_article_id' => $other->legal_article_id,
                            'added_by_expert'  => $other->added_by_expert,
                        ]);
                    }
                    $qa->update(['has_custom_citations' => true]);
                }

                foreach ($request->correct_law_references as $ref) {
                    if (!empty($ref['system']) || !empty($ref['article'])) {
                        $articleId = $ref['article_id'] ?? null;
                        if (!$articleId && !empty($ref['article']) && !empty($ref['system'])) {
                            $articleId = $this->findLegalArticleId($ref['article'], $ref['system']);
                        }

                        \App\Models\LegalCitation::create([
                            'legal_record_id'  => $qa->legal_record_id,
                            'legal_qa_pair_id' => $qa->id,
                            'system_name'      => $ref['system'] ?? 'نظام غير محدد',
                            'article_number'   => $ref['article'] ?? '',
                            'citation_source'  => 'law',
                            'added_by_expert'  => true,
                            'legal_article_id' => $articleId,
                        ]);
                    }
                }
            }

            // Optional: Update record tags if they changed
            if ($request->has('tags')) {
                $qa->record->update(['tags' => $request->tags]);
            }

            // الربط مع نظام الحوكمة الأساسي (Governance System)

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
                        'reward_amount'    => 0.00, // تم استبدال العائد المالي بنظام النقاط
                        'time_spent'       => $request->input('time_spent'),
                    ]
                );

                // منح الخبير نقطة واحدة/توكنز للمحفظة بعد التدقيق
                $expertUser = Auth::user();
                DB::table('users')->where('id', $expertUser->id)->increment('audit_tokens', 1);



                // تحديث حالة المهمة القانونية الفرعية
                $legalTask->update([
                    'status'              => 'completed',
                    'expert_id'           => Auth::id(),
                    'completed_at'        => now(),
                    'correct_answer'      => $request->is_correct ? null : $request->correct_answer,
                    'expert_comment'      => $request->expert_comment,
                    'is_correct'          => $request->is_correct,
                    'time_spent'          => $request->input('time_spent'),
                    'correct_law_system'  => $correctLawSystem,
                    'correct_law_article' => $correctLawArticle,
                ]);
            }

            // إطلاق سراح التخصيص
            $assignment->delete();
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

        // 2. إيجاد المهمة القانونية وإلغاء تخصيصها للمحامي الحالي
        $legalTask = LegalTask::where('source_id', $currentId)
            ->where('source_type', 'legal_qa_pair')
            ->first();

        if ($legalTask) {
            $assignment = TaskAssignment::where('task_id', $legalTask->task_id)
                ->where('expert_id', $expert->id)
                ->first();

            if ($assignment) {
                $assignment->delete();
            }

            // إعادة السؤال إلى الحالة Pending وإزالة reviewer_id للتوافق الرجعي
            $qa = LegalQaPair::find($currentId);
            if ($qa && $qa->reviewer_id == $expert->id && $qa->review_status === 'Processing') {
                $qa->update([
                    'review_status' => 'Pending',
                    'reviewer_id'   => null
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Previous task navigation — يرجع للمهمة السابقة من الـ session stack
     */
    public function previous(Request $request)
    {
        $expert = Auth::user();

        // 1. إيجاد وحذف التخصيص الحالي للخبير الحالي (إطلاق سراح المهمة الحالية)
        $currentAssignment = TaskAssignment::where('expert_id', $expert->id)
            ->active()
            ->whereHas('task.legalTask')
            ->first();

        $currentId = null;
        if ($currentAssignment) {
            $legalTask = $currentAssignment->task->legalTask;
            if ($legalTask && $legalTask->source_type === 'legal_qa_pair') {
                $currentId = $legalTask->source_id;
                $qa = LegalQaPair::find($currentId);
                if ($qa && $qa->reviewer_id == $expert->id && $qa->review_status === 'Processing') {
                    $qa->update([
                        'review_status' => 'Pending',
                        'reviewer_id'   => null,
                    ]);
                }
            }
            $currentAssignment->delete();
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
                $prevLegalTask = LegalTask::where('source_id', $prevId)->where('source_type', 'legal_qa_pair')->first();
                if ($prevLegalTask) {
                    // حجز المهمة مجدداً للمحامي
                    TaskAssignment::create([
                        'task_id'     => $prevLegalTask->task_id,
                        'expert_id'   => $expert->id,
                        'assigned_at' => now(),
                        'expires_at'  => now()->addHours(2),
                    ]);

                    $prevQA->update([
                        'review_status' => 'Processing',
                        'reviewer_id'   => $expert->id,
                    ]);
                    return response()->json(['success' => true, 'found' => true]);
                }
            }
        }

        // 3. Fallback: آخر مهمة راجعها المحامي من جدول الردود
        $lastResponse = \App\Models\AiResponse::where('expert_id', $expert->id)
            ->whereHas('task.legalTask')
            ->orderByDesc('created_at')
            ->first();

        if ($lastResponse) {
            $prevLegalTask = $lastResponse->task->legalTask;
            if ($prevLegalTask && $prevLegalTask->source_type === 'legal_qa_pair') {
                $prevQA = LegalQaPair::find($prevLegalTask->source_id);
                if ($prevQA) {
                    // حجز المهمة مجدداً للمحامي ليتمكن من تعديلها
                    TaskAssignment::create([
                        'task_id'     => $prevLegalTask->task_id,
                        'expert_id'   => $expert->id,
                        'assigned_at' => now(),
                        'expires_at'  => now()->addHours(2),
                    ]);

                    $prevQA->update([
                        'review_status' => 'Processing',
                        'reviewer_id'   => $expert->id,
                    ]);
                    return response()->json(['success' => true, 'found' => true]);
                }
            }
        }

        return response()->json(['success' => true, 'found' => false]);
    }

    public function searchSystems(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            $systems = \App\Models\LegalArticle::select('legislation_title')
                ->groupBy('legislation_title')
                ->orderBy('legislation_title', 'asc')
                ->pluck('legislation_title');
        } else {
            $systems = \App\Models\LegalArticle::select('legislation_title')
                ->where('legislation_title', 'LIKE', '%' . $query . '%')
                ->groupBy('legislation_title')
                ->orderBy('legislation_title', 'asc')
                ->pluck('legislation_title');
        }

        return response()->json($systems);
    }

    /**
     * Search articles within a system
     */
    public function searchArticles(Request $request)
    {
        $system = $request->input('system');
        $query = $request->input('q');

        if (empty($system)) {
            return response()->json([]);
        }

        $dbQuery = \App\Models\LegalArticle::where('legislation_title', $system);
        
        if (!empty($query)) {
            $normalizedQuery = $this->convertArabicNumbers($query);
            
            $dbQuery->where(function ($q) use ($query, $normalizedQuery) {
                $q->where('article_title', 'LIKE', '%' . $query . '%')
                  ->orWhere('article_title', 'LIKE', '%' . $normalizedQuery . '%');

                if (is_numeric($normalizedQuery)) {
                    $ordinal = $this->arabicOrdinal((int) $normalizedQuery);
                    $q->orWhere('article_title', 'LIKE', '%' . $ordinal . '%')
                      ->orWhere('article_title', 'LIKE', '% ' . $normalizedQuery . ' %')
                      ->orWhere('article_title', 'LIKE', '%(' . $normalizedQuery . ')%')
                      ->orWhere('article_title', 'LIKE', '%المادة ' . $normalizedQuery . '%');
                } else {
                    $parsedNum = $this->parseWrittenArabicNumber($query);
                    if ($parsedNum) {
                        $q->orWhere('article_title', 'LIKE', '%' . $parsedNum . '%')
                          ->orWhere('article_title', 'LIKE', '%' . $this->arabicOrdinal((int) $parsedNum) . '%');
                    }
                }

                $q->orWhere('content', 'LIKE', '%' . $query . '%')
                  ->orWhere('content', 'LIKE', '%' . $normalizedQuery . '%');
            });
        }

        $articles = $dbQuery->orderBy('id', 'asc')->get(['id', 'article_title', 'content']);

        return response()->json($articles);
    }

    /**
     * Convert Eastern Arabic numerals to Western Arabic.
     */
    private function convertArabicNumbers(string $str): string
    {
        return str_replace(
            ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9'],
            $str
        );
    }

    /**
     * Parse written Arabic ordinal numbers to integer string.
     */
    private function parseWrittenArabicNumber(string $text): ?string
    {
        $text = trim($text);
        $map = [
            'الأولى' => '1', 'الأول' => '1',
            'الثانية' => '2', 'الثاني' => '2',
            'الثالثة' => '3', 'الثالث' => '3',
            'الرابعة' => '4', 'الرابع' => '4',
            'الخامسة' => '5', 'الخامس' => '5',
            'السادسة' => '6', 'السادس' => '6',
            'السابعة' => '7', 'السابع' => '7',
            'الثامنة' => '8', 'الثامن' => '8',
            'التاسعة' => '9', 'التاسع' => '9',
            'العاشرة' => '10', 'العاشر' => '10',
            'الحادية عشرة' => '11', 'الحادي عشر' => '11',
            'الثانية عشرة' => '12', 'الثاني عشر' => '12',
            'الثالثة عشرة' => '13', 'الثالث عشر' => '13',
            'الرابعة عشرة' => '14', 'الرابع عشر' => '14',
            'الخامسة عشرة' => '15', 'الخامس عشر' => '15',
            'السادسة عشرة' => '16', 'السادس عشر' => '16', 'السادسة عشر' => '16',
            'السابعة عشرة' => '17', 'السابع عشر' => '17', 'السابعة عشر' => '17',
            'الثامنة عشرة' => '18', 'الثامن عشر' => '18', 'الثامنة عشر' => '18',
            'التاسعة عشرة' => '19', 'التاسع عشر' => '19', 'التاسعة عشر' => '19',
            'العشرون' => '20', 'العشرين' => '20',
            'الحادية والعشرون' => '21', 'الحادي والعشرون' => '21', 'الحادية والعشرين' => '21',
            'الثانية والعشرون' => '22', 'الثاني والعشرون' => '22', 'الثانية والعشرين' => '22',
            'الثالثة والعشرون' => '23', 'الثالث والعشرون' => '23', 'الثالثة والعشرين' => '23',
            'الرابعة والعشرون' => '24', 'الرابع والعشرون' => '24', 'الرابعة والعشرين' => '24',
            'الخامسة والعشرون' => '25', 'الخامس والعشرون' => '25', 'الخامسة والعشرين' => '25',
            'السادسة والعشرون' => '26', 'السادس والعشرون' => '26', 'السادسة والعشرين' => '26',
            'السابعة والعشرون' => '27', 'السابع والعشرون' => '27', 'السابعة والعشرين' => '27',
            'الثامنة والعشرون' => '28', 'الثامن والعشرون' => '28', 'الثامنة والعشرين' => '28',
            'التاسعة والعشرون' => '29', 'التاسع والعشرون' => '29', 'التاسعة والعشرين' => '29',
            'الثلاثون' => '30', 'الثلاثين' => '30',
            'الحادية والثلاثون' => '31', 'الحادية والثلاثين' => '31',
            'الثانية والثلاثون' => '32', 'الثانية والثلاثين' => '32',
            'الخمسون' => '50', 'الخمسين' => '50',
            'الحادية والخمسون' => '51', 'الحادية والخمسين' => '51',
            'الخامسة والخمسون' => '55', 'الخامسة والخمسين' => '55',
            'السادسة والخمسون' => '56', 'السادسة والخمسين' => '56',
            'الثامنة والخمسون' => '58', 'الثامنة والخمسين' => '58',
            'السبعون' => '70', 'السبعين' => '70',
            'الثالثة والسبعون' => '73', 'الثالثة والسبعين' => '73',
            'السادسة والسبعون' => '76', 'السادسة والسبعين' => '76',
            'الثامنة والسبعون' => '78', 'الثامنة والسبعين' => '78',
            'التسعون' => '90', 'التسعين' => '90',
            'العاشرة بعد المائة' => '110',
            'الرابعة والستون بعد المائة' => '164', 'الرابعة والستين بعد المائة' => '164',
            'الثانية عشرة بعد المائتين' => '212', 'الثانية عشرة بعد المائتان' => '212'
        ];

        uksort($map, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($map as $word => $num) {
            if (str_contains($text, $word)) {
                return $num;
            }
        }

        return null;
    }

    /**
     * Search legal_articles for a matching article.
     */
    private function findLegalArticleId(?string $articleNumber, ?string $systemName): ?int
    {
        if (!$articleNumber) {
            return null;
        }

        $systemName = trim($systemName);
        if (empty($systemName)) {
            return null;
        }

        // Avoid matching non-law references
        $isNonLaw = preg_match('/(?:العقد|اتفاق|محضر|إفادة|تقرير|بينة|سند|فاتورة|كشف|خطاب|مبدأ|قاعدة شرعية|فقه|أسباب|منطوق|تاريخ|مستنبط|استنباط|قضائي مستقر)/ui', $systemName);
        if ($isNonLaw) {
            return null;
        }

        $query = \App\Models\LegalArticle::query();

        // 1. Match the legislation/system name
        $synonyms = [
            'الإثبات'            => ['نظام الإثبات', 'قانون الإثبات'],
            'المحاكم التجارية'   => ['نظام المحاكم التجارية'],
            'نظام المحاكم التجارية' => ['المحاكم التجارية'],
            'المرافعات الشرعية'  => ['نظام المرافعات الشرعية'],
            'نظام المرافعات الشرعية' => ['المرافعات الشرعية'],
            'المعاملات المدنية'  => ['نظام المعاملات المدنية'],
            'نظام المعاملات المدنية' => ['المعاملات المدنية'],
            'الشركات'            => ['نظام الشركات'],
            'نظام الشركات'       => ['الشركات'],
            'التحكيم'            => ['نظام التحكيم'],
            'نظام التحكيم'       => ['التحكيم'],
            'العمل'              => ['نظام العمل'],
            'نظام العمل'         => ['العمل'],
        ];

        $searchTerms = [$systemName];
        foreach ($synonyms as $key => $names) {
            if (str_contains($systemName, $key)) {
                $searchTerms = array_merge($searchTerms, $names);
            }
        }

        $query->where(function($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('legislation_title', $term)
                  ->orWhere('legislation_title', 'LIKE', "%{$term}%");
            }
        });

        // 2. Match the article number exactly or as ordinal
        $textNum = is_numeric($articleNumber) ? $this->arabicOrdinal((int) $articleNumber) : null;

        $query->where(function($q) use ($articleNumber, $textNum) {
            $exactTitles = [];
            
            $exactTitles[] = "المادة {$articleNumber}";
            $exactTitles[] = "المادة ({$articleNumber})";
            $exactTitles[] = "المادة {$articleNumber} مكرر";
            $exactTitles[] = "المادة ({$articleNumber}) مكرر";
            
            if ($textNum) {
                $exactTitles[] = "المادة {$textNum}";
                $exactTitles[] = "المادة ({$textNum})";
                $exactTitles[] = "المادة {$textNum} مكرر";
            }

            $q->whereIn('article_title', $exactTitles);
            
            foreach ($exactTitles as $title) {
                $q->orWhere('article_title', 'LIKE', $title);
            }

            $q->orWhere('content', 'LIKE', "%المادة {$articleNumber}%")
              ->orWhere('content', 'LIKE', "%المادة ({$articleNumber})%");
              
            if ($textNum) {
                $q->orWhere('content', 'LIKE', "%المادة {$textNum}%")
                  ->orWhere('content', 'LIKE', "%المادة ({$textNum})%");
            }
        });

        $articles = $query->get();
        $matchedArticle = null;
        
        foreach ($articles as $art) {
            $title = trim($art->article_title);
            if ($textNum && ($title === "المادة {$textNum}" || $title === "المادة ({$textNum})")) {
                $matchedArticle = $art;
                break;
            }
            if ($title === "المادة {$articleNumber}" || $title === "المادة ({$articleNumber})") {
                $matchedArticle = $art;
                break;
            }
        }

        if (!$matchedArticle && $articles->isNotEmpty()) {
            foreach ($articles as $art) {
                $title = trim($art->article_title);
                
                if ($textNum && $articleNumber % 10 === 0) {
                    if (str_contains($title, 'و' . $textNum)) {
                        continue;
                    }
                }

                $matchedArticle = $art;
                break;
            }
        }

        return $matchedArticle?->id;
    }

    /**
     * Expert statistics for today
     */
    private function getExpertStats($expert)
    {
        // حساب المراجعات التي قدمها الخبير الحالي اليوم من جدول الردود الفردية لمنع التداخل
        $completedToday = AiResponse::where('expert_id', $expert->id)
            ->whereHas('task.legalTask')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $completedCount = \App\Models\AiResponse::where('expert_id', $expert->id)
            ->whereHas('task.legalTask')
            ->count();
        $isSeniorExpert = $expert->trust_score >= 90 && $completedCount >= 10;

        // حساب عدد المهام التوافقية المتاحة للخبير الحالي
        $pendingTasks = LegalTask::where('source_type', 'legal_qa_pair')
            ->whereHas('task', function ($query) use ($expert, $isSeniorExpert) {
                $query->whereIn('status', ['pending', 'in_progress'])
                    ->whereColumn('current_responses', '<', 'required_responses')
                    ->whereDoesntHave('responses', function ($q) use ($expert) {
                        $q->where('expert_id', $expert->id);
                    })
                    ->whereDoesntHave('assignments', function ($q) use ($expert) {
                        $q->where('expert_id', $expert->id);
                    });

                // تصفية المهام التي تحتاج لترجيح/حسم إذا لم يكن المحامي خبيراً كبيراً
                if (!$isSeniorExpert) {
                    $query->where(function ($q) {
                        $q->where('required_responses', '!=', 3)
                          ->orWhere('current_responses', '!=', 2);
                    });
                }
            })
            ->count();

        return [
            'completed_today' => $completedToday,
            'earnings_today'  => $completedToday * 0.25,
            'pending_tasks'   => $pendingTasks,
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

    /**
     * Find the best matching legal article from the database based on question/answer keywords.
     * Used when a citation references a law by name but without a specific article number.
     */
    private function findBestMatchingArticle(string $systemName, string $searchText): ?\App\Models\LegalArticle
    {
        // استخراج الكلمات المفتاحية من نص السؤال والإجابة (كلمات أكثر من 3 حروف)
        $stopWords = ['من', 'في', 'على', 'إلى', 'عن', 'مع', 'هذا', 'هذه', 'ذلك', 'تلك', 'التي', 'الذي', 'التي',
                       'كان', 'كانت', 'يكون', 'أن', 'إن', 'لا', 'ما', 'هل', 'كيف', 'متى', 'أين', 'لماذا',
                       'بعد', 'قبل', 'بين', 'حتى', 'عند', 'منذ', 'ثم', 'أو', 'بل', 'لكن', 'وقد', 'فقد',
                       'ولا', 'فلا', 'أما', 'إذا', 'لو', 'قد', 'كما', 'أيضا', 'أيضاً', 'عليه', 'عليها',
                       'فيه', 'فيها', 'منه', 'منها', 'به', 'بها', 'له', 'لها', 'وهو', 'وهي'];
        
        // تنظيف النص واستخراج كلمات مفتاحية
        $words = preg_split('/[\s,،.؟?!؛:;()\[\]{}«»"\'"]+/u', $searchText);
        $keywords = [];
        foreach ($words as $word) {
            $clean = trim($word);
            // كلمات أطول من 3 حروف ومش من الـ stop words
            if (mb_strlen($clean) > 3 && !in_array($clean, $stopWords)) {
                $keywords[] = $clean;
            }
        }
        
        if (empty($keywords)) return null;
        
        // البحث في legal_articles عن مواد من نفس النظام
        $articles = \App\Models\LegalArticle::where('legislation_title', $systemName)->get();
        
        if ($articles->isEmpty()) {
            // محاولة بحث مرن (LIKE)
            $articles = \App\Models\LegalArticle::where('legislation_title', 'LIKE', '%' . $systemName . '%')->get();
        }
        
        if ($articles->isEmpty()) return null;
        
        // حساب نقاط التطابق لكل مادة
        $bestScore = 0;
        $bestArticle = null;
        
        foreach ($articles as $article) {
            $score = 0;
            $content = $article->content ?? '';
            
            foreach ($keywords as $keyword) {
                // عدد مرات ظهور الكلمة في محتوى المادة
                $count = mb_substr_count($content, $keyword);
                if ($count > 0) {
                    $score += $count;
                }
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestArticle = $article;
            }
        }
        
        // نقبل النتيجة فقط لو فيه تطابق معقول (3 كلمات على الأقل)
        if ($bestScore >= 3 && $bestArticle) {
            return $bestArticle;
        }
        
        return null;
    }

    /**
     * Delete/Decouple a citation from the current QA task.
     */
    public function deleteCitation(Request $request)
    {
        $request->validate([
            'task_id'     => 'required|exists:legal_qa_pairs,id',
            'citation_id' => 'required|string',
        ]);

        $expert = Auth::user();
        $qa = LegalQaPair::findOrFail($request->task_id);

        // Verify task assignment
        $legalTask = \App\Models\LegalTask::where('source_id', $qa->id)
            ->where('source_type', 'legal_qa_pair')
            ->firstOrFail();

        $assignment = TaskAssignment::where('task_id', $legalTask->task_id)
            ->where('expert_id', $expert->id)
            ->active()
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لم يتم تخصيص هذه المهمة لك أو انتهى وقت صلاحيتها.'
            ], 403);
        }

        $citationIdStr = $request->citation_id;
        if (!str_starts_with($citationIdStr, 'temp-')) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا يمكن حذف هذا المرجع.'
            ], 400);
        }

        $citationId = (int) str_replace('temp-', '', $citationIdStr);
        $citation = LegalCitation::find($citationId);

        if (!$citation) {
            return response()->json([
                'success' => false,
                'message' => 'المرجع القانوني غير موجود.'
            ], 404);
        }

        DB::transaction(function () use ($qa, $citation, $citationId) {
            // Check if the citation belongs directly to this QA pair
            if ($citation->legal_qa_pair_id == $qa->id) {
                // It belongs directly to this QA pair -> we can delete it directly
                $citation->delete();
            } else {
                // It belongs to the parent record (fallback).
                // We need to copy other citations to this QA pair, excluding the deleted one.
                $otherCitations = LegalCitation::where('legal_record_id', $qa->legal_record_id)
                    ->where('id', '!=', $citationId)
                    ->get();

                foreach ($otherCitations as $other) {
                    LegalCitation::create([
                        'legal_record_id'  => $qa->legal_record_id,
                        'legal_qa_pair_id' => $qa->id,
                        'system_name'      => $other->system_name,
                        'article_number'   => $other->article_number,
                        'citation_source'  => $other->citation_source,
                        'legal_article_id' => $other->legal_article_id,
                        'added_by_expert'  => $other->added_by_expert,
                    ]);
                }
            }

            // Mark this QA pair as having customized citations
            $qa->update(['has_custom_citations' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المرجع بنجاح.'
        ]);
    }
}
