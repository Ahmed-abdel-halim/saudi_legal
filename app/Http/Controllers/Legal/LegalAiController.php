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
    protected $geminiService;
    protected $bedrockService;

    public function __construct(
        LegalSearchService    $searchService,
        AzureSearchService    $azureService,
        QdrantSearchService   $qdrantService,
        LegalReferenceService $referenceService,
        \App\Services\GeminiApiService $geminiService,
        \App\Services\BedrockApiService $bedrockService
    ) {
        $this->searchService   = $searchService;
        $this->azureService    = $azureService;
        $this->qdrantService   = $qdrantService;
        $this->referenceService = $referenceService;
        $this->geminiService   = $geminiService;
        $this->bedrockService  = $bedrockService;
    }

    public function index()
    {
        return view('saudi_legal.chat');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API ENDPOINTS FOR CHAT HISTORY & FEEDBACK
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * استقبال وحفظ تقييم المستخدم لإجابة المساعد الذكي (like/dislike + سبب عدم الإفادة)
     */
    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'rating'            => 'required|in:like,dislike',
            'reason'            => 'nullable|string|max:2000',
            'user_query'        => 'nullable|string|max:4000',
            'ai_response'       => 'nullable|string|max:10000',
            'conversation_uuid' => 'nullable|string',
            'ai_message_id'     => 'nullable|integer',
        ]);

        $conversationId = null;
        if (!empty($validated['conversation_uuid'])) {
            $conv = AiConversation::where('uuid', $validated['conversation_uuid'])->first();
            if ($conv) {
                $conversationId = $conv->id;
            }
        }

        $feedback = \App\Models\AiMessageFeedback::create([
            'user_id'            => auth()->id(),
            'ai_conversation_id' => $conversationId,
            'ai_message_id'     => $validated['ai_message_id'] ?? null,
            'rating'             => $validated['rating'],
            'reason'             => $validated['reason'] ?? null,
            'user_query'         => $validated['user_query'] ?? null,
            'ai_response'        => $validated['ai_response'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['rating'] === 'like' 
                ? 'شكراً لتقييمك الإيجابي!' 
                : 'شكراً لملاحظتك، تم حفظها وسنقوم بمراجعتها لتطوير المساعد الذكي.',
            'feedback_id' => $feedback->id,
        ]);
    }

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

        $messages = $conversation->messages;
        $firstUserMessage = $messages->firstWhere('role', 'user')?->message ?? '';
        $isEnglishConv = preg_match('/[a-zA-Z]/', $firstUserMessage);

        return response()->json([
            'conversation' => [
                'uuid'  => $conversation->uuid,
                'title' => $conversation->title,
            ],
            'messages' => $messages->map(function($m) use ($isEnglishConv) {
                $citations = $m->citations;
                if ($isEnglishConv && !empty($citations) && is_array($citations)) {
                    $items = $citations['items'] ?? null;
                    if (is_array($items) && !empty($items)) {
                        $firstText = $items[0]['text'] ?? '';
                        if (!preg_match('/[a-zA-Z]{4,}/', $firstText)) {
                            $translatedItems = $this->translateCitationsIfNeeded($items, 'English question');
                            $citations['items'] = $translatedItems;
                            $m->update(['citations' => $citations]);
                        }
                    }
                }
                return [
                    'role'      => $m->role,
                    'message'   => $m->message,
                    'citations' => $citations,
                ];
            }),
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
            'question'          => 'required|string|max:10000',
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

        // 2.5. التحقق مما إذا كانت الرسالة تحية مجردة أو مجاملة بدون سؤال قانوني
        if ($this->isPureGreeting($question)) {
            $answer = $this->getGreetingReply($question);
            $citationsPayload = [
                'confidence_score' => 0,
                'items'            => [],
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

            $messageCount = 0;
            $limit = 10;
            $this->checkMessageLimit($request, $messageCount, $limit);
            $referralLink = auth()->check() ? route('register.company', ['ref' => auth()->id()]) : null;

            return response()->json([
                'answer'            => $answer,
                'citations'         => $citationsPayload,
                'conversation_uuid' => $conversation->uuid,
                'search_method'     => 'greeting',
                'usage'             => [
                    'count'        => $messageCount,
                    'limit'        => $limit,
                    'remaining'    => max(0, $limit - $messageCount),
                    'is_logged_in' => auth()->check(),
                    'referral_link'=> $referralLink,
                ]
            ]);
        }

        // 3. صياغة استعلام البحث دلالياً (Query Rewriting) لربط الأسئلة المتابعة والأسئلة غير العربية
        $searchQuery = $question;
        if ($history->isNotEmpty() || preg_match('/[a-zA-Z]/', $question)) {
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
        $isDirectArticleRequest = preg_match(
            '/(نص|ما\s*هي|ما\s*هو|ماذا\s*تقول|ماذا\s*تنص|عرض|اعرض|اذكر|أريد|اريد)\s+(نص\s+)?المادة/u',
            $searchQuery
        ) || preg_match(
            '/^المادة\s+([أ-ي0-9]+)/u',
            trim($searchQuery)
        ) || preg_match(
            '/نص\s+المادة/u',
            $searchQuery
        );

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
        if ($isDirectArticleRequest) {
            if ($exactArticlesPart->isNotEmpty()) {
                // عند طلب نص مادة مباشرة، نكتفي بالمادة المطابقة بدقة فقط ونستبعد أي مواد فرعية أو متشابهة في أنظمة أخرى
                $contextTasks = $exactArticlesPart;
            } else {
                // إذا لم توجد مطابقة دقيقة من الاستعلام، نأخذ المادة الأكثر صلة فقط (مادة واحدة)
                $contextTasks = $articlesPart->take(1);
                if ($contextTasks->isEmpty()) {
                    $contextTasks = $casesPart->take(1);
                }
            }
        } else {
            $contextTasks = $exactArticlesPart->merge($casesPart)->merge($articlesPart);
        }

        // استخراج المواد المشارة إليها من الأحكام لتوسيع السياق
        if ($contextTasks->isNotEmpty() && ! $isDirectArticleRequest) {
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
                'type'           => $task->source_type ?? 'judgment',
                'title'          => $ref,
                'article'        => $badgeLabel,
                'text'           => $textToShow,
                'system'         => $task->law_system_name ?? '',
                'article_number' => $task->law_article_number ?? '',
                'case_reference' => $task->case_reference ?? '',
            ];
        }

        // إضافة الأنظمة والمواد المترابطة المستخرجة من أسباب الأحكام (فقط في الأسئلة الاستشارية العامة وعند عدم وجود تكرار)
        $addedArticleIds = $contextTasks->filter(fn($t) => (isset($t->source_type) && $t->source_type === 'article'))
            ->map(function($t) {
                if (is_string($t->id) && str_starts_with($t->id, 'article_')) {
                    return (int) substr($t->id, 8);
                }
                return (int) $t->id;
            })->toArray();

        if (! $isDirectArticleRequest && $allArticles->isNotEmpty()) {
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
            }
        }

        // تنقية مصفوفة المراجع (Citations) لمنع أي تكرار
        $uniqueCitations = [];
        $seenCitationKeys = [];
        foreach ($citations as $cite) {
            $citeKey = ($cite['title'] ?? '') . '_' . ($cite['system'] ?? '') . '_' . md5(trim($cite['text'] ?? ''));
            if (!isset($seenCitationKeys[$citeKey])) {
                $seenCitationKeys[$citeKey] = true;
                $uniqueCitations[] = $cite;
            }
        }
        $citations = $uniqueCitations;

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
        $articleOnlyRule = "";
        if ($isDirectArticleRequest) {
            $articleOnlyRule = "⚠️ طلب نص مادة مباشرة (تنبيه حاسم): سؤال العميل يطلب نص مادة نظامية محددة. يُمنع منعاً باتاً تقسيم الإجابة ويُمنع إضافة تحليلات أو أسباب زوائد. اعرض اسم النظام ورقم المادة متبوعاً بنص المادة المباشر والدقيق فقط كما هو وارد في السياق.\n";
        }

        $prompt = "أنت المستشار القانوني الذكي الأول لمنصة 'رديف' والمتخصص الحصري في الأنظمة واللوائح والسوابق القضائية في المملكة العربية السعودية.

🌍 تعليمات تعدد اللغات واللغة المستهدفة للإجابة (صارمة بنسبة 100%):
- مهما كانت لغة سؤال المستخدم (سواء كانت العربية، الإنجليزية، الفرنسية، الألمانية، الأوردو، الفارسية، أو أي لغة أخرى)، قم بالآتي:
  1. ابحث واسترجع السوابق والمواد النظامية الدقيقة من قاعدة المعرفة المرفقة بالسياق أدناه.
  2. صُغ الإجابة النهائية مباشرة بنفس لغة سؤال المستخدم تماماً.
  3. يجب الاحتفاظ بأسماء الأنظمة السعودية الرسمية وأرقام المواد والمراجع القضائية بدقة مطلقة.

🔒 القواعد الأصولية والمحددات القانونية الصارمة (System Guardrails):
1. **أولوية المواد والأنظمة (Statute-First):**
   - نصوص الأنظمة واللوائح التنفيذية الصريحة هي الأساس والتشريع الإلزامي الأول، وتُعطى الأولوية المطلقة في التأسيس.
   - السوابق والأحكام القضائية تُذكر كتطبيق عملي واستئناس لتأكيد فهم المادة، وفي حال عدم وجود سابقة قضائية مماثلة، أجب مباشرة وبثقة تامة بناءً على نصوص المواد دون ارتباك ولا تذكر 'لا توجد قضايا'.

2. **منع التلوث المعرفي والعزل السياقي (Noise Reduction & Domain Isolation):**
   - الالتزام الحصري بالنظام ولائحته التنفيذية ذات الصلة المباشرة بموضوع السؤال؛ فعند معالجة الدعاوى والاعتراضات التجارية يتم الاعتماد حصراً على (نظام المحاكم التجارية ولائحته التنفيذية، نظام الشركات، نظام المعاملات المدنية)، وفي قضايا العمل على (نظام العمل ولائحته)، وفي الأحوال الشخصية على (نظام الأحوال الشخصية).
   - يُمنع منعاً باتاً استدعاء أو إقحام نصوص من أنظمة غير مطبقة (كالأنظمة الجزائية أو الإدارية أو الأوراق التجارية أو التخصيص) لمجرد تشابه كلمات مفتاحية عامة مثل 'اعتراض'، 'إخطار'، 'تبليغ'، 'مهلة'، 'شطب'. اعزل كل نزاع داخل نظامه الموضوعي والإجرائي المختص.

3. **قاعدة حجية الأمر المقضي به (Res Judicata - حاسمة وإلزامية):**
   - إذا تبين من استعلام المستفيد أو الوقائع وجود حكم قضائي نهائي ومؤيد من محكمة الاستئناف (أو مكتسب القطعية)، يُمنع منعاً باتاً اقتراح رفع أي دعوى موضوعية جديدة حول نفس الحق والسبب والخصوم، ويجب إرشاد المستفيد حصراً إلى المسارين التاليين:
     أ) مسار التنفيذ الجبري لدى محكمة التنفيذ وفقاً لنظام التنفيذ.
     ب) مسار تقديم (التماس إعادة النظر) في حالاته الحصرية والضيقة المنصوص عليها نظاماً (مثل: ظهور أوراق قاطعة تعذر إبرازها، ثبوت غش أو تزوير).

