<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudioTranscriptionService
{
    public function __construct(
        protected GeminiApiService $geminiService
    ) {}

    /**
     * تفريغ المقطع الصوتي إلى نص مع التعرف التلقائي على اللهجة واللغة (Auto-Detect Dialect & Language)
     *
     * @param string $audioBytes محتوى الملف الصوتي كـ Raw Bytes
     * @param string $mimeType نوع الملف (audio/ogg, audio/mp3, audio/wav, audio/m4a, إلخ)
     * @param string|null $filename اسم الملف المؤقت
     * @return string|null
     */
    public function transcribe(string $audioBytes, string $mimeType = 'audio/ogg', ?string $filename = null): ?string
    {
        if (empty($audioBytes)) {
            return null;
        }

        // 1. تجربة OpenAI Whisper API إذا توفر المفتاح
        $openAiKey = env('OPENAI_API_KEY');
        if (!empty($openAiKey)) {
            try {
                $ext = $this->getExtensionFromMime($mimeType);
                $fn = $filename ?: ('audio_' . time() . '.' . $ext);

                $response = Http::withToken($openAiKey)
                    ->timeout(35)
                    ->attach('file', $audioBytes, $fn)
                    ->post('https://api.openai.com/v1/audio/transcriptions', [
                        'model'       => 'whisper-1',
                        'prompt'      => 'مياه بيرين، طلبات، كراتين، موية، نادية، ماء زمزم، قطاف، توصيل مساجد، لهجة سعودية، لهجة مصرية',
                        'temperature' => 0.0,
                    ]);

                if ($response->successful()) {
                    $text = trim($response->json('text', ''));
                    if (!empty($text)) {
                        Log::info('[AudioTranscription] تفريغ صوتي ناجح عبر OpenAI Whisper', ['text' => $text]);
                        return $text;
                    }
                } else {
                    Log::warning('[AudioTranscription] فشل OpenAI Whisper: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::warning('[AudioTranscription] استثناء Whisper: ' . $e->getMessage());
            }
        }

        // 2. تجربة Google Gemini Multimodal Audio (دعم كامل للهجات العربية واللغات الأجنبية)
        $geminiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        if (!empty($geminiKey)) {
            try {
                // تنظيف نوع الوسائط ليتوافق مع صيغ Gemini المدعومة (audio/ogg, audio/mp3, audio/wav)
                $safeMime = str_contains($mimeType, 'ogg') ? 'audio/ogg' :
                           (str_contains($mimeType, 'mp4') || str_contains($mimeType, 'm4a') ? 'audio/mp4' :
                           (str_contains($mimeType, 'wav') ? 'audio/wav' : 'audio/mp3'));

                $base64Audio = base64_encode($audioBytes);

                $payload = [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'inline_data' => [
                                        'mime_type' => $safeMime,
                                        'data'      => $base64Audio,
                                    ]
                                ],
                                [
                                    'text' => 'Transcribe this audio exactly as spoken. Do not translate. Support Arabic dialects (Saudi, Egyptian, etc.) and any foreign languages. Return only the transcription text, nothing else.'
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.0,
                    ]
                ];

                $response = Http::timeout(30)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$geminiKey}", $payload);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text', '');
                    $clean = trim($text);
                    if (!empty($clean)) {
                        Log::info('[AudioTranscription] تفريغ صوتي ناجح عبر Gemini Audio', ['text' => $clean]);
                        return $clean;
                    }
                } else {
                    // تجربة نموذج فلاش بديل
                    $responseAlt = Http::timeout(30)
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", $payload);
                    if ($responseAlt->successful()) {
                        $clean = trim($responseAlt->json('candidates.0.content.parts.0.text', ''));
                        if (!empty($clean)) {
                            return $clean;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('[AudioTranscription] استثناء Gemini Audio: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * تنزيل وتفريغ مقطع صوتي قادم من رابط Twilio MediaUrl
     */
    public function transcribeTwilioMedia(string $mediaUrl, string $mimeType = 'audio/ogg'): ?string
    {
        try {
            $sid   = config('services.twilio.sid');
            $token = config('services.twilio.token');

            $res = Http::withBasicAuth($sid, $token)
                ->timeout(25)
                ->get($mediaUrl);

            if ($res->successful()) {
                return $this->transcribe($res->body(), $mimeType);
            }

            Log::warning('[AudioTranscription] تعذر تنزيل ملف Twilio الصوتي', ['status' => $res->status()]);
        } catch (\Exception $e) {
            Log::error('[AudioTranscription] استثناء تنزيل Twilio Media: ' . $e->getMessage());
        }

        return null;
    }

    private function getExtensionFromMime(string $mime): string
    {
        return match (true) {
            str_contains($mime, 'ogg') => 'ogg',
            str_contains($mime, 'mp3') || str_contains($mime, 'mpeg') => 'mp3',
            str_contains($mime, 'wav') => 'wav',
            str_contains($mime, 'm4a') || str_contains($mime, 'mp4') => 'm4a',
            str_contains($mime, 'amr') => 'amr',
            default => 'ogg',
        };
    }
}
