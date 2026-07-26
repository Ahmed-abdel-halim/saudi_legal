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
    public function sendMessage(string $to, string $body, array $buttons = [], ?string $mediaUrl = null): bool
    {
        if (empty($this->sid) || empty($this->token)) {
            Log::warning('[Twilio] TWILIO_SID أو TWILIO_AUTH_TOKEN غير محددان في .env');
            return false;
        }

        try {
            $params = [
                'From' => $this->from,
                'To'   => $to,
                'Body' => $body,
            ];

            if (!empty($mediaUrl)) {
                $params['MediaUrl'] = $mediaUrl;
            }

            // بناء استعلام URL-encoded لضمان تكرار معيار PersistentAction بالشكل الصحيح في Twilio
            $bodyData = http_build_query($params);

            if (!empty($buttons)) {
                foreach ($buttons as $btn) {
                    $bodyData .= '&PersistentAction=' . urlencode('reply:' . $btn);
                }
            }

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
                    'buttons'  => $buttons,
                    'media_url'=> $mediaUrl,
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
     * التحقق من إعداد الخدمة
     */
    public function isConfigured(): bool
    {
        return !empty($this->sid) && !empty($this->token) && !empty($this->from);
    }
}
