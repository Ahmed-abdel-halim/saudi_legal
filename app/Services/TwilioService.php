<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected string $sid;
    protected string $token;
    protected string $from;
    protected string $baseUrl;

    public function __construct()
    {
        $this->sid     = config('services.twilio.sid', '');
        $this->token   = config('services.twilio.token', '');
        $this->from    = config('services.twilio.whatsapp_from', '');
        $this->baseUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}";
    }

    /**
     * إرسال رسالة واتساب عبر Twilio REST API مع دعم الأزرار التفاعلية وصور الوسائط (Logo/Images)
     *
     * @param string      $to        رقم المستلم (whatsapp:+966...)
     * @param string      $body      نص الرسالة
     * @param array       $buttons   مصفوفة الأزرار التفاعلية (مثل: ['القائمة الرئيسية 🏠'])
     * @param string|null $mediaUrl  رابط صورة مرفقة مع الرسالة (مثل صورة اللوجو)
     * @return bool
     */
    /**
     * إرسال رسالة واتساب مع دعم الأزرار التفاعلية الحقيقية عبر Twilio Content Templates
     *
     * @param string      $to          رقم المستلم (whatsapp:+966...)
     * @param string      $body        نص الرسالة
     * @param array       $buttons     مصفوفة الأزرار — تُستخدم كـ Fallback فقط إذا لم يُضبط templateKey
     * @param string|null $mediaUrl    رابط صورة مرفقة (يُهمل عند استخدام ContentSid)
     * @param string|null $templateKey مفتاح التمبلت من config('services.twilio.templates.*')
     * @return bool
     */
    public function sendMessage(string $to, string $body, array $buttons = [], ?string $mediaUrl = null, ?string $templateKey = null): bool
    {
        if (empty($this->sid) || empty($this->token)) {
            Log::warning('[Twilio] TWILIO_SID أو TWILIO_AUTH_TOKEN غير محددان في .env');
            return false;
        }

        // ضمان عدم تجاوز الحد الأقصى لطول الرسالة في واتساب (1600 حرف)
        if (mb_strlen($body) > 1550) {
            $body = mb_substr($body, 0, 1500) . "\n\n⚠️ _(تم اختصار باقي النص لتجاوز الحد الأقصى للرسالة)_";
        }

        $sent = $this->executeSend($to, $body, $buttons, $mediaUrl, $templateKey);

        // محاولة إعادة الإرسال بدون أزرار تفاعلية في حال فشلت المحاولة الأولى
        if (!$sent && !empty($buttons)) {
            Log::info('[Twilio] إعادة المحاولة بدون أزرار تفاعلية...');
            $sent = $this->executeSend($to, $body, [], $mediaUrl, null);
        }

        // محاولة إعادة الإرسال بدون وسائط في حال الفشل
        if (!$sent && !empty($mediaUrl)) {
            Log::info('[Twilio] إعادة المحاولة بدون وسائط...');
            $sent = $this->executeSend($to, $body, [], null, null);
        }

        return $sent;
    }

    private function executeSend(string $to, string $body, array $buttons = [], ?string $mediaUrl = null, ?string $templateKey = null): bool
    {
        try {
            // إذا كانت هناك أزرار تفاعلية — نجرب ContentSid أولاً ثم PersistentAction كـ Fallback
            if (!empty($buttons)) {
                return $this->sendInteractiveMessage($to, $body, $buttons, $templateKey);
            }

            $params = [
                'From' => $this->from,
                'To'   => $to,
                'Body' => $body,
            ];

            if (!empty($mediaUrl)) {
                $params['MediaUrl'] = $mediaUrl;
            }

            $bodyData = http_build_query($params);

            $response = Http::withBasicAuth($this->sid, $this->token)
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->timeout(30)
                ->withBody($bodyData, 'application/x-www-form-urlencoded')
                ->post("{$this->baseUrl}/Messages.json");

            if ($response->successful()) {
                Log::info('[Twilio] رسالة أُرسلت بنجاح', [
                    'to'       => $to,
                    'sid'      => $response->json('sid'),
                ]);
                return true;
            }

            Log::error('[Twilio] فشل إرسال الرسالة', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('[Twilio] استثناء أثناء إرسال الرسالة: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال رسالة واتساب تفاعلية بأزرار Quick Reply حقيقية
     *
     * الأولوية:
     *  1. Twilio Content Template (ContentSid) — يُظهر أزرار WhatsApp الحقيقية
     *  2. PersistentAction — Fallback قديم في حال عدم وجود تمبلت
     */
    private function sendInteractiveMessage(string $to, string $body, array $buttons, ?string $templateKey = null): bool
    {
        // ── 1. محاولة الإرسال عبر Content Template (الطريقة الصحيحة لأزرار WhatsApp) ──
        if ($templateKey) {
            $contentSid = config("services.twilio.templates.{$templateKey}", '');
            if (!empty($contentSid)) {
                $sent = $this->sendWithContentSid($to, $body, $contentSid);
                if ($sent) {
                    return true;
                }
                Log::warning('[Twilio] ContentSid فشل، الرجوع إلى PersistentAction', ['templateKey' => $templateKey]);
            }
        }

        // ── 2. Fallback: PersistentAction (يعمل داخل نافذة 24 ساعة كـ Session Message) ──
        try {
            $bodyData = 'From=' . urlencode($this->from) . '&To=' . urlencode($to) . '&Body=' . urlencode($body);
            foreach (array_slice($buttons, 0, 3) as $btn) {
                $bodyData .= '&PersistentAction=' . urlencode('reply:' . $btn);
            }

            $response = Http::withBasicAuth($this->sid, $this->token)
                ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
                ->timeout(30)
                ->withBody($bodyData, 'application/x-www-form-urlencoded')
                ->post("{$this->baseUrl}/Messages.json");

            if ($response->successful()) {
                Log::info('[Twilio] رسالة تفاعلية (PersistentAction) أُرسلت بنجاح', [
                    'to'      => $to,
                    'sid'     => $response->json('sid'),
                    'buttons' => $buttons,
                ]);
                return true;
            }

            Log::error('[Twilio] فشل إرسال الرسالة التفاعلية', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('[Twilio] استثناء أثناء إرسال الرسالة التفاعلية: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال رسالة عبر Twilio Content Template (ContentSid)
     * هذه هي الطريقة الرسمية لإظهار أزرار WhatsApp التفاعلية الحقيقية
     * الأزرار معرَّفة داخل التمبلت في Twilio Content API — والنص الديناميكي يُمرَّر عبر ContentVariables
     */
    private function sendWithContentSid(string $to, string $body, string $contentSid): bool
    {
        try {
            $bodyData = 'From='             . urlencode($this->from)
                      . '&To='             . urlencode($to)
                      . '&ContentSid='     . urlencode($contentSid)
                      . '&ContentVariables=' . urlencode(json_encode(['1' => $body]));

            $response = Http::withBasicAuth($this->sid, $this->token)
                ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
                ->timeout(30)
                ->withBody($bodyData, 'application/x-www-form-urlencoded')
                ->post("{$this->baseUrl}/Messages.json");

            if ($response->successful()) {
                Log::info('[Twilio] رسالة ContentSid أُرسلت بنجاح', [
                    'to'         => $to,
                    'contentSid' => $contentSid,
                    'sid'        => $response->json('sid'),
                ]);
                return true;
            }

            Log::error('[Twilio] فشل إرسال رسالة ContentSid', [
                'status'     => $response->status(),
                'body'       => $response->body(),
                'contentSid' => $contentSid,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('[Twilio] استثناء ContentSid: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * التحقق من صحة توقيع Twilio لضمان أن الطلب قادم من Twilio فعلاً
     */
    public function validateRequest(string $url, array $params, string $signature): bool
    {
        if (empty($this->token)) {
            return false;
        }

        ksort($params);
        $data = $url;
        foreach ($params as $key => $value) {
            $data .= $key . $value;
        }

        $expectedSignature = base64_encode(hash_hmac('sha1', $data, $this->token, true));
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * إرسال مؤشر "يكتب الآن..." (Typing Indicator) للمستخدم في الواتساب عبر Twilio API
     *
     * @param string $messageId الـ MessageSid الخاص بالرسالة الواردة (SM...)
     * @return bool
     */
    public function sendTypingIndicator(string $messageId): bool
    {
        if (empty($this->sid) || empty($this->token) || empty($messageId)) {
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->timeout(5)
                ->post('https://messaging.twilio.com/v3/Indicators/Typing.json', [
                    'channel'   => 'WHATSAPP',
                    'messageId' => $messageId,
                ]);

            if ($response->successful()) {
                Log::info('[Twilio] تم إرسال مؤشر يكتب الآن (Typing Indicator) بنجاح', ['messageId' => $messageId]);
                return true;
            }

            Log::warning('[Twilio] فشل إرسال مؤشر Typing Indicator', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('[Twilio] استثناء أثناء إرسال Typing Indicator: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * التحقق من إعداد الخدمة
     */
    public function isConfigured(): bool
    {
        return !empty($this->sid) && !empty($this->token) && !empty($this->from);
    }
}
