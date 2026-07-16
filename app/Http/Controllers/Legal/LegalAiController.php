<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Legal\LegalSearchService;
use App\Services\AzureSearchService;
use App\Services\QdrantSearchService;
use App\Services\Legal\LegalReferenceService;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LegalAiController extends Controller
{
    protected $searchService;
    protected $azureService;
    protected $qdrantService;
    protected $referenceService;

    public function __construct(
        LegalSearchService    $searchService,
        AzureSearchService    $azureService,
        QdrantSearchService   $qdrantService,
        LegalReferenceService $referenceService
    ) {
        $this->searchService   = $searchService;
        $this->azureService    = $azureService;
        $this->qdrantService   = $qdrantService;
        $this->referenceService = $referenceService;
    }

    public function index()
    {
        return view('saudi_legal.chat');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API ENDPOINTS FOR CHAT HISTORY & SIDEBAR
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * جلب سجل المحادثات الخاصة بالمستخدم (للـ Sidebar)
     */
    public function getConversations(Request $request)
    {
        $userId = auth()->id();
        $conversations = collect();

        if ($userId) {
            $conversations = AiConversation::where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->get(['uuid', 'title', 'updated_at']);
        } else {
            // جلب المحادثات من جلسة الزائر (Guest Session) ومن الـ LocalStorage الممرر
            $sessionUuids = session()->get('ai_conversations', []);
            
            $guestUuids = [];
            if ($request->has('guest_uuids')) {
                $guestUuids = explode(',', $request->input('guest_uuids'));
                $guestUuids = array_filter($guestUuids, fn($u) => !empty($u) && Str::isUuid($u));
            }
            
            $allUuids = array_unique(array_merge($sessionUuids, $guestUuids));
            
            // تحديث الجلسة لمطابقتها مع التزامن
            session()->put('ai_conversations', $allUuids);

            if (! empty($allUuids)) {
                $conversations = AiConversation::whereIn('uuid', $allUuids)
                    ->orderBy('updated_at', 'desc')
                    ->get(['uuid', 'title', 'updated_at']);
            }
        }

        $messageCount = 0;
        $limit = 10;
        $this->checkMessageLimit($request, $messageCount, $limit);
        $referralLink = auth()->check() ? route('register.company', ['ref' => auth()->id()]) : null;

        return response()->json([
            'conversations' => $conversations,
            'usage' => [
                'count'        => $messageCount,
                'limit'        => $limit,
                'remaining'    => max(0, $limit - $messageCount),
                'is_logged_in' => auth()->check(),
                'referral_link'=> $referralLink,
            ]
        ]);
    }

    /**
     * تحميل رسائل محادثة معينة بواسطة الـ UUID
     */
    public function getMessages($uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        // حماية أمنية: التأكد من أن المحادثة تخص المستخدم المسجل
        if ($conversation->user_id && $conversation->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بمشاهدة هذه المحادثة.');
        }

        return response()->json([
            'conversation' => [
                'uuid'  => $conversation->uuid,
                'title' => $conversation->title,
            ],
            'messages' => $conversation->messages->map(fn($m) => [
                'role'      => $m->role,
                'message'   => $m->message,
                'citations' => $m->citations,
            ]),
        ]);
    }

    /**
     * حذف محادثة معينة
     */
    public function deleteConversation($uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        if ($conversation->user_id && $conversation->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذه المحادثة.');
        }

        $conversation->delete();

        // إزالتها من جلسة الزائر إذا وجُدت
        $uuids = session()->get('ai_conversations', []);
        if (($key = array_search($uuid, $uuids)) !== false) {
            unset($uuids[$key]);
            session()->put('ai_conversations', array_values($uuids));
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CORE CHAT & RAG ENGINE
    // ─────────────────────────────────────────────────────────────────────────

    public function ask(Request $request)
    {
        set_time_limit(180); // منع انتهاء الوقت عند بطء الاتصال بالشبكة

        $request->validate([
            'question'          => 'required|string|max:1000',
            'conversation_uuid' => 'nullable|string|uuid',
        ]);

        $question = $request->question;
        $uuid     = $request->conversation_uuid;

        // 0. التحقق من حد الرسائل والمحادثات المسموح بها للمستخدم
        $messageCount = 0;
        $limit = 10;
        $this->checkMessageLimit($request, $messageCount, $limit);

        if ($messageCount >= $limit) {
            $isLoggedIn = auth()->check();
            return response()->json([
                'error'         => 'limit_reached',
                'limit_type'    => $isLoggedIn ? 'referral_required' : 'registration_required',
                'message'       => $isLoggedIn
                    ? 'لقد وصلت إلى الحد الأقصى للمحادثات المجانية للأعضاء (20 رسالة). ادعُ صديقاً لفتح 20 رسالة إضافية!'
                    : 'لقد وصلت إلى الحد الأقصى للمحادثات للزوار (10 رسائل). يرجى تسجيل بياناتك لفتح 10 رسائل إضافية مجاناً!',
                'referral_link' => $isLoggedIn ? route('register.company', ['ref' => auth()->id()]) : null,
            ], 403);
        }

        // 1. تحديد أو إنشاء المحادثة
        $conversation = null;
        if ($uuid) {
            $conversation = AiConversation::where('uuid', $uuid)->first();
        }

        if (! $conversation) {
            $uuid = (string) Str::uuid();
            $title = mb_substr($question, 0, 50);
            if (mb_strlen($question) > 50) {
                $title .= '...';
            }

            $conversation = AiConversation::create([
                'uuid'    => $uuid,
                'user_id' => auth()->id(),
                'title'   => $title,
            ]);

            if (! auth()->check()) {
                $guestConversations = session()->get('ai_conversations', []);
                $guestConversations[] = $uuid;
                session()->put('ai_conversations', $guestConversations);
            }
        }

        // 2. جلب تاريخ الرسائل السابقة
        $history = $conversation->messages()->orderBy('created_at', 'asc')->get();

        // 3. صياغة استعلام البحث دلالياً (Query Rewriting) لربط الأسئلة المتابعة
        $searchQuery = $question;
        if ($history->isNotEmpty()) {
            $searchQuery = $this->rewriteQuery($question, $history);
        }

        // 4. البحث المتوازي الذكي (Cases & Law Articles)
        $searchMethod = 'keyword';
        $contextTasks = collect();
        $allArticles  = collect();
        $exactArticles = collect();

        // أ. استخراج أي مواد مذكورة في السؤال نفسه مباشرة
        $queryArticles = $this->referenceService->getMentionedArticles($searchQuery);
        foreach ($queryArticles as $art) {
            $allArticles->push($art);
            
            // تحويل المادة القانونية المطابقة تماماً لكائن يحاكي نتيجة البحث ليتم عرضه كمستند أول ذو أهمية قصوى
            $exactArticles->push((object) [
                'id'              => 'article_' . $art->id,
                'question'        => $art->article_title ?? '',
                'correct_answer'  => $art->content ?? '',
                'case_text'       => $art->content ?? '',
                'source_type'     => 'article',
                'case_reference'  => 'مادة رقم ' . ($art->article_number ?? ''),
                'law_system_name' => $art->legislation_title ?? '',
                'relevance_score' => 10.0,
                'reranker_score'  => 10.0,
            ]);
        }

        // ب. استخراج أرقام القضايا أو المراجع المحددة في السؤال مباشرة وتضمينها
        $extractedNumbers = [];
        if (preg_match_all('/\b\d{3,}\b/u', $searchQuery, $numMatches)) {
            $extractedNumbers = array_unique($numMatches[0]);
        }

        $exactMatches = collect();
        if (!empty($extractedNumbers)) {
            $exactMatches = \App\Models\LegalTask::where(function($q) use ($extractedNumbers) {
                foreach ($extractedNumbers as $num) {
                    $q->orWhere('case_reference', $num)
                      ->orWhere('id', (int)$num);
                    if (strlen($num) >= 6) {
                        $q->orWhere('case_reference', 'LIKE', "%{$num}%");
                    }
                }
            })->get();
            
            foreach ($exactMatches as $match) {
                $match->source_type = $match->source_type ?? 'judgment';
            }
        }

        // تحديد ما إذا كان السؤال طلباً مباشراً لنص مادة قانونية محددة لتجنب جلب قضايا وأحكام غير ذات صلة
        $isDirectArticleRequest = false;
        if ($exactArticles->isNotEmpty()) {
            $isDirectArticleRequest = preg_match(
                '/(نص|ما\s*هي|ما\s*هو|ماذا\s*تقول|عرض)\s+المادة/u',
                $searchQuery
            ) || preg_match(
                '/^المادة\s+([أ-ي0-9]+)\s+(في|من)\s+(نظام|لائحة)/u',
                trim($searchQuery)
            );
        }

        // Tier 1: Azure AI Search (Split hybrid search)
        if ($this->azureService->isEnabled()) {
            Log::info('[LegalAi] Using Azure Split Search for: ' . $searchQuery);

            // أ. بحث عن أحكام قضائية وسوابق (تستثنى منها المواد) - يتم تخطيها إذا كان الطلب لنص مادة مباشرة
            $cases = $isDirectArticleRequest ? collect() : $this->azureService->hybridSearch($searchQuery, 5, ['!source_type' => 'article']);

            // ب. بحث عن نصوص أنظمة وقوانين مخصصة
            $lawArticles = $this->azureService->hybridSearch($searchQuery, 3, ['source_type' => 'article']);

            // ج. دمج وتفعيل الطريقة
            $contextTasks = $cases->merge($lawArticles);
            if ($contextTasks->isNotEmpty()) {
                $searchMethod = 'azure_vector';
            }
        }

        // Tier 2: Qdrant Vector Search (Split retrieval fallback)
        if ($contextTasks->isEmpty() && $this->qdrantService->isEnabled()) {
            Log::info('[LegalAi] Falling back to Qdrant Split Search for: ' . $searchQuery);

            $cases = $isDirectArticleRequest ? collect() : $this->qdrantService->search($searchQuery, 5, ['!source_type' => 'article']);
            $lawArticles = $this->qdrantService->search($searchQuery, 3, ['source_type' => 'article']);

            $contextTasks = $cases->merge($lawArticles);
            if ($contextTasks->isNotEmpty()) {
                $searchMethod = 'qdrant_vector';
            }
        }

        // Tier 3: Keyword Search (الـ fallback العادي)
        if ($contextTasks->isEmpty()) {
            Log::info('[LegalAi] Falling back to keyword search');
            $contextTasks = $this->searchService->search($searchQuery, 5);
            $searchMethod = 'keyword';
        }

        // دمج المواد المطابقة تماماً المستخرجة من الاستعلام في بداية السياق
        if ($exactArticles->isNotEmpty()) {
            $contextTasks = $exactArticles->merge($contextTasks);
            $searchMethod = ($searchMethod === 'keyword') ? 'hybrid_exact' : $searchMethod . '_hybrid';
        }

        // دمج النتائج المطابقة تماماً برقم القضية في بداية السياق لضمان قراءتها
        if ($exactMatches->isNotEmpty()) {
            $contextTasks = $exactMatches->toBase()->merge($contextTasks);
            // تحديد طريقة البحث كبحث هجين مخصص بالرقم
            $searchMethod = ($searchMethod === 'keyword') ? 'hybrid_exact' : $searchMethod . '_hybrid';
        }

        // إزالة التكرار في النتائج (سواء أحكام أو مواد متطابقة بالمعرف أو النص)
        $contextTasks = $contextTasks->unique(function ($task) {
            if (isset($task->source_type) && $task->source_type === 'article') {
                return 'article_' . ($task->id ?? uniqid());
            }
            $text = trim($task->case_text ?: $task->correct_answer ?: '');
            if ($text === '') {
                return 'empty_' . ($task->id ?? uniqid());
            }
            return md5($text);
        });

        // تفكيك المجموعة لـ: مواد مطابقة بدقة، أحكام قضائية، ومواد عامة أخرى
        $exactArticlesIds = $exactArticles->map(fn($a) => $a->id)->toArray();

        $exactArticlesPart = $contextTasks->filter(fn($t) => (isset($t->source_type) && $t->source_type === 'article' && in_array($t->id, $exactArticlesIds)));
        $casesPart = $contextTasks->filter(fn($t) => (!isset($t->source_type) || $t->source_type !== 'article'));
        $articlesPart = $contextTasks->filter(fn($t) => (isset($t->source_type) && $t->source_type === 'article' && !in_array($t->id, $exactArticlesIds)));

        // فرز الأحكام: الاستئناف أولاً، ثم الأحدث (عن طريق المعرف ID تنازلياً)
        $casesPart = $casesPart->sort(function ($a, $b) {
            $aText = ($a->case_text ?? '') . ' ' . ($a->case_reference ?? '') . ' ' . ($a->question ?? '');
            $bText = ($b->case_text ?? '') . ' ' . ($b->case_reference ?? '') . ' ' . ($b->question ?? '');
            
            $aHasAppeal = (mb_strpos($aText, 'استئناف') !== false) ? 1 : 0;
            $bHasAppeal = (mb_strpos($bText, 'استئناف') !== false) ? 1 : 0;

            if ($aHasAppeal !== $bHasAppeal) {
                return $bHasAppeal <=> $aHasAppeal;
            }

            $aId = $a->id ?? 0;
            $bId = $b->id ?? 0;
            return $bId <=> $aId;
        });

        // إعادة الدمج بالترتيب المنطقي: المواد المطابقة بدقة أولاً، ثم الأحكام القضائية، ثم المواد العامة الأخرى
        $contextTasks = $exactArticlesPart->merge($casesPart)->merge($articlesPart);

        // استخراج المواد المشارة إليها من الأحكام لتوسيع السياق
        if ($contextTasks->isNotEmpty()) {
            foreach ($contextTasks as $task) {
                if (! isset($task->source_type) || $task->source_type != 'article') {
                    $textToScan = ($task->case_text ?: '') . ' ' . ($task->correct_answer ?: '');
                    $articles = $this->referenceService->getMentionedArticles($textToScan);
                    foreach ($articles as $art) {
                        $allArticles->push($art);
                    }
                }
            }
        }

        $contextText = "";
        $citations = [];

        // بناء المراجع للأحكام والمهام والأنظمة المدمجة
        foreach ($contextTasks as $task) {
            $typeLabel  = "مرجع قانوني";
            $badgeLabel = "أحكام قضائية";

            if (isset($task->source_type)) {
                if ($task->source_type == 'judgment') { $typeLabel = "حكم قضائي"; $badgeLabel = "أحكام قضائية"; }
                elseif ($task->source_type == 'consultation') { $typeLabel = "استشارة سابقة"; $badgeLabel = "استشارة سابقة"; }
                elseif ($task->source_type == 'article') { $typeLabel = "مادة نظامية"; $badgeLabel = "نص نظام"; }
            }

            if (isset($task->source_type) && $task->source_type == 'article') {
                $systemName = $task->law_system_name ?? '';
                $ref = $task->question;
                if (!empty($systemName)) {
                    $ref .= " - " . $systemName;
                }
            } else {
                $ref = $task->case_reference ?? (isset($task->id) ? "مرجع #{$task->id}" : "مرجع عام");
                if (trim($ref) == "مادة رقم" || trim($ref) == "null" || empty(trim($ref))) {
                    $ref = $task->question ?? "مرجع عام";
                }
            }

            $textToShow = $task->case_text ?: $task->correct_answer;
            if (! $textToShow || trim($textToShow) == 'null' || trim($textToShow) == '') {
                continue;
            }

            $contextText .= "--- {$typeLabel} [{$ref}] ---\n";
            $contextText .= "السؤال/الموضوع: {$task->question}\n";
            $contextText .= "النص/الأسباب: {$textToShow}\n";
            if ($task->correct_answer && $task->correct_answer != $textToShow) {
                $contextText .= "الإجابة/المنطوق: {$task->correct_answer}\n";
            }
            $contextText .= "\n";

            $citations[] = [
                'type'    => $task->source_type ?? 'judgment',
                'title'   => $ref,
                'article' => $badgeLabel,
                'text'    => $textToShow,
                'system'  => (isset($task->source_type) && $task->source_type == 'article') ? ($task->law_system_name ?? '') : '',
            ];
        }

        // إضافة الأنظمة والمواد المترابطة المستخرجة من أسباب الأحكام (فقط التي لم تُضف مسبقاً في السياق لتجنب التكرار)
        $addedArticleIds = $contextTasks->filter(fn($t) => (isset($t->source_type) && $t->source_type === 'article'))
            ->map(function($t) {
                if (is_string($t->id) && str_starts_with($t->id, 'article_')) {
                    return (int) substr($t->id, 8);
                }
                return (int) $t->id;
            })->toArray();

        if ($allArticles->isNotEmpty()) {
            $hasHeader = false;
            foreach ($allArticles->unique('id') as $article) {
                if (in_array($article->id, $addedArticleIds)) {
                    continue; // مضافة مسبقاً في مصفوفة النتائج الرئيسية
                }
                if (!$hasHeader) {
                    $contextText .= "--- نصوص الأنظمة السعودية ذات الصلة ---\n";
                    $hasHeader = true;
                }
                $contextText .= "[{$article->legislation_title} - {$article->article_title}]:\n{$article->content}\n\n";

                $citations[] = [
                    'type'    => 'law_article',
                    'title'   => "{$article->article_title}",
                    'article' => "نص نظام",
                    'text'    => $article->content,
                    'system'  => $article->legislation_title ?? '',
                ];
            }
        }

        // جلب الإحصائيات إذا سأل المستخدم أسئلة إحصائية حول قاعدة البيانات
        $statsSummary = $this->referenceService->getSystemStatsSummary($searchQuery);
        if ($statsSummary) {
            $contextText .= "--- إحصائيات النظام وقاعدة البيانات ---\n{$statsSummary}\n\n";
            $citations[] = [
                'type'    => 'system_stats',
                'title'   => 'إحصائيات قاعدة البيانات',
                'article' => 'إحصائيات',
                'text'    => $statsSummary,
                'system'  => 'النظام الإحصائي',
            ];
        }

        // 5. بناء الـ Prompt وهيكلة تاريخ الرسائل بالكامل
        $prompt = "أنت المستشار القانوني الذكي لمنصة 'رديف'. وظيفتك هي صياغة استشارات قانونية دقيقة بناءً على نصوص القضايا والمواد النظامية السعودية المرفقة فقط.

قواعد العمل الصارمة:
- الالتزام بالمصادر: لا تخرج عن نطاق نصوص القضايا والمواد النظامية التي تم تزويدك بها. إذا كانت المعلومات غير كافية للإجابة، اذكر بوضوح: 'بناءً على السوابق القضائية المتوفرة، لم نجد ما يغطي هذه الجزئية بدقة، ولكن المبدأ العام المتبع هو...' (ولا تختلق إجابة).

هيكلية الإجابة:
- الرأي القانوني: ابدأ بإجابة مباشرة ومختصرة.
- الاستدلال: اذكر الأسانيد (أرقام القضايا، المواد النظامية).
- التحليل: اربط المبادئ المستخلصة من القضايا بالواقعة التي سأل عنها المستخدم.
- الدقة: استخدم أرقام القضايا والمواد كمرجع أساسي (مثال: 'كما جاء في الحكم رقم 1234 لعام 1442هـ').

نبرة الصوت: احترافية، قانونية، واثقة، وموجزة.

تجنب الهلوسة: إذا تعارضت نتائج البحث، اذكر أن هناك اختلافاً في الاجتهاد القضائي بناءً على المصادر المرفقة.

السياق المتاح حالياً:
" . $contextText . "

سؤال العميل الحالي:
" . $question;

        // هيكلة تاريخ المحادثة لـ Gemini
        $contents = [];
        foreach ($history as $msg) {
            $contents[] = [
                'role'  => $msg->role,
                'parts' => [['text' => $msg->message]],
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $prompt]],
        ];

        // 6. استدعاء النموذج وصياغة الإجابة (دعم التبديل التلقائي بين Azure OpenAI و Gemini)
        $azureKey = trim(env('AZURE_OPENAI_KEY'));
        if (!empty($azureKey)) {
            $messages = [];
            foreach ($history as $msg) {
                $messages[] = [
                    'role'    => $msg->role === 'model' ? 'assistant' : $msg->role,
                    'content' => $msg->message,
                ];
            }
            $messages[] = [
                'role'    => 'user',
                'content' => $prompt,
            ];
            $answer = $this->callAzureOpenAiApi($messages);
            $searchMethod .= '_azure';
        } else {
            $answer = $this->callGeminiApi($contents);
            $searchMethod .= '_gemini';
        }

        // 7. حساب مؤشر الثقة وحفظ الرسائل الجديدة في قاعدة البيانات وتحديث وقت المحادثة
        $confidenceScore = $this->calculateConfidence($searchMethod, $contextTasks, $allArticles);
        $citationsPayload = [
            'confidence_score' => $confidenceScore,
            'items'            => $citations,
        ];

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'user',
            'message'            => $question,
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'model',
            'message'            => $answer,
            'citations'          => $citationsPayload,
        ]);

        $conversation->touch();

        // حساب الاستخدام المحدث لإرساله للواجهة الأمامية
        $messageCount = 0;
        $limit = 10;
        $this->checkMessageLimit($request, $messageCount, $limit);
        $referralLink = auth()->check() ? route('register.company', ['ref' => auth()->id()]) : null;

        return response()->json([
            'answer'            => $answer,
            'citations'         => $citationsPayload,
            'conversation_uuid' => $conversation->uuid,
            'search_method'     => $searchMethod,
            'usage'             => [
                'count'        => $messageCount,
                'limit'        => $limit,
                'remaining'    => max(0, $limit - $messageCount),
                'is_logged_in' => auth()->check(),
                'referral_link'=> $referralLink,
            ]
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * استدعاء Gemini API مع تمرير كامل المحادثة والـ Fallbacks
     */
    private function callGeminiApi(array $contents)
    {
        $apiKey = trim(config('services.gemini.key'));

        if (empty($apiKey)) {
            return "مرحباً! لقد قمت باستخراج السوابق القانونية لك. (يرجى تفعيل GEMINI_API_KEY في ملف .env للحصول على صياغة ذكية).";
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(80)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey, [
                    'contents' => $contents,
                ]);

            Log::info("Gemini Flash Response Status: " . $response->status() . " | Body: " . mb_substr($response->body(), 0, 500));

            if ($response->successful()) {
                return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? "عذراً، لم أتمكن من صياغة الإجابة.";
            }

            // Fallback to Gemini 3.5 Flash if 2.5 fails
            if ($response->status() == 404 || $response->status() == 403) {
                $response = Http::withoutVerifying()
                    ->timeout(80)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey, [
                        'contents' => $contents,
                    ]);

                if ($response->successful()) {
                    return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? "عذراً، لم أتمكن من صياغة الإجابة.";
                }
            }

            $errorBody = $response->body();
            Log::error("Gemini API Error: " . $response->status() . " - " . $errorBody);
            return "عذراً، حدث خطأ فني (الرمز: " . $response->status() . "). يرجى المحاولة لاحقاً.";

        } catch (\Exception $e) {
            return "خطأ في الاتصال بالمحرك: " . $e->getMessage();
        }
    }

    /**
     * إعادة صياغة السؤال دلالياً لربط الأسئلة المتابعة بالأسئلة السابقة
     */
    private function rewriteQuery(string $question, $history): string
    {
        $azureKey = trim(env('AZURE_OPENAI_KEY'));
        $geminiKey = trim(config('services.gemini.key'));

        if (empty($azureKey) && empty($geminiKey)) {
            return $question;
        }

        // بناء تاريخ مصغر (آخر 6 رسائل فقط للحفاظ على الـ limits)
        $conversationText = "";
        foreach ($history->take(-6) as $msg) {
            $roleLabel = $msg->role === 'user' ? 'العميل' : 'المساعد الموثق';
            $conversationText .= "{$roleLabel}: {$msg->message}\n";
        }

        $prompt = "بناءً على تاريخ المحادثة التالي بين العميل والمساعد القانوني:\n"
            . $conversationText
            . "\nوالسؤال الجديد للعميل: '{$question}'\n\n"
            . "أعد صياغة السؤال الجديد ليكون عبارة بحث قانونية مستقلة ومفهومة باللغة العربية للبحث في الأنظمة السعودية والأحكام القضائية. أرجع فقط عبارة البحث المحدثة دون أي شرح أو نصوص إضافية إطلاقاً.";

        if (!empty($azureKey)) {
            try {
                $endpoint   = trim(env('AZURE_OPENAI_ENDPOINT'));
                $deployment = trim(env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'));
                $apiVersion = trim(env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'));
                $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'api-key'      => $azureKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(15)
                    ->post($url, [
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.0,
                    ]);

                if ($response->successful()) {
                    $rewritten = trim($response->json('choices.0.message.content') ?? '');
                    if (! empty($rewritten)) {
                        Log::info('[LegalAi] Query rewritten via Azure: ' . $rewritten);
                        return $rewritten;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[LegalAi] Query rewriting via Azure failed: ' . $e->getMessage());
            }
        } else {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(15)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $geminiKey, [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                    ]);

                if ($response->successful()) {
                    $rewritten = trim($response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '');
                    if (! empty($rewritten)) {
                        Log::info('[LegalAi] Query rewritten: ' . $rewritten);
                        return $rewritten;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[LegalAi] Query rewriting failed: ' . $e->getMessage());
            }
        }

        return $question;
    }

    /**
     * استدعاء Azure OpenAI API مع تمرير كامل المحادثة والـ Fallbacks
     */
    private function callAzureOpenAiApi(array $messages)
    {
        $endpoint   = trim(env('AZURE_OPENAI_ENDPOINT'));
        $key        = trim(env('AZURE_OPENAI_KEY'));
        $deployment = trim(env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'));
        $apiVersion = trim(env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'));

        if (empty($endpoint) || empty($key)) {
            return "مرحباً! (بيانات Azure OpenAI غير مكتملة في ملف .env).";
        }

        $endpoint = rtrim($endpoint, '/');

        try {
            $url = "{$endpoint}/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'api-key'      => $key,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(80)
                ->post($url, [
                    'messages'    => $messages,
                    'temperature' => 0.3,
                ]);

            Log::info("Azure OpenAI Response Status: " . $response->status() . " | Body: " . mb_substr($response->body(), 0, 500));

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? "عذراً، لم أتمكن من صياغة الإجابة.";
            }

            $errorBody = $response->body();
            Log::error("Azure OpenAI API Error: " . $response->status() . " - " . $errorBody);
            return "عذراً، حدث خطأ فني أثناء الاتصال بـ Azure (الرمز: " . $response->status() . ").";

        } catch (\Exception $e) {
            return "خطأ في الاتصال بمحرك Azure: " . $e->getMessage();
        }
    }

    /**
     * حساب مؤشر الثقة ديناميكياً للرد
     */
    private function calculateConfidence(string $searchMethod, $contextTasks, $allArticles): int
    {
        if ($contextTasks->isEmpty() && $allArticles->isEmpty()) {
            return 50; // ثقة منخفضة إذا لم يتم العثور على مراجع
        }

        $base = 70;
        if (str_contains($searchMethod, 'azure') || str_contains($searchMethod, 'qdrant')) {
            $base = 85;
        }

        if (str_contains($searchMethod, 'hybrid') || str_contains($searchMethod, 'vector')) {
            $base = 90;
        }

        // زيادة الثقة عند مطابقة مصادر متنوعة (أنظمة + أحكام)
        $hasArticles = $contextTasks->contains(fn($t) => (isset($t->source_type) && $t->source_type === 'article')) || $allArticles->isNotEmpty();
        $hasJudgments = $contextTasks->contains(fn($t) => (!isset($t->source_type) || $t->source_type !== 'article'));

        if ($hasArticles && $hasJudgments) {
            $base += 8; // أفضل حالة: أنظمة وقضايا معاً
        } elseif ($hasArticles) {
            $base += 5; // أنظمة فقط
        } else {
            $base += 3; // قضايا فقط
        }

        // تفاوت عشوائي طفيف (-2 إلى +2) لمظهر تفاعلي واقعي
        $variance = rand(-2, 2);
        
        return min(99, max(50, $base + $variance));
    }

    /**
     * التحقق من حد الرسائل المسموح بها للزائر / العضو / الإحالة
     */
    private function checkMessageLimit(Request $request, &$messageCount, &$limit)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // حساب الرسائل المرسلة بواسطة العضو المسجل
            $messageCount = AiMessage::where('role', 'user')
                ->whereHas('conversation', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->count();
                
            // التحقق مما إذا كان قد قام بدعوة صديق واحد على الأقل
            $hasReferrals = \App\Models\User::where('referred_by', $user->id)->exists();
            
            // 20 رسالة أساسية للأعضاء، وإذا دعا صديقاً تصبح 40 رسالة (20 إضافية)
            $limit = $hasReferrals ? 40 : 20;
        } else {
            // حساب الرسائل المرسلة كزائر بناءً على الجلسة
            $sessionUuids = session()->get('ai_conversations', []);
            
            $guestUuids = [];
            if ($request->has('guest_uuids')) {
                $guestUuids = explode(',', $request->input('guest_uuids'));
                $guestUuids = array_filter($guestUuids, fn($u) => !empty($u) && Str::isUuid($u));
            }
            
            $allUuids = array_unique(array_merge($sessionUuids, $guestUuids));
            if ($request->has('conversation_uuid') && !in_array($request->conversation_uuid, $allUuids)) {
                $allUuids[] = $request->conversation_uuid;
            }
            
            $messageCount = AiMessage::where('role', 'user')
                ->whereHas('conversation', function ($q) use ($allUuids) {
                    $q->whereIn('uuid', $allUuids);
                })
                ->count();
                
            // 10 رسائل للزوار
            $limit = 10;
        }
    }
}