4. **الالتزام بالسياق ومنع الهلوسة والتفكير الداخلي:**
   - لا تذكر مواداً أو أرقام أنظمة أو سوابق وهمية من خارج السياق المرفق أدناه.
   - يُمنع منعاً باتاً إخراج أي تفكير داخلي أو نصوص تبدأ بـ 'THOUGHT:' أو 'Reasoning:' أو استخدام وسم '<thought>'.
   - تحديد نطاق العمل: إذا كان سؤال المستخدم عاماً أو دردشة أو خارج النطاق القانوني السعودي تماماً، أجب باختصار وبلطف وبفقرة واحدة بنفس لغته بأنك متخصص فقط في الأنظمة والقضايا السعودية.

{$articleOnlyRule}
🏛️ الهيكلية البصرية الإلزامية للمخرجات (Structured Markdown):
صغ الإجابة وفق القالب المهيكل التالي دائماً (في الاستشارات العامة):

### 📌 الرأي والمشورة القانونية
[إجابة حاسمة ومباشرة وواضحة لسؤال المستفيد]

### ⚖️ التحليل والتطبيق النظامي
[التحليل الموضوعي والإجرائي، وربط الوقائع بالنصوص النظامية، وتطبيق المبادئ القضائية إن وجدت]

---
### 📚 المراجع والنصوص النظامية المستشهد بها:
- **[اسم النظام، مثال: نظام المحاكم التجارية]:** المادة ([رقم المادة]) - [ملخص مقتضب لحكم المادة].
- **[اللائحة التنفيذية]:** المادة ([رقم المادة]).
- **[السوابق القضائية المعتمدة (إن وجدت)]:** حكم محكمة الاستئناف رقم ([الرقم]) لعام ([السنة])هـ.

