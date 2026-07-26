<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\TwilioService;
use App\Services\WhatsAppRagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(
        protected TwilioService      $twilio,
        protected WhatsAppRagService $rag
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  WEBHOOK — يستقبل كل رسائل Twilio الواردة
    // ─────────────────────────────────────────────────────────────────────────

    public function webhook(Request $request)
    {
        // 1. التحقق من صحة التوقيع إذا كانت البيانات متوفرة (اختياري في وضع التطوير)
        if (config('app.env') === 'production') {
            $signature = $request->header('X-Twilio-Signature', '');
            $url       = $request->fullUrl();
            if (!$this->twilio->validateRequest($url, $request->all(), $signature)) {
                Log::warning('[WhatsApp] Webhook request with invalid Twilio signature rejected');
                return response('Unauthorized', 403);
            }
        }

        // 2. استخراج بيانات الرسالة الواردة من Twilio
        $from        = $request->input('From', '');   // مثال: whatsapp:+966500000000
        $body        = trim($request->input('Body', ''));
        $profileName = $request->input('ProfileName', '');

        Log::info('[WhatsApp] رسالة واردة', ['from' => $from, 'body' => $body]);

        if (empty($from) || empty($body)) {
            return response('OK', 200);
        }

        // 3. إيجاد أو إنشاء جلسة المحادثة لهذا الرقم
        $conversation = WhatsAppConversation::firstOrCreate(
            ['phone_number' => $from],
            [
                'display_name'  => $profileName,
                'session_state' => 'idle',
                'message_count' => 0,
                'free_limit'    => (int) config('services.twilio.free_limit', 10),
            ]
        );

        // تحديث الاسم إذا تغير
        if ($profileName && $conversation->display_name !== $profileName) {
            $conversation->update(['display_name' => $profileName]);
        }

        // 4. كشف النية (Intent Detection)
        $intent = $this->detectIntent($body, $conversation->session_state);

        // 5. معالجة الـ Intent وإرسال الرد المناسب
        $reply = match ($intent) {
            'start_chat'  => $this->handleStartChat($conversation),
            'end_chat'    => $this->handleEndChat($conversation),
            'legal_query' => $this->handleLegalQuery($conversation, $body),
            'idle_prompt' => $this->getIdlePrompt(),
            default       => $this->getDefaultReply(),
        };

        // 6. إرسال الرد عبر Twilio
        $this->twilio->sendMessage($from, $reply);

        return response('OK', 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INTENT DETECTION
    // ─────────────────────────────────────────────────────────────────────────

    private function detectIntent(string $body, string $sessionState): string
    {
        $normalizedBody = mb_strtolower(trim($body));

        // كلمات تفعيل جلسة المساعد
        $startTriggers = [
            'مساعد قانوني', 'مساعد', 'قانوني', '1', 'ابدأ', 'ابدا',
            'تصفح المساعدة', 'استشارة', 'مرحبا', 'مرحباً', 'هاي', 'hi', 'hello',
        ];

        // كلمات إنهاء الجلسة
        $endTriggers = ['رجوع', '0', 'خروج', 'انهاء', 'إنهاء', 'وداعا', 'وداعاً', 'bye', 'exit', 'quit'];

        foreach ($startTriggers as $trigger) {
            if (mb_strpos($normalizedBody, $trigger) !== false) {
                return 'start_chat';
            }
        }

        foreach ($endTriggers as $trigger) {
            if (mb_strpos($normalizedBody, $trigger) !== false) {
                return 'end_chat';
            }
        }

        // إذا الجلسة نشطة → معالجة كسؤال قانوني
        if ($sessionState === 'in_chat') {
            return 'legal_query';
        }

        // الجلسة غير نشطة والرسالة غير محددة
        return 'idle_prompt';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HANDLERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * بدء جلسة المحادثة وإرسال رسالة الترحيب
     */
    private function handleStartChat(WhatsAppConversation $conversation): string
    {
        $conversation->update([
            'session_state'  => 'in_chat',
            'last_active_at' => now(),
        ]);

        $name = $conversation->display_name ? "، {$conversation->display_name}" : '';
        $remaining = max(0, $conversation->free_limit - $conversation->message_count);

        return "🌟 *مرحباً بك{$name} في المساعد القانوني لمنصة رديف!*

⚖️ يمكنك طرح أي سؤال قانوني يخص الأنظمة والقضايا السعودية، وسأجيبك بناءً على أحدث المراجع القانونية.

📌 *أمثلة على الأسئلة:*
• على من يقع عبء إثبات التزوير؟
• ما نص المادة 15 من نظام الشركات؟
• ما شروط تملك العقار للأجانب في السعودية؟

💡 لديك *{$remaining} استشارة مجانية* متبقية.
اكتب سؤالك الآن، أو اكتب *رجوع* للخروج.";
    }

    /**
     * إنهاء الجلسة وإرسال رسالة الوداع
     */
    private function handleEndChat(WhatsAppConversation $conversation): string
    {
        $conversation->update(['session_state' => 'idle']);

        return "👋 *تم إنهاء جلسة الاستشارة بنجاح.*

شكراً لاستخدامك المساعد القانوني لمنصة *رديف*.
يمكنك العودة في أي وقت بكتابة: *مساعد قانوني* 🔁";
    }

    /**
     * معالجة السؤال القانوني عبر محرك RAG
     */
    private function handleLegalQuery(WhatsAppConversation $conversation, string $question): string
    {
        // التحقق من حد الرسائل
        if ($conversation->hasReachedLimit()) {
            return $this->getLimitReachedMessage($conversation);
        }

        // جلب تاريخ المحادثة الأخير
        $history = $conversation->getRecentHistory(6)->map(fn($m) => [
            'role'    => $m->role,
            'content' => $m->content,
        ])->toArray();

        // حفظ سؤال المستخدم
        WhatsAppMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'role'                     => 'user',
            'content'                  => $question,
        ]);

        // استدعاء محرك الذكاء الاصطناعي
        try {
            $result = $this->rag->ask($question, $history);
            $answer = $result['answer'];
            $citations = $result['citations'];
        } catch (\Exception $e) {
            Log::error('[WhatsApp] RAG error: ' . $e->getMessage());
            $answer    = 'عذراً، حدث خطأ فني. يرجى المحاولة لاحقاً.';
            $citations = [];
        }

        // تنسيق الإجابة لواتساب
        $formattedAnswer = $this->formatForWhatsApp($answer, $citations);

        // حفظ رد المساعد
        WhatsAppMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'role'                     => 'assistant',
            'content'                  => $answer,
        ]);

        // تحديث عداد الرسائل
        $conversation->incrementAndTouch();

        // إضافة تذكير بالرصيد المتبقي إذا اقترب من الحد
        $remaining = max(0, $conversation->free_limit - $conversation->message_count - 1);
        if ($remaining <= 2 && $remaining > 0) {
            $formattedAnswer .= "\n\n⚠️ _تبقى لك {$remaining} استشارة مجانية فقط._";
        }

        return $formattedAnswer;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FORMAT & MESSAGES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * تحويل الإجابة من HTML/Markdown إلى تنسيق واتساب (plain text + WhatsApp bold)
     */
    private function formatForWhatsApp(string $answer, array $citations): string
    {
        // إزالة HTML tags
        $text = strip_tags($answer);

        // تحويل Markdown headers إلى bold في واتساب
        $text = preg_replace('/^#{1,3}\s+(.+)$/mu', '*$1*', $text);

        // تحويل bold Markdown إلى WhatsApp bold
        $text = preg_replace('/\*\*(.+?)\*\*/u', '*$1*', $text);

        // تنظيف المسافات الزائدة
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        // إضافة المصادر إذا وُجدت
        if (!empty($citations)) {
            $sourcesText = "\n\n📚 *المصادر:*";
            $seen        = [];
            $count       = 0;
            foreach ($citations as $cite) {
                $key = trim($cite['title'] ?? '');
                if (empty($key) || isset($seen[$key]) || $count >= 3) continue;
                $seen[$key] = true;
                $count++;
                $system = !empty($cite['system']) ? " - {$cite['system']}" : '';
                $sourcesText .= "\n• {$key}{$system}";
            }
            $text .= $sourcesText;
        }

        return $text;
    }

    private function getLimitReachedMessage(WhatsAppConversation $conversation): string
    {
        $appUrl = config('app.url', 'https://radiif.com');
        return "🔒 *لقد استنفدت رصيدك المجاني ({$conversation->free_limit} استشارات).*

للاستمرار في الاستفادة من المساعد القانوني، يرجى التسجيل على منصة رديف:
🔗 {$appUrl}/register/company

✨ سيحصل كل عضو على *20 استشارة مجانية* إضافية عند التسجيل!";
    }

    private function getIdlePrompt(): string
    {
        return "👋 مرحباً! أنا *المساعد القانوني لمنصة رديف*.

لبدء جلسة استشارة قانونية، اكتب:
➡️ *مساعد قانوني*";
    }

    private function getDefaultReply(): string
    {
        return "لم أفهم طلبك. اكتب *مساعد قانوني* لبدء استشارة قانونية، أو *رجوع* للخروج.";
    }
}
