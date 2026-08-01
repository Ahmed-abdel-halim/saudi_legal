<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatwootService
{
    protected string $baseUrl;
    protected string $accountId;
    protected string $token;
    protected string $inboxId;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.chatwoot.url', 'https://app.chatwoot.com'), '/');
        $this->accountId = (string) config('services.chatwoot.account_id', '');
        $this->token     = (string) config('services.chatwoot.token', '');
        $this->inboxId   = (string) config('services.chatwoot.inbox_id', '');
    }

    /**
     * التحقق من اكتمال إعدادات Chatwoot
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountId) && !empty($this->token) && !empty($this->inboxId);
    }

    /**
     * إعداد الهيدر الموحد لطلبات API في Chatwoot
     */
    protected function http()
    {
        return Http::withHeaders([
            'api_access_token' => $this->token,
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
        ])->timeout(15);
    }

    /**
     * البحث عن جهة اتصال أو إنشاؤها ورقم الهاتف
     */
    public function findOrCreateContact(string $phone, string $name = ''): ?int
    {
        if (!$this->isConfigured()) return null;

        $cleanPhone = '+' . preg_replace('/\D/', '', $phone);
        $displayName = !empty($name) ? $name : $cleanPhone;

        try {
            // 1. البحث عن جهة الاتصال
            $searchUrl = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/search";
            $response = $this->http()->get($searchUrl, ['q' => $cleanPhone]);

            if ($response->successful()) {
                $payload = $response->json();
                $contacts = $payload['payload'] ?? [];
                if (!empty($contacts)) {
                    return (int) $contacts[0]['id'];
                }
            }

            // 2. إنشاء جهة اتصال جديدة إذا لم تكن موجودة
            $createUrl = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts";
            $createResponse = $this->http()->post($createUrl, [
                'name'         => $displayName,
                'phone_number' => $cleanPhone,
            ]);

            if ($createResponse->successful()) {
                $contactData = $createResponse->json('payload.contact') ?? $createResponse->json('payload');
                return (int) ($contactData['id'] ?? null);
            }

            Log::warning('[Chatwoot] FAILED creating contact', [
                'phone'  => $cleanPhone,
                'status' => $createResponse->status(),
                'body'   => $createResponse->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('[Chatwoot] Exception in findOrCreateContact: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * البحث عن محادثة نشطة لجهة الاتصال أو إنشاؤها
     */
    public function findOrCreateConversation(int $contactId, string $phone): ?array
    {
        if (!$this->isConfigured()) return null;

        try {
            // 1. البحث عن المحادثات الحالية لهذا الرقم
            $convUrl = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/conversations";
            $response = $this->http()->get($convUrl);

            if ($response->successful()) {
                $conversations = $response->json('payload') ?? [];
                foreach ($conversations as $conv) {
                    if ((string)($conv['inbox_id'] ?? '') === $this->inboxId && ($conv['status'] ?? '') !== 'resolved') {
                        return [
                            'id'          => (int) $conv['id'],
                            'status'      => $conv['status'] ?? 'open',
                            'assignee_id' => $conv['assignee_id'] ?? null,
                        ];
                    }
                }
            }

            // 2. إنشاء محادثة جديدة تحت صندوق الوارد 지정
            $createUrl = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations";
            
            $createResponse = $this->http()->post($createUrl, [
                'inbox_id'   => (int) $this->inboxId,
                'contact_id' => (int) $contactId,
            ]);

            if ($createResponse->successful()) {
                $convData = $createResponse->json();
                return [
                    'id'          => (int) ($convData['id'] ?? null),
                    'status'      => $convData['status'] ?? 'open',
                    'assignee_id' => $convData['assignee_id'] ?? null,
                ];
            }

            Log::warning('[Chatwoot] FAILED creating conversation', [
                'contactId' => $contactId,
                'status'    => $createResponse->status(),
                'body'      => $createResponse->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('[Chatwoot] Exception in findOrCreateConversation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إرسال رسالة إلى المحادثة في Chatwoot
     *
     * @param int    $conversationId
     * @param string $content
     * @param string $messageType ('outgoing', 'incoming')
     * @param bool   $isPrivate
     * @return bool
     */
    public function sendMessage(int $conversationId, string $content, string $messageType = 'outgoing', bool $isPrivate = false): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $msgUrl = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages";
            $response = $this->http()->post($msgUrl, [
                'content'      => $content,
                'message_type' => $messageType,
                'private'      => $isPrivate,
            ]);

            if ($response->successful()) {
                Log::info('[Chatwoot] Message posted successfully', [
                    'conversation_id' => $conversationId,
                    'type'            => $messageType,
                ]);
                return true;
            }

            Log::error('[Chatwoot] Failed posting message', [
                'conversation_id' => $conversationId,
                'status'          => $response->status(),
                'body'            => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('[Chatwoot] Exception in sendMessage: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * مزامنة الرسالة الواردة ورد الذكاء الاصطناعي مع Chatwoot كـ سجل داخلي لمنع تكرار الإرسال
     */
    public function syncIncomingAndOutgoing(string $phone, string $name, string $userMessage, string $aiReply): bool
    {
        if (!$this->isConfigured()) return false;

        $contactId = $this->findOrCreateContact($phone, $name);
        if (!$contactId) return false;

        $conv = $this->findOrCreateConversation($contactId, $phone);
        if (!$conv || empty($conv['id'])) return false;

        $convId = $conv['id'];

        // 1. تسجيل رسالة العميل الواردة
        if (!empty($userMessage)) {
            $sent = $this->sendMessage($convId, $userMessage, 'incoming');
            if (!$sent) {
                $this->sendMessage($convId, "💬 **سؤال العميل:**\n" . $userMessage, 'outgoing', true);
            }
        }

        // 2. تسجيل رد الذكاء الاصطناعي كـ ملاحظة خاصة داخلية لمنع إرسال رسالة مكررة للعميل عبر Chatwoot
        if (!empty($aiReply)) {
            $this->sendMessage($convId, "🤖 **رد منصة رديف:**\n" . $aiReply, 'outgoing', true);
        }

        return true;
    }
}
