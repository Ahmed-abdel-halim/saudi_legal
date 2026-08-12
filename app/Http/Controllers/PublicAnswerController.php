<?php

namespace App\Http\Controllers;

use App\Models\PublicLegalAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublicAnswerController extends Controller
{
    /**
     * عرض صفحة السؤال القانوني باللغة العربية
     */
    public function showArabic(string $slug)
    {
        $answer = PublicLegalAnswer::where('slug', $slug)->first();

        if (!$answer) {
            $answer = PublicLegalAnswer::where('counterpart_slug', $slug)->first();
        }

        if (!$answer && preg_match('/(\d+)$/', $slug, $matches)) {
            $answer = PublicLegalAnswer::find($matches[1]);
        }

        if (!$answer) {
            abort(404);
        }

        // زيادة عداد المشاهدات لكل زيارة
        $answer->increment('views_count');

        $arabicCounterpart = $answer;
        // النسخة الإنجليزية الموازية تستخدم نفس الـ slug بمسار /en/legal-qa/{slug}
        $englishCounterpart = (object)[
            'slug' => $answer->slug,
            'locale' => 'en'
        ];

        return view('frontend.answer-detail', compact('answer', 'englishCounterpart', 'arabicCounterpart'));
    }

    /**
     * عرض صفحة السؤال القانوني باللغة الإنجليزية (ترجمة ديناميكية مع Cache)
     */
    public function showEnglish(string $slug)
    {
        // 1. بحث عن السجل الأساسي بالـ slug المباشر، أو البحث بـ counterpart_slug أو بالـ ID إذا كان الـ slug يتضمن رقم السجل
        $arAnswer = PublicLegalAnswer::where('slug', $slug)->first();

        if (!$arAnswer) {
            $arAnswer = PublicLegalAnswer::where('counterpart_slug', $slug)->first();
        }

        if (!$arAnswer && preg_match('/(\d+)$/', $slug, $matches)) {
            $arAnswer = PublicLegalAnswer::find($matches[1]);
        }

        if (!$arAnswer) {
            abort(404);
        }

        // زيادة عداد المشاهدات
        $arAnswer->increment('views_count');

        // 2. استخدام Cache ذكي لمدة 30 يوماً حتى لا يتم طلب Gemini أكثر من مرة لكل سؤال
        $cacheKey = "translated_qa_en_{$arAnswer->id}";
        $translatedData = Cache::remember($cacheKey, now()->addDays(30), function () use ($arAnswer) {
            return $this->translateContentWithGemini($arAnswer->question, $arAnswer->answer);
        });

        // 3. بناء كائن إجابة وهمي مؤقت للعرض باللغة الإنجليزية
        $answer = clone $arAnswer;
        $answer->locale = 'en';
        $answer->question = $translatedData['question'] ?? $arAnswer->question;
        $answer->answer = $translatedData['answer'] ?? $arAnswer->answer;

        $arabicCounterpart = $arAnswer;
        $englishCounterpart = $answer;

        return view('frontend.answer-detail', compact('answer', 'englishCounterpart', 'arabicCounterpart'));
    }

    /**
     * ترجمة السؤال والإجابة ديناميكياً بـ Gemini AI
     */
    private function translateContentWithGemini(string $question, string $answer): array
    {
        $apiKey = trim(config('services.gemini.key', env('GEMINI_API_KEY', '')));
        if (empty($apiKey)) {
            Log::warning('[DynamicTranslate] GEMINI_API_KEY is empty');
            return ['question' => $question, 'answer' => $answer];
        }

        $prompt = <<<PROMPT
You are a professional legal translator specializing in Saudi Arabian law.
Translate the following question and answer from Arabic to English using accurate Saudi legal terms.
Return ONLY a valid JSON object with keys "question" and "answer".

Question: {$question}
Answer: {$answer}
PROMPT;

        $models = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-1.5-flash'];

        foreach ($models as $model) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(12)
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                        [
                            'contents' => [[
                                'role' => 'user',
                                'parts' => [['text' => $prompt]],
                            ]],
                            'generationConfig' => [
                                'temperature' => 0.1,
                                'responseMimeType' => 'application/json',
                            ],
                        ]
                    );

                if ($response->successful()) {
                    $parts = $response->json()['candidates'][0]['content']['parts'] ?? [];
                    $textParts = [];
                    foreach ($parts as $part) {
                        if (!empty($part['thought'])) continue;
                        if (isset($part['text'])) $textParts[] = $part['text'];
                    }
                    $rawText = trim(implode('', $textParts)) ?: trim($parts[0]['text'] ?? '');
                    $rawText = preg_replace('/^```json\s*/i', '', $rawText);
                    $rawText = preg_replace('/```\s*$/i', '', $rawText);

                    $decoded = json_decode($rawText, true);
                    if (is_array($decoded) && !empty($decoded['question']) && !empty($decoded['answer'])) {
                        return $decoded;
                    }
                } else {
                    Log::warning("[DynamicTranslate] Gemini {$model} status: " . $response->status() . " | " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("[DynamicTranslate] Gemini {$model} Exception: " . $e->getMessage());
            }
        }

        return ['question' => $question, 'answer' => $answer];
    }
}

