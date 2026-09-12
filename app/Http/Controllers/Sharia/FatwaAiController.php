<?php

namespace App\Http\Controllers\Sharia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Sharia\ShariaSearchService;
use App\Services\GeminiApiService;
use App\Services\BedrockApiService;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiMessageFeedback;
use App\Models\FatwaQaPair;
use App\Models\ShariaCitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FatwaAiController extends Controller
{
    protected $searchService;
    protected $geminiService;
    protected $bedrockService;

    public function __construct(
        ShariaSearchService $searchService,
        GeminiApiService $geminiService,
        BedrockApiService $bedrockService
    ) {
        $this->searchService  = $searchService;
        $this->geminiService  = $geminiService;
        $this->bedrockService = $bedrockService;
    }

    /**
     * Display the Dedicated Smart Islamic Mufti Interface.
     */
    public function index()
    {
        return view('sharia.chat');
    }

    /**
     * Submit feedback on fatwa response (helpful / unhelpful).
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

        $feedback = AiMessageFeedback::create([
            'user_id'            => auth()->id(),
            'ai_conversation_id' => $conversationId,
            'ai_message_id'     => $validated['ai_message_id'] ?? null,
            'rating'             => $validated['rating'],
            'reason'             => ($validated['reason'] ?? '') . ' [Sharia Mufti]',
            'user_query'         => $validated['user_query'] ?? null,
            'ai_response'        => $validated['ai_response'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['rating'] === 'like'
                ? 'جزاكم الله خيراً على تقييمكم الإيجابي!'
                : 'شكراً لملاحظتكم، تم حفظها لمراجعتها من الباحثين الشرعيين في رديف.',
            'feedback_id' => $feedback->id,
        ]);
    }

    /**
     * Get conversations history for user.
     */
    public function getConversations(Request $request)
    {
        $userId = auth()->id();
        $conversations = collect();

        if ($userId) {
            $conversations = AiConversation::where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->take(20)
                ->get(['uuid', 'title', 'updated_at']);
        } else {
            $sessionUuids = session()->get('sharia_conversations', []);
            if (!empty($sessionUuids)) {
                $conversations = AiConversation::whereIn('uuid', $sessionUuids)
                    ->orderBy('updated_at', 'desc')
                    ->get(['uuid', 'title', 'updated_at']);
            }
        }

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get messages for a given conversation UUID.
     */
    public function getMessages($uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        if ($conversation->user_id && $conversation->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بمشاهدة هذه المحادثة.');
        }

        return response()->json([
            'conversation' => [
                'uuid'  => $conversation->uuid,
                'title' => $conversation->title,
            ],
            'messages' => $conversation->messages->map(function ($m) {
                return [
                    'role'      => $m->role === 'model' ? 'model' : 'user',
                    'message'   => $m->message,
                    'citations' => $m->citations,
                ];
            }),
        ]);
    }

    /**
     * Delete conversation.
     */
    public function deleteConversation($uuid)
    {
        $conversation = AiConversation::where('uuid', $uuid)->firstOrFail();

        if ($conversation->user_id && $conversation->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذه المحادثة.');
        }

        $conversation->delete();

        $uuids = session()->get('sharia_conversations', []);
        if (($key = array_search($uuid, $uuids)) !== false) {
            unset($uuids[$key]);
            session()->put('sharia_conversations', array_values($uuids));
        }

        return response()->json(['success' => true]);
    }

    /**
     * Main Ask Endpoint: Zero-Hallucination RAG Sharia Assistant.
     */
    public function ask(Request $request)
    {
        set_time_limit(180);

        $request->validate([
            'question'          => 'required|string|max:4000',
            'conversation_uuid' => 'nullable|string|uuid',
        ]);

        $question = trim($request->question);
        $uuid     = $request->conversation_uuid;

        // 1. Manage Conversation
        $conversation = null;
        if ($uuid) {
            $conversation = AiConversation::where('uuid', $uuid)->first();
        }

        if (!$conversation) {
            $conversation = AiConversation::create([
                'uuid'    => (string) Str::uuid(),
                'user_id' => auth()->id(),
                'title'   => Str::limit($question, 60),
            ]);

            // Save to guest session
            $sessionUuids = session()->get('sharia_conversations', []);
            $sessionUuids[] = $conversation->uuid;
            session()->put('sharia_conversations', array_unique($sessionUuids));
        }

        // Save User Message
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'user',
            'message'            => $question,
        ]);

        // 2. Hybrid RAG Search over verified Sharia Knowledge Base
        // Uses strict substantive keyword matching and thresholding
        $results = $this->searchService->search($question, 3);

        $citations = [
            'quran'        => [],
            'hadiths'      => [],
            'scholars'     => [],
            'authorities'  => [],
            'items'        => [],
            'verified'     => false,
        ];

        $aiAnswer = null;

        // ── CASE A: NO MATCH FOUND (Strict Piety & Deferral) ───────────────────
        if ($results->isEmpty()) {
            // Do NOT fabricate citations or attach unrelated fatwas!
            $aiAnswer = "الحمد لله والصلاة والسلام على رسول الله، أما بعد:\n\n" .
                        "هذه المسألة المعينة لم ترد لها فتوى موثقة ومطابقة في قاعدة بياناتنا الشرعية المعتمدة في الوقت الراهن.\n\n" .
                        "وامتثالاً لقول الله تعالى: ﴿فَاسْأَلُوا أَهْلَ الذِّكْرِ إِن كُنتُمْ لَا تَعْلَمُونَ﴾ [الأنبياء: 7] وحرصاً على عدم الإفتاء بغير علم أو نص قاطع، ننصحكم بالرجوع المباشر إلى الجهات الإفتائية المعتبرة (كالرئاسة العامة للبحوث العلمية والإفتاء أو دار الإفتاء الرسمية).\n\n" .
                        "وفقكم الله وسددكم، والله تعالى أعلم وأحكم.";
        } else {
            // ── CASE B: SUBSTANTIVE MATCH FOUND ──────────────────────────────────
            $bestMatch = $results->first();
            $authority = $bestMatch->record->source_authority ?? 'اللجنة الدائمة للبحوث العلمية والإفتاء';

            $citations['authorities'][] = $authority;
            if ($bestMatch->traffic_light === 'green' || $bestMatch->review_status === 'Approved') {
                $citations['verified'] = true;
            }

            // Collect Citations ONLY from the matching records
            foreach ($bestMatch->citations as $citation) {
                if ($citation->citation_type === 'quran') {
                    $citations['quran'][] = [
                        'surah' => $citation->surah_name,
                        'ayah'  => $citation->ayah_number,
                        'text'  => $citation->quran_text,
                    ];
                } elseif ($citation->citation_type === 'hadith') {
                    $citations['hadiths'][] = [
                        'text'   => $citation->hadith_text,
                        'source' => $citation->hadith_source ?? 'صحيح السنة',
                        'grade'  => $citation->hadith_grade ?? 'صحيح',
                    ];
                } elseif ($citation->citation_type === 'scholar') {
                    if (!in_array($citation->scholar_name, $citations['scholars']) && !empty($citation->scholar_name)) {
                        $citations['scholars'][] = $citation->scholar_name;
                    }
                }
            }

            $citations['items'][] = [
                'type'        => 'fatwa',
                'title'       => $bestMatch->question,
                'authority'   => $authority,
                'text'        => Str::limit($bestMatch->final_answer, 200),
                'verified'    => $bestMatch->traffic_light === 'green',
            ];

            // Build Prompt for LLM formatting if available
            $contextText = "المصدر الشرعي المعتمد: [{$authority}]\n" .
                           "السؤال الشرعي الأصلي: " . $bestMatch->question . "\n" .
                           "الفتوى والحكم المعتمد: " . $bestMatch->final_answer;

            $systemPrompt = "أنت «المفتي الإسلامي الذكي» التابع لمنصة رديف. مهمتك صياغة الفتوى بناءً حصراً على الفتوى المعتمدة المرفقة في السياق أدناه بدقة تامة دون زيادة أو اختراع. اذكر الحكم بوضوح متبوعاً بالأدلة المنقولة، ثم انسب الفتوى لـ [{$authority}].";
            $userPrompt = "سؤال المستفيد: {$question}\n\nالسياق المعتمد:\n{$contextText}";

            // Try Gemini API
            $geminiKey = trim(config('services.gemini.key', env('GEMINI_API_KEY', '')));
            if (!empty($geminiKey) && $this->geminiService) {
                try {
                    $contents = [
                        ['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n\n" . $userPrompt]]]
                    ];
                    $raw = $this->geminiService->generateContent($contents, ['temperature' => 0.0, 'timeout' => 20]);
                    if (!empty($raw)) {
                        $aiAnswer = $this->cleanModelResponse($raw);
                    }
                } catch (\Exception $e) {
                    Log::warning("[FatwaAi] Gemini call failed: " . $e->getMessage());
                }
            }

            // Deterministic verified fallback from the exact matched fatwa
            if (empty($aiAnswer)) {
                $aiAnswer = "الحمد لله والصلاة والسلام على رسول الله، أما بعد:\n\n" .
                            "فبناءً على الفتوى المعتمدة الصادرة عن [{$authority}] في المسألة:\n\n" .
                            $bestMatch->final_answer . "\n\n" .
                            "والله تعالى أعلم وأحكم.";
            }
        }

        // Save Model Message
        $aiMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'model',
            'message'            => $aiAnswer,
            'citations'          => $citations,
        ]);

        return response()->json([
            'conversation_uuid' => $conversation->uuid,
            'ai_message_id'     => $aiMessage->id,
            'answer'            => $aiAnswer,
            'citations'         => $citations,
        ]);
    }

    /**
     * Clean model response from thought tags or reasoning artifacts.
     */
    private function cleanModelResponse(string $text): string
    {
        $text = preg_replace('/<thought>[\s\S]*?<\/thought>/iu', '', $text);
        $text = preg_replace('/^(THOUGHT|Reasoning):[\s\S]*?(?=(\r?\n\r?\n[^\s]|\r?\n[^\s]|$))/iu', '', $text);
        return trim($text);
    }
}