السياق المتاح حالياً (من قاعدة المعرفة):
" . $contextText . "

سؤال العميل الحالي (أجب فوراً بنفس لغته):
" . $question;

        // هيكلة تاريخ المحادثة
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

        // 6. استدعاء النموذج وصياغة الإجابة (دعم Bedrock كخيار أول لتوفير التكاليف مع التبديل التلقائي لـ Azure OpenAI و Gemini)
        $answer = null;

        if ($this->bedrockService->isEnabled()) {
            $bedrockMessages = [];
            foreach ($history as $msg) {
                $bedrockMessages[] = [
                    'role'    => $msg->role === 'model' ? 'assistant' : $msg->role,
                    'content' => $msg->message,
                ];
            }
            $bedrockMessages[] = [
                'role'    => 'user',
                'content' => $prompt,
            ];
            $answer = $this->bedrockService->generateContent($bedrockMessages, null, ['temperature' => 0.1]);
            if (!empty($answer)) {
                $searchMethod .= '_bedrock';
            }
        }

        if (empty($answer)) {
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
        }

        if (empty($answer)) {
            if (!empty($citations)) {
                $topCitation = $citations[0];
                $title = $topCitation['title'] ?? 'مرجع قضائي';
                $textSnippet = mb_substr(trim($topCitation['text']), 0, 500);
                if (mb_strlen(trim($topCitation['text'])) > 500) {
                    $textSnippet .= '...';
                }

                $answer = "⚖️ **نتيجة البحث في الأنظمة والأحكام القضائية المتاحة:**\n\n" .
                          "بناءً على السجلات المتاحة في قاعدة البيانات القضائية لرديف حول موضوع استفسارك:\n\n" .
                          "📌 **المرجع:** {$title}\n" .
                          "📜 **النص المقتبس:** {$textSnippet}";
            } else {
                $answer = "عذراً، المحرك الذكي يواجه ضغطاً مؤقتاً ولم نتمكن من استخراج صياغة تفصيلية حالياً. يرجى كتابة السؤال بصيغة أخرى أو المحاولة لاحقاً.";
            }
        } else {
            // 6.2. تنقية الرد من أي نصوص تفكير داخلية (Chain of Thought / THOUGHT: / <thought>)
            $answer = $this->cleanModelResponse($answer);
        }

        // 6.5. فحص الإجابة واستخراج المواد التي تمت الإشارة إليها فعلياً وإضافتها للـ citations
        $answerArticles = $this->referenceService->getMentionedArticles($answer);
        if ($answerArticles->isNotEmpty()) {
            foreach ($answerArticles->unique('id') as $article) {
                // تجنب التكرار
                $exists = false;
                foreach ($citations as $cit) {
                    if (($cit['type'] === 'law_article' || $cit['type'] === 'article') && 
                        trim($cit['title']) == trim($article->article_title) && 
                        trim($cit['system']) == trim($article->legislation_title)) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $citations[] = [
                        'type'           => 'law_article',
                        'title'          => "{$article->article_title}",
                        'article'        => "نص نظام",
                        'text'           => $article->content,
                        'system'         => $article->legislation_title ?? '',
                        'article_number' => $article->article_title ?? '',
                        'case_reference' => '',
                    ];
                }
            }
        }

        // 7. حساب مؤشر الثقة وحفظ الرسائل الجديدة في قاعدة البيانات وتحديث وقت المحادثة
        $confidenceScore = $this->calculateConfidence($searchMethod, $contextTasks, $allArticles);

        // التحقق مما إذا كانت الإجابة خارج النطاق (أسئلة عامة غير قانونية)
        $isOutOfScope = (
            str_contains($answer, 'متخصص فقط في الأنظمة') ||
            str_contains($answer, 'متخصص حصراً في الأنظمة') ||
            str_contains(strtolower($answer), 'only specialized in saudi') ||
            (
                !str_contains($answer, 'الرأي القانوني') &&
                !str_contains($answer, 'الاستدلال') &&
                !str_contains(strtolower($answer), 'legal opinion') &&
                !str_contains(strtolower($answer), 'justification') &&
                !str_contains(strtolower($answer), 'analysis') &&
                !$isDirectArticleRequest &&
                $contextTasks->isEmpty()
            )
        );

        if ($isOutOfScope) {
            $citations = [];
            $confidenceScore = 0;
        }

        // ترجمة المصادر والمواد تلقائياً بلغة العميل إذا كان السؤال غير عربي
        $citations = $this->translateCitationsIfNeeded($citations, $question);

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
        $answer = $this->geminiService->generateContent($contents, ['timeout' => 15]);
        if (empty($answer)) {
            return null;
        }
        return $this->cleanModelResponse($answer);
    }

    /**
     * استخراج نص الإجابة النهائي من استجابة Gemini مع استبعاد أجزاء التفكير (thought parts)
     */
    private function extractGeminiResponseText(array $responseJson): string
    {
        $parts = $responseJson['candidates'][0]['content']['parts'] ?? [];
        $textParts = [];

        foreach ($parts as $part) {
            // استبعاد أجزاء التفكير المحددة بعلامة thought
            if (!empty($part['thought'])) {
                continue;
            }
            if (isset($part['text'])) {
                $textParts[] = $part['text'];
            }
        }

        $text = trim(implode("", $textParts));

        // Fallback إذا لم توجد أجزاء نصية بدون thought
        if (empty($text) && isset($parts[0]['text'])) {
            $text = trim($parts[0]['text']);
        }

        return $this->cleanModelResponse($text);
    }

    /**
     * تنقية استجابة النموذج من أفكار التفكير الداخلي (THOUGHT: / CoT / <thought> tags)
     */
    private function cleanModelResponse(string $answer): string
    {
        if (empty($answer)) {
            return $answer;
        }

        // 1. إزالة وسوم التفكير <thought>...</thought>
        $answer = preg_replace('/<thought>[\s\S]*?<\/thought>/iu', '', $answer);

        // 2. إزالة أي نص تفكير يبدأ بـ THOUGHT: أو Reasoning:
        if (preg_match('/^(THOUGHT|Reasoning):/i', trim($answer))) {
            $cleaned = preg_replace('/^(THOUGHT|Reasoning):[\s\S]*?(?=(\r?\n\r?\n[^\s]|\r?\n[^\s]|\bالرأي القانوني\b|\bLegal Opinion\b|$))/iu', '', $answer);
            $cleaned = trim($cleaned);

            if (!empty($cleaned) && !preg_match('/^(THOUGHT|Reasoning):/i', $cleaned)) {
                $answer = $cleaned;
            } else {
                $lines = explode("\n", $answer);
                $validLines = array_filter($lines, function($line) {
                    $trimmed = trim($line);
                    return !preg_match('/^(THOUGHT|Reasoning):/i', $trimmed);
                });
                $answer = trim(implode("\n", $validLines));
            }
        }

        return trim($answer);
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
            $rewritten = $this->geminiService->generateContent([['parts' => [['text' => $prompt]]]], ['timeout' => 8]);
            if (! empty($rewritten)) {
                Log::info('[LegalAi] Query rewritten: ' . $rewritten);
                return trim($rewritten);
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
     * التحقق من حد الرسائل المسموح بها للزائر / العضو / الإحالة / الاشتراك
     */
    private function checkMessageLimit(Request $request, &$messageCount, &$limit)
    {
        if (auth()->check()) {
            $user = auth()->user();

            // ── Check active paid subscription first ───────────────────────────
            $subscription = \App\Models\AiSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
                })
                ->with('package')
                ->latest()
                ->first();

            if ($subscription && $subscription->package) {
                // Subscription is active — count queries used in this billing period
                $periodStart = $subscription->starts_at ?? now()->startOfMonth();

                $messageCount = AiMessage::where('role', 'user')
                    ->whereHas('conversation', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->where('created_at', '>=', $periodStart)
                    ->count();

                if ($subscription->package->is_unlimited || $subscription->package->query_limit === -1) {
                    $limit = PHP_INT_MAX; // لامحدود
                } else {
                    $limit = $subscription->package->query_limit;
                }

                // Sync queries_used in subscription record
                $subscription->update(['queries_used' => $messageCount]);
                return;
            }

            // ── Free tier (no active subscription) ───────────────────────────
            $messageCount = AiMessage::where('role', 'user')
                ->whereHas('conversation', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->count();

            // التحقق من الإحالات
            $hasReferrals = \App\Models\User::where('referred_by', $user->id)->exists();

            // 20 رسالة أساسية، 40 مع إحالة + رصيد إضافي
            $limit = ($hasReferrals ? 40 : 20) + ($user->extra_messages_limit ?? 0);

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

    /**
     * ترجمة عناوين ونصوص المصادر والمواد تلقائياً للغة الإنجليزية إذا كان سؤال العميل بالإنجليزية
     */
    private function translateCitationsIfNeeded(array $citations, string $question): array
    {
        if (empty($citations)) {
            return $citations;
        }

        // التحقق مما إذا كان السؤال باللغة الإنجليزية/غير العربية
        if (!preg_match('/[a-zA-Z]/', $question)) {
            return $citations;
        }

        // التحقق مما إذا كانت الكروت مترجمة مسبقاً للإنجليزية
        $firstText = $citations[0]['text'] ?? ($citations['items'][0]['text'] ?? '');
        if (preg_match('/[a-zA-Z]{5,}/', $firstText)) {
            return $citations;
        }

        // بناء حمولة سريعة ومختصرة لتجنب انتهاء مهلة الطلب (cURL timeout)
        $lightweight = [];
        foreach ($citations as $idx => $cit) {
            $lightweight[] = [
                'idx'     => $idx,
                'title'   => $cit['title'] ?? '',
                'system'  => $cit['system'] ?? '',
                'article' => $cit['article'] ?? '',
                'text'    => mb_substr($cit['text'] ?? '', 0, 350),
            ];
        }

        $azureKey  = trim(env('AZURE_OPENAI_KEY'));
        $geminiKey = trim(config('services.gemini.key'));

        $prompt = "You are a professional legal translator specializing in Saudi Arabian Law.
Translate the following legal citation items (laws/court rulings) into clear, professional English.
Instructions:
1. For 'system': Translate law name to English and keep original Arabic official name in parentheses, e.g. 'System for Non-Saudi Real Estate Ownership (نظام تملك غير السعوديين للعقار واستثماره)'.
2. For 'title': Translate to English e.g. 'Article 1 (المادة الأولى)' or 'Case No: 4430630992'.
3. For 'article': Translate label e.g. 'Statutory Article' or 'Judicial Ruling'.
4. For 'text': Translate the text snippet into clear English.

Input JSON:
" . json_encode(['items' => $lightweight], JSON_UNESCAPED_UNICODE) . "

Output a JSON object with key 'items':
{\"items\": [{\"idx\": 0, \"title\": \"...\", \"system\": \"...\", \"article\": \"...\", \"text\": \"...\"}]}";

        $translatedJson = null;

        // التجربة عبر Azure OpenAI أولاً لسرعته العالية
        if (!empty($azureKey)) {
            try {
                $endpoint   = trim(env('AZURE_OPENAI_ENDPOINT'));
                $deployment = trim(env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4o'));
                $apiVersion = trim(env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'));
                $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

                $res = Http::withoutVerifying()
                    ->withHeaders(['api-key' => $azureKey, 'Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post($url, [
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.1,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if ($res->successful()) {
                    $translatedJson = $res->json('choices.0.message.content');
                }
            } catch (\Exception $e) {
                Log::warning('[LegalAi] Azure citation translation failed: ' . $e->getMessage());
            }
        }

        // استخدام Gemini كـ Fallback في حال عدم توفر Azure
        if (empty($translatedJson) && !empty($geminiKey)) {
            $translatedJson = $this->geminiService->generateContent([['parts' => [['text' => $prompt]]]], [
                'timeout' => 15,
                'responseMimeType' => 'application/json',
            ]);
        }

        if (!empty($translatedJson)) {
            $rawText = preg_replace('/^```json\s*/i', '', $translatedJson);
            $rawText = preg_replace('/^```\s*/i', '', $rawText);
            $rawText = preg_replace('/\s*```$/i', '', $rawText);

            $parsed = json_decode(trim($rawText), true);
            $itemsList = null;
            if (isset($parsed['items']) && is_array($parsed['items'])) {
                $itemsList = $parsed['items'];
            } elseif (is_array($parsed) && isset($parsed[0])) {
                $itemsList = $parsed;
            } elseif (is_array($parsed)) {
                // Try finding any array inside $parsed
                foreach ($parsed as $k => $v) {
                    if (is_array($v) && isset($v[0]['idx'])) {
                        $itemsList = $v;
                        break;
                    }
                }
            }

            if (is_array($itemsList)) {
                foreach ($itemsList as $p) {
                    $idx = $p['idx'] ?? null;
                    if ($idx !== null && isset($citations[$idx])) {
                        if (!empty($p['title']))   $citations[$idx]['title']   = $p['title'];
                        if (!empty($p['system']))  $citations[$idx]['system']  = $p['system'];
                        if (!empty($p['article'])) $citations[$idx]['article'] = $p['article'];
                        if (!empty($p['text']))    $citations[$idx]['text']    = $p['text'];
                    }
                }
                return $citations;
            } else {
                Log::warning('[LegalAi] Failed to parse itemsList from translation response: ' . mb_substr($rawText, 0, 300));
            }
        }

        return $citations;
    }

    /**
     * التحقق مما إذا كانت الرسالة تحية مجردة أو مجاملة بدون سؤال قانوني
     */
    private function isPureGreeting(string $text): bool
    {
        $raw = trim($text);

        // إذا كانت الرسالة عبارة عن رموز، نقطة (.)، أو علامات ترقيم فقط، أو نص قصير جداً بدون أحرف قانونية
        $clean = mb_strtolower($raw);
        $cleanAlpha = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $cleanAlpha = preg_replace('/\s+/', ' ', trim($cleanAlpha));

        if (empty($cleanAlpha) || mb_strlen($raw) <= 2) {
            return true;
        }

        $greetingWords = [
            'السلام', 'عليكم', 'ورحمة', 'الله', 'وبركاته', 'سلام', 'سلامات',
            'مرحبا', 'مرحباً', 'مرحبتين', 'مراحيب',
            'اهلا', 'أهلا', 'اهلاً', 'أهلاً', 'اهلين', 'أهلين', 'اهدا', 'وسهلاً', 'وسهلا',
            'صباح', 'مساء', 'الخير', 'النور', 'الورد', 'السرور',
            'كيف', 'حالك', 'الحال', 'الحالية', 'كيفك', 'اخبارك', 'شخبارك', 'علومك', 'شلونك',
            'يعطيك', 'العافية', 'يعطيكوا', 'عافية', 'الله', 'يعافيك',
            'شكرا', 'شكراً', 'مشكور', 'مشكورة', 'تسلم', 'تسلمي', 'جزاك', 'خير', 'خيور', 'جزيل', 'الشكر',
            'يا', 'هلا', 'غالي', 'حياك', 'حياكم',
            'hi', 'hello', 'hey', 'good', 'morning', 'evening', 'afternoon', 'how', 'are', 'you', 'thanks', 'thank'
        ];

        $words = explode(' ', $cleanAlpha);

        $nonGreetingWords = array_values(array_filter($words, function ($w) use ($greetingWords) {
            return !in_array($w, $greetingWords, true);
        }));

        if (empty($nonGreetingWords)) {
            return true;
        }

        if (count($nonGreetingWords) <= 2) {
            $legalKeywords = [
                'مادة', 'نظام', 'قضية', 'حكم', 'عقد', 'عمل', 'استئناف', 'محكمة', 'عقوبة',
                'تعويض', 'حق', 'تركة', 'طلاق', 'تزوير', 'شركة', 'شركات', 'عقار', 'إثبات',
                'اثبات', 'سؤال', 'استفسار', 'طلب', 'مساعدة', 'نص', 'شرط', 'شروط', 'لوائح',
                'لائحة', 'تنفيذية', 'مبلغ', 'حقوق', 'راتب', 'فصل', 'إنهاء', 'انهاء'
            ];

            $questionMarkers = [
                'ما', 'ماذا', 'هل', 'كيف', 'متى', 'اين', 'أين', 'كم', 'ليه', 'لماذا', 'مين', 'من'
            ];

            foreach ($nonGreetingWords as $w) {
                if (in_array($w, $legalKeywords, true) || in_array($w, $questionMarkers, true)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * صياغة رد التحية للمستشار القانوني الذكي في الواجهة الرئيسية
     */
    private function getGreetingReply(string $body): string
    {
        $clean = mb_strtolower(trim($body));

        if (mb_strpos($clean, 'شكرا') !== false || mb_strpos($clean, 'شكراً') !== false || mb_strpos($clean, 'مشكور') !== false || mb_strpos($clean, 'يعطيك') !== false || mb_strpos($clean, 'جزاك') !== false) {
            return "العفو! أهلاً وسهلاً بك في أي وقت. ⚖️ كيف يمكنني مساعدتك اليوم في أي استفسار قانوني؟";
        }

        return "أهلاً بك! أنا المستشار القضائي والنظامي الذكي لمنصة رديف ✨\n\nكيف يمكنني مساعدتك اليوم؟ يمكنك طرح أي استفسار يتعلق بالأنظمة والقضايا السعودية.";
    }
}
