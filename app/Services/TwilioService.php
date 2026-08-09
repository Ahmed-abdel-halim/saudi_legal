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

        // ضمان عدم تجاوز الحد الأقصى لطول الرسالة في واتساب (1600 حرف)
        if (mb_strlen($body) > 1550) {
            $body = mb_substr($body, 0, 1500) . "\n\n⚠️ _(تم اختصار باقي النص لتجاوز الحد الأقصى للرسالة)_";
        }

        $sent = $this->executeSend($to, $body, $buttons, $mediaUrl);

        // محاولة إعادة الإرسال بدون أزرار تفاعلية في حال فشلت المحاولة الأولى
        if (!$sent && !empty($buttons)) {
            Log::info('[Twilio] إعادة المحاولة بدون أزرار تفاعلية...');
            $sent = $this->executeSend($to, $body, [], $mediaUrl);
        }

        // محاولة إعادة الإرسال بدون وسائط في حال الفشل
        if (!$sent && !empty($mediaUrl)) {
            Log::info('[Twilio] إعادة المحاولة بدون وسائط...');
            $sent = $this->executeSend($to, $body, [], null);
        }

        return $sent;
    }

    private function executeSend(string $to, string $body, array $buttons = [], ?string $mediaUrl = null): bool
    {
        try {
            // إذا كانت هناك أزرار تفاعلية نستخدم Interactive Messages API لواتساب
            if (!empty($buttons)) {
                return $this->sendInteractiveMessage($to, $body, $buttons);
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
     * إرسال رسالة واتساب تفاعلية بأزرار Quick Reply حقيقية عبر WhatsApp Interactive Messages
     */
    private function sendInteractiveMessage(string $to, string $body, array $buttons): bool
    {
        // تحويل الأزرار إلى JSON format المطلوب لـ WhatsApp Interactive API
        $buttonsList = [];
        foreach (array_slice($buttons, 0, 3) as $index => $btn) {
            $buttonsList[] = [
                'type'  => 'reply',
                'reply' => [
                    'id'    => 'btn_' . $index,
                    'title' => mb_substr($btn, 0, 20), // واتساب يقبل 20 حرف كحد أقصى للزر
                ],
            ];
        }

        $interactive = [
            'type' => 'button',
            'body' => [
                'text' => $body,
            ],
            'action' => [
                'buttons' => $buttonsList,
            ],
        ];

        $params = [
            'From'        => $this->from,
            'To'          => $to,
            'Body'        => $body,
            'ContentType' => 'application/json',
            'ContentVariables' => json_encode([]),
        ];

        // محاولة إرسال Interactive Message
        try {
            $bodyData = http_build_query([
                'From'             => $this->from,
                'To'               => $to,
                'Body'             => $body,
                'PersistentAction' => array_map(fn($b) => 'reply:' . $b, $buttons),
            ]);

            // إعادة بناء PersistentAction كمصفوفة (Twilio يقبل تكرار المفتاح)
            $bodyData = 'From=' . urlencode($this->from) . '&To=' . urlencode($to) . '&Body=' . urlencode($body);
            foreach (array_slice($buttons, 0, 3) as $btn) {
                $bodyData .= '&PersistentAction=' . urlencode('reply:' . $btn);
            }

            $response = Http::withBasicAuth($this->sid, $this->token)
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->timeout(30)
                ->withBody($bodyData, 'application/x-www-form-urlencoded')
                ->post("{$this->baseUrl}/Messages.json");

            if ($response->successful()) {
                Log::info('[Twilio] رسالة تفاعلية أُرسلت بنجاح', [
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
