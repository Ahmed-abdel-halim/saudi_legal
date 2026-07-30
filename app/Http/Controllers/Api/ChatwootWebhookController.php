<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatwootService;
use App\Services\WhatsAppRagService;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatwootWebhookController extends Controller
{
    public function __construct(
        protected ChatwootService $chatwoot,
        protected WhatsAppRagService $rag
    ) {}

    /**
     * استقبال تنبيهات Webhook الواردة من Chatwoot
     */
    public function webhook(Request $request)
    {
        $event = $request->input('event');
        $messageType = $request->input('message_type');
        $isPrivate = (bool) $request->input('private', false);
        $content = trim($request->input('content', ''));

        Log::info('[Chatwoot Webhook] Event received', [
            'event'        => $event,
            'message_type' => $messageType,
            'content'      => mb_substr($content, 0, 50),
        ]);

        // 1. تصفية التنبيهات: نقبل فقط الرسائل الواردة غير الخاصة
        if ($event !== 'message_created' || $isPrivate) {
            return response()->json(['status' => 'ignored', 'reason' => 'Not a public message_created event']);
        }

        // مطابقة نمط نوع الرسالة الواردة (incoming أو 0)
        $isIncoming = ($messageType === 'incoming' || $messageType === 0 || $messageType === '0');
        if (!$isIncoming) {
            return response()->json(['status' => 'ignored', 'reason' => 'Not an incoming message']);
        }

        if (empty($content)) {
            return response()->json(['status' => 'ignored', 'reason' => 'Empty message content']);
        }

        // 2. استخراج بيانات المحادثة والمرسل
        $conversation = $request->input('conversation', []);
        $conversationId = $conversation['id'] ?? null;
        $status = $conversation['status'] ?? 'open';
        $assigneeId = $conversation['assignee_id'] ?? null;

        $sender = $request->input('sender', []);
        $phone = $sender['phone_number'] ?? $sender['identifier'] ?? '';
        $name = $sender['name'] ?? 'عميل واتساب';

        if (!$conversationId) {
            return response()->json(['status' => 'error', 'message' => 'Missing conversation_id'], 400);
        }

        // 3. التحقق من التدخل البشري (Human Handover)
        // إذا كانت المحادثة مسندة لموظف بشري (assignee_id موجود)، يمتنع الذكاء الاصطناعي عن الرد
        if (!empty($assigneeId)) {
            Log::info("[Chatwoot Webhook] Conversation #{$conversationId} assigned to human agent #{$assigneeId}. Skipping AI auto-reply.");
            return response()->json(['status' => 'handover', 'message' => 'Conversation assigned to human agent']);
        }

        // 4. جلب أو إنشاء سجل المحادثة المحلي في رديف لمعرفة حد الرصيد وحفظ السجل
        $localConv = null;
        if (!empty($phone)) {
            $localConv = WhatsAppConversation::firstOrCreate(
                ['phone_number' => $phone],
                [
                    'display_name'  => $name,
                    'session_state' => 'in_chat',
                    'message_count' => 0,
                    'free_limit'    => (int) config('services.twilio.free_limit', 10),
                ]
            );
        }

        // 5. جلب السجل السابق لحفظ السياق
        $history = [];
        if ($localConv) {
            $history = $localConv->getRecentHistory(6)->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])->toArray();

            WhatsAppMessage::create([
                'whatsapp_conversation_id' => $localConv->id,
                'role'                     => 'user',
                'content'                  => $content,
            ]);
        }

        // 6. توليد الرد عبر محرك الذكاء الاصطناعي RAG
        try {
            $result = $this->rag->ask($content, $history);
            $answer = $result['answer'] ?? 'عذراً، لم أتمكن من استخراج الإجابة حالياً.';
            $citations = $result['citations'] ?? [];
            $isGreeting = $result['is_greeting'] ?? false;
        } catch (\Exception $e) {
            Log::error('[Chatwoot Webhook] RAG execution failed: ' . $e->getMessage());
            $answer = 'عذراً، حدث خطأ فني أثناء معالجة استفسارك. يرجى المحاولة مرة أخرى.';
            $citations = [];
            $isGreeting = false;
        }

        // 7. تنسيق الرد للعرض في Chatwoot
        $formattedAnswer = $this->formatForChatwoot($answer, $citations, $isGreeting);

        // 8. حفظ الرد في السجل المحلي
        if ($localConv) {
            WhatsAppMessage::create([
                'whatsapp_conversation_id' => $localConv->id,
                'role'                     => 'assistant',
                'content'                  => $answer,
            ]);
            $localConv->incrementAndTouch();
        }

        // 9. إرسال الرد إلى Chatwoot عبر REST API
        $sent = $this->chatwoot->sendMessage((int) $conversationId, $formattedAnswer, 'outgoing');

        return response()->json([
            'status'          => $sent ? 'success' : 'failed_to_post',
            'conversation_id' => $conversationId,
        ]);
    }

    /**
     * تنسيق الرد لصندوق محادثات Chatwoot
     */
    protected function formatForChatwoot(string $answer, array $citations = [], bool $isGreeting = false): string
    {
        $text = strip_tags($answer);
        $text = trim($text);

        if ($isGreeting || empty($citations)) {
            return $text;
        }

        $sourcesText = "\n\n📚 **المصادر والمرجعيات القضائية:**";
        $seen = [];
        $count = 0;

        foreach ($citations as $cite) {
            $rawTitle = trim($cite['title'] ?? '');
            if (empty($rawTitle) || isset($seen[$rawTitle]) || $count >= 4) continue;
            $seen[$rawTitle] = true;
            $count++;

            $systemName = $cite['system'] ?? 'السوابق والأحكام القضائية';
            $sourcesText .= "\n• 📜 **{$rawTitle}** - {$systemName}";
        }

        return $text . $sourcesText;
    }
}
