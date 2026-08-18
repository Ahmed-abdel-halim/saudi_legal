<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiApiService
{
    protected string $apiKey;
    protected array $defaultModels = [
        'gemini-flash-lite-latest',
        'gemini-3.5-flash-lite',
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash',
        'gemini-3.6-flash',
    ];

    public function __construct()
    {
        $this->apiKey = trim(config('services.gemini.key', env('GEMINI_API_KEY', '')));
    }

    /**
     * إرسال طلب توليد محتوى إلى Gemini مع دعم الاستكشاف التلقائي والتنقل بين النماذج
     *
     * @param array $contents
     * @param array $options ['timeout' => 15, 'temperature' => 0.2, 'topP' => 0.9, 'responseMimeType' => null]
     * @return string|null
     */
    public function generateContent(array $contents, array $options = []): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('[GeminiApiService] API Key is missing');
            return null;
        }

        $timeout = $options['timeout'] ?? 15;
        $models = $this->getModelsToTry();

        // المحاولة 1: تجرب القائمة الحالية (الافتراضية أو المخزنة في المؤقت)
        $result = $this->tryModels($models, $contents, $options, $timeout);
        if ($result !== null) {
            return $result;
        }

        // المحاولة 2 (الاستكشاف التلقائي - Dynamic Auto-Discovery):
        // في حال فشلت القائمة الحالية بالكامل (مثلاً تغيير جوجل لأسماء النماذج)، يتم جلب النماذج الفعالة فوراً من جوجل
        Log::notice('[GeminiApiService] All cached/default models failed. Fetching active models directly from Google API...');
        $freshModels = $this->discoverActiveModelsFromGoogle(true);

        if (!empty($freshModels)) {
            Log::info('[GeminiApiService] Retrying with newly discovered models: ' . implode(', ', $freshModels));
            return $this->tryModels($freshModels, $contents, $options, $timeout);
        }

        return null;
    }

    /**
     * تجربة قائمة نماذج محددة بالترتيب
     */
    protected function tryModels(array $models, array $contents, array $options, int $timeout): ?string
    {
        $generationConfig = array_filter([
            'temperature'      => $options['temperature'] ?? null,
            'topP'             => $options['topP'] ?? null,
            'responseMimeType' => $options['responseMimeType'] ?? null,
        ], fn($v) => $v !== null);

        foreach ($models as $model) {
            try {
                $payload = ['contents' => $contents];
                if (!empty($generationConfig)) {
                    $payload['generationConfig'] = $generationConfig;
                }

                $response = Http::withoutVerifying()
                    ->timeout($timeout)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}", $payload);

                if ($response->successful()) {
                    $parts = $response->json()['candidates'][0]['content']['parts'] ?? [];
                    $textParts = [];
                    foreach ($parts as $part) {
                        if (!empty($part['thought'])) continue;
                        if (isset($part['text'])) $textParts[] = $part['text'];
                    }

                    $text = trim(implode('', $textParts)) ?: trim($parts[0]['text'] ?? '');
                    if (!empty($text)) {
                        return $text;
                    }
                }

                Log::warning("[GeminiApiService] Model {$model} returned HTTP " . $response->status() . " | " . mb_substr($response->body(), 0, 200));
            } catch (\Exception $e) {
                Log::warning("[GeminiApiService] Connection to model {$model} failed: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * جلب قائمة النماذج المستهدفة (من .env، ثم Cache الاستكشاف، ثم الافتراضية)
     */
    public function getModelsToTry(): array
    {
        // 1. إذا كان المبرمج محدد قائمة في .env
        $envModelsStr = trim(config('services.gemini.models', env('GEMINI_MODELS', '')));
        if (!empty($envModelsStr)) {
            return array_map('trim', explode(',', $envModelsStr));
        }

        // 2. إذا وجد قائمة تم استكشافها حياً من جوجل وتخزينها بالـ Cache
        $discovered = Cache::get('gemini_discovered_models');
        if (!empty($discovered) && is_array($discovered)) {
            return $discovered;
        }

        // 3. النماذج الافتراضية
        return $this->defaultModels;
    }

    /**
     * استكشاف النماذج المتاحة حياً من Google ListModels API
     */
    public function discoverActiveModelsFromGoogle(bool $forceRefresh = false): array
    {
        if (empty($this->apiKey)) return [];

        if (!$forceRefresh) {
            $cached = Cache::get('gemini_discovered_models');
            if (!empty($cached) && is_array($cached)) {
                return $cached;
            }
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}");

            if ($response->successful()) {
                $models = $response->json()['models'] ?? [];
                $validModels = [];

                foreach ($models as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods)) {
                        $name = str_replace('models/', '', $m['name']);
                        // استبعاد النماذج القديمة والتجريبية الخاصة بنواحي غير النصوص
                        if (!str_contains($name, 'embedding') && !str_contains($name, 'tts') && !str_contains($name, 'computer-use')) {
                            $validModels[] = $name;
                        }
                    }
                }

                if (!empty($validModels)) {
                    // فرز النماذج لتصدير النماذج السريعة flash أولاً
                    usort($validModels, function($a, $b) {
                        $score = fn($str) => (str_contains($str, 'lite') ? 3 : (str_contains($str, 'flash') ? 2 : 1));
                        return $score($b) <=> $score($a);
                    });

                    // حفظ القائمة بالـ Cache لمدة 24 ساعة
                    Cache::put('gemini_discovered_models', $validModels, now()->addHours(24));
                    Log::info('[GeminiApiService] Dynamic Auto-Discovery successfully saved active models to cache: ' . implode(', ', $validModels));

                    return $validModels;
                }
            }
        } catch (\Exception $e) {
            Log::error('[GeminiApiService] Failed to discover models from Google ListModels API: ' . $e->getMessage());
        }

        return $this->defaultModels;
    }
}
