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

        return response()->json($conversations);
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
        $request->validate([
            'question'          => 'required|string|max:1000',
            'conversation_uuid' => 'nullable|string|uuid',
        ]);

        $question = $request->question;
        $uuid     = $request->conversation_uuid;

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

        // أ. استخراج أي مواد مذكورة في السؤال نفسه مباشرة
        $queryArticles = $this->referenceService->getMentionedArticles($searchQuery);
        foreach ($queryArticles as $art) {
            $allArticles->push($art);
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

        // Tier 1: Azure AI Search (Split hybrid search)
        if ($this->azureService->isEnabled()) {
            Log::info('[LegalAi] Using Azure Split Search for: ' . $searchQuery);

            // أ. بحث عن أحكام قضائية وسوابق (تستثنى منها المواد)
            $cases = $this->azureService->hybridSearch($searchQuery, 5, ['!source_type' => 'article']);

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

            $cases = $this->qdrantService->search($searchQuery, 5, ['!source_type' => 'article']);
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

        // دمج النتائج المطابقة تماماً برقم القضية في بداية السياق لضمان قراءتها
        if ($exactMatches->isNotEmpty()) {
            $contextTasks = $exactMatches->toBase()->merge($contextTasks)->unique('id');
            // تحديد طريقة البحث كبحث هجين مخصص بالرقم
            $searchMethod = ($searchMethod === 'keyword') ? 'hybrid_exact' : $searchMethod . '_hybrid';
        }

        // إزالة التكرار في الأحكام المتطابقة النص لمنع التكرار البصري في المراجع والـ Context
        $contextTasks = $contextTasks->unique(function ($task) {
            $text = trim($task->case_text ?: $task->correct_answer ?: '');
            if ($text === '') {
                return 'empty_' . ($task->id ?? uniqid());
            }
            return md5($text);
        });

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

        // بناء المراجع للأحكام والمهام
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
            ];
        }

        // إضافة الأنظمة والمواد المترابطة إلى السياق
        if ($allArticles->isNotEmpty()) {
            $contextText .= "--- نصوص الأنظمة السعودية ذات الصلة ---\n";
            foreach ($allArticles->unique('id') as $article) {
                $contextText .= "[{$article->legislation_title} - {$article->article_title}]:\n{$article->content}\n\n";

                $citations[] = [
                    'type'    => 'law_article',
                    'title'   => "{$article->article_title} - {$article->legislation_title}",
                    'article' => "نص نظام",
                    'text'    => $article->content,
                ];
            }
        }

        // 5. بناء الـ Prompt وهيكلة تاريخ الرسائل بالكامل
        $prompt = "أنت 'رديف القانوني'، مستشار قانوني سعودي فصيح وخبير. 
مهمتك الأساسية: الإجابة على سؤال العميل بدقة بناءً على 'المعلومات القانونية والمراجع' المرفقة.

القواعد الذهبية للاستشارة:
1. (أولوية النظام): اعتمد دائماً على 'نصوص الأنظمة' (المواد القانونية) كمرجعك الأول والأهم. الأحكام القضائية والاستشارات السابقة تُستخدم كأمثلة داعمة فقط إذا طابقت موضوع السؤال.
2. (تجاهل السياق الخاطئ): إذا كان سؤال العميل في مجال معين، ووجدت في المراجع المرفقة حكماً قضائياً في مجال آخر، **تجاهل الحكم القضائي تماماً** ولا تذكره أو تلخصه في إجابتك أبداً.
3. (الإجابة المباشرة): إذا طرح العميل سؤالاً محدداً، أجب عليه مباشرة ولا تقم بـ 'تحليل' أو 'تلخيص' أي نصوص قضائية مرفقة إلا إذا طلب العميل ذلك صراحةً.
4. دائماً ابدأ إجابتك بـ 'أهلاً بك، بصفتي رديف القانوني...'.
5. إذا كانت المراجع لا تحتوي على الإجابة، أخبر العميل بوضوح: 'عذراً، لم أجد نصوصاً نظامية محددة في المراجع الحالية للإجابة على سؤالك.'.
6. (تنبيه هام): بعض المراجع المرفقة هي إجابات مقترحة من الذكاء الاصطناعي ولم يتم تدقيقها بعد من قبل محامٍ معتمد. استخدمها كمرشد عام، لكن نبّه العميل في نهاية إجابتك بأن 'هذه الاستشارة إرشادية ولا تغني عن الرجوع لمحامٍ مختص.'.

المعلومات القانونية المتاحة (المراجع):
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

        // 7. حفظ الرسائل الجديدة في قاعدة البيانات وتحديث وقت المحادثة
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'user',
            'message'            => $question,
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'model',
            'message'            => $answer,
            'citations'          => $citations,
        ]);

        $conversation->touch();

        return response()->json([
            'answer'            => $answer,
            'citations'         => $citations,
            'conversation_uuid' => $conversation->uuid,
            'search_method'     => $searchMethod,
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
}
