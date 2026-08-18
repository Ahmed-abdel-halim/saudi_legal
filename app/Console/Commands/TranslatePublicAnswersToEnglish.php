<?php

namespace App\Console\Commands;

use App\Models\PublicLegalAnswer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * php artisan seo:translate-en
 *
 * يمر على كل سجلات public_legal_answers التي locale = 'en'
 * ولم تُترجَم بعد (question لا تزال بالعربية)، ويترجمها عبر Gemini API.
 *
 * الخيارات:
 *   --limit=500   عدد السجلات في كل تشغيل (افتراضي 500)
 *   --delay=2     ثواني انتظار بين كل batch (افتراضي 2)
 *   --batch=10    حجم الـ batch لكل استدعاء Gemini (افتراضي 10)
 *   --dry-run     معاينة بدون حفظ
 */
class TranslatePublicAnswersToEnglish extends Command
{
    protected $signature = 'seo:translate-en
                            {--limit=75000 : أقصى عدد سجلات تُترجَم في التشغيل الواحد}
                            {--delay=0     : ثواني انتظار بين كل batch}
                            {--batch=50    : عدد السجلات في كل استدعاء Gemini}
                            {--dry-run     : معاينة بدون حفظ فعلي}';

    protected $description = 'ترجمة الأسئلة والإجابات القانونية من العربية إلى الإنجليزية باستخدام Gemini AI';

    private string $apiKey;
    private bool   $dryRun;

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $this->apiKey = trim(config('services.gemini.key', env('GEMINI_API_KEY', '')));
        $this->dryRun = (bool) $this->option('dry-run');

        if (empty($this->apiKey)) {
            $this->error('❌ GEMINI_API_KEY غير مضبوط في ملف .env');
            return self::FAILURE;
        }

        $limit     = (int) $this->option('limit');
        $delay     = (int) $this->option('delay');
        $batchSize = (int) $this->option('batch');

        if ($this->dryRun) {
            $this->warn('🔍 وضع المعاينة (dry-run) — لن يُحفظ شيء');
        }

        // نجلب السجلات الإنجليزية التي لم تُترجم بعد
        $query = PublicLegalAnswer::where('locale', 'en')
            ->whereNull('translated_at')
            ->orderBy('id')
            ->limit($limit);

        $total = $query->count();

        $this->info("🌍 إجمالي السجلات المراد ترجمتها: {$total}");

        if ($total === 0) {
            $this->info('✅ لا توجد سجلات تحتاج ترجمة.');
            return self::SUCCESS;
        }

        $translated = 0;
        $failed     = 0;

        $query->chunk($batchSize, function ($records) use (&$translated, &$failed, $delay, $batchSize) {
            $items = $records->map(fn($r) => [
                'id'       => $r->id,
                'question' => $r->question,
                'answer'   => $r->answer,
            ])->values()->all();

            $this->line("  📤 إرسال batch ({$records->count()} سجل) إلى Gemini...");

            $translations = $this->translateBatch($items);

            foreach ($records as $record) {
                $t = $translations[$record->id] ?? null;

                if (!$t || empty($t['question']) || empty($t['answer'])) {
                    $this->warn("  ⚠️  فشلت ترجمة السجل #{$record->id}");
                    $failed++;
                    continue;
                }

                if ($this->dryRun) {
                    $this->line("  [DRY] #{$record->id}: " . mb_substr($t['question'], 0, 80));
                } else {
                    $record->update([
                        'question'      => $t['question'],
                        'answer'        => $t['answer'],
                        'translated_at' => now(),
                    ]);
                    $this->line("  ✅ #{$record->id}: " . mb_substr($t['question'], 0, 80));
                }

                $translated++;
            }

            if ($delay > 0) {
                sleep($delay);
            }
        });

        $this->info("🎉 تمت الترجمة! نجح: {$translated} | فشل: {$failed}");

        if (!$this->dryRun && $translated > 0) {
            $this->line('👉 شغّل الآن: php artisan sitemap:legal-qa لتحديث الـ Sitemap');
        }

        return self::SUCCESS;
    }

    /**
     * ترجمة دفعة من السجلات دفعةً واحدة عبر Gemini
     * يُرجع مصفوفة [id => ['question' => '...', 'answer' => '...']]
     */
    private function translateBatch(array $items): array
    {
        // بناء prompt يطلب من Gemini ترجمة كل السجلات كـ JSON
        $inputJson = json_encode($items, JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
You are a professional legal translator specializing in Saudi Arabian law.
Translate the following JSON array from Arabic to English.
Each item has: id, question, answer.
Translate ONLY the "question" and "answer" fields. Keep the "id" unchanged.
The translations must be accurate, formal, and use proper Saudi legal terminology in English.
Return ONLY valid JSON array with the same structure, no extra text.

Input:
{$inputJson}
PROMPT;

        $models = ['gemini-flash-lite-latest', 'gemini-3.5-flash-lite', 'gemini-3.1-flash-lite', 'gemini-3.5-flash'];
        foreach ($models as $model) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(60)
                    ->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}",
                        [
                            'contents' => [[
                                'role'  => 'user',
                                'parts' => [['text' => $prompt]],
                            ]],
                            'generationConfig' => [
                                'temperature'     => 0.1,
                                'topP'            => 0.9,
                                'responseMimeType' => 'application/json',
                            ],
                        ]
                    );

                if ($response->successful()) {
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("[TranslateEN] Gemini model {$model} failed: " . $e->getMessage());
            }
        }

        if (empty($response) || !$response->successful()) {
            Log::error('[TranslateEN] Gemini HTTP error: ' . ($response ? $response->status() . ' ' . $response->body() : 'No response'));
            return [];
        }

            // استخراج النص من استجابة Gemini
            $parts     = $response->json()['candidates'][0]['content']['parts'] ?? [];
            $textParts = [];
            foreach ($parts as $part) {
                if (!empty($part['thought'])) continue;
                if (isset($part['text'])) $textParts[] = $part['text'];
            }
            $rawText = trim(implode('', $textParts));

            // تنظيف الـ JSON (أحياناً Gemini يُغلّفه بـ ```json ... ```)
            $rawText = preg_replace('/^```json\s*/i', '', $rawText);
            $rawText = preg_replace('/```\s*$/i', '', $rawText);

            $decoded = json_decode($rawText, true);

            if (!is_array($decoded)) {
                Log::error('[TranslateEN] Gemini returned invalid JSON: ' . mb_substr($rawText, 0, 500));
                return [];
            }

            // بناء map [id => translation]
            $result = [];
            foreach ($decoded as $item) {
                if (isset($item['id'])) {
                    $result[$item['id']] = [
                        'question' => $item['question'] ?? '',
                        'answer'   => $item['answer']   ?? '',
                    ];
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('[TranslateEN] Exception: ' . $e->getMessage());
            return [];
        }
    }
}
