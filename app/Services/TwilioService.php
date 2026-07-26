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
     * إرسال رسالة واتساب عبر Twilio REST API
     */
    public function sendMessage(string $to, string $body): bool
    {
        if (empty($this->sid) || empty($this->token)) {
            Log::warning('[Twilio] TWILIO_SID أو TWILIO_AUTH_TOKEN غير محددان في .env');
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->asForm()
                ->timeout(30)
                ->post("{$this->baseUrl}/Messages.json", [
                    'From' => $this->from,
                    'To'   => $to,
                    'Body' => $body,
                ]);

            if ($response->successful()) {
                Log::info('[Twilio] رسالة أُرسلت بنجاح', [
                    'to'  => $to,
                    'sid' => $response->json('sid'),
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

        // ترتيب الـ params أبجدياً ودمجها مع الـ URL
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
