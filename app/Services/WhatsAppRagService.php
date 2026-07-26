<?php

namespace App\Services;

use App\Services\Legal\LegalSearchService;
use App\Services\AzureSearchService;
use App\Services\QdrantSearchService;
use App\Services\Legal\LegalReferenceService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppRagService
 * -----------------
 * نفس منطق البحث والذكاء الاصطناعي الموجود في LegalAiController،
 * لكن كخدمة مستقلة يمكن استدعاؤها من واجهة الواتساب.
 */
class WhatsAppRagService
{
    public function __construct(
        protected LegalSearchService    $searchService,
        protected AzureSearchService    $azureService,
        protected QdrantSearchService   $qdrantService,
        protected LegalReferenceService $referenceService
    ) {}

    /**
     * الإجابة على سؤال قانوني بالكامل
     *
     * @param string $question    السؤال الحالي
     * @param array  $history     ['role' => 'user'|'assistant', 'content' => '...']
     * @return array ['answer' => string, 'citations' => array, 'limit_reached' => false]
     */
    public function ask(string $question, array $history = []): array
    {
        set_time_limit(180);

        // 1. إعادة صياغة الاستعلام دلالياً إذا كان هناك تاريخ
        $searchQuery = $question;
        if (!empty($history)) {
            $searchQuery = $this->rewriteQuery($question, $history);
        }

        // 2. استخراج المواد المذكورة مباشرة في الاستعلام
        $queryArticles = $this->referenceService->getMentionedArticles($searchQuery);
        $allArticles   = collect($queryArticles);
        $exactArticles = collect();

        foreach ($queryArticles as $art) {
            $exactArticles->push((object) [
                'id'              => 'article_' . $art->id,
                'question'        => $art->article_title ?? '',
                'correct_answer'  => $art->content ?? '',
                'case_text'       => $art->content ?? '',
                'source_type'     => 'article',
                'case_reference'  => 'مادة رقم ' . ($art->article_number ?? ''),
                'law_system_name' => $art->legislation_title ?? '',
                'relevance_score' => 10.0,
                'reranker_score'  => 10.0,
            ]);
        }

        // 3. كشف هل السؤال طلب مباشر لنص مادة؟
        $isDirectArticleRequest = preg_match(
            '/(نص|ما\s*هي|ما\s*هو|ماذا\s*تقول|ماذا\s*تنص|عرض|اعرض|اذكر|أريد|اريد)\s+(نص\s+)?المادة/u',
            $searchQuery
        ) || preg_match('/^المادة\s+([أ-ي0-9]+)/u', trim($searchQuery))
          || preg_match('/نص\s+المادة/u', $searchQuery);

        // 4. البحث متعدد المستويات
        $searchMethod = 'keyword';
        $contextTasks = collect();

        if ($this->azureService->isEnabled()) {
            $cases       = $isDirectArticleRequest ? collect() : $this->azureService->hybridSearch($searchQuery, 5, ['!source_type' => 'article']);
            $lawArticles = $this->azureService->hybridSearch($searchQuery, 3, ['source_type' => 'article']);
            $contextTasks = $cases->merge($lawArticles);
            if ($contextTasks->isNotEmpty()) {
                $searchMethod = 'azure_vector';
            }
        }

        if ($contextTasks->isEmpty() && $this->qdrantService->isEnabled()) {
            $cases       = $isDirectArticleRequest ? collect() : $this->qdrantService->search($searchQuery, 5, ['!source_type' => 'article']);
            $lawArticles = $this->qdrantService->search($searchQuery, 3, ['source_type' => 'article']);
            $contextTasks = $cases->merge($lawArticles);
            if ($contextTasks->isNotEmpty()) {
                $searchMethod = 'qdrant_vector';
            }
        }

        if ($contextTasks->isEmpty()) {
            $contextTasks = $this->searchService->search($searchQuery, 5);
            $searchMethod = 'keyword';
        }

        // 5. دمج المواد المطابقة بدقة
        if ($exactArticles->isNotEmpty()) {
            $contextTasks = $exactArticles->merge($contextTasks);
        }

        // 6. إزالة التكرار وفرز النتائج
        $contextTasks = $contextTasks->unique(function ($task) {
            if (isset($task->source_type) && $task->source_type === 'article') {
                return 'article_' . ($task->id ?? uniqid());
            }
            $text = trim($task->case_text ?: $task->correct_answer ?: '');
            return $text === '' ? 'empty_' . uniqid() : md5($text);
        });

        $exactArticlesIds = $exactArticles->map(fn($a) => $a->id)->toArray();
        $exactArticlesPart = $contextTasks->filter(fn($t) => isset($t->source_type) && $t->source_type === 'article' && in_array($t->id, $exactArticlesIds));
        $casesPart         = $contextTasks->filter(fn($t) => !isset($t->source_type) || $t->source_type !== 'article');
        $articlesPart      = $contextTasks->filter(fn($t) => isset($t->source_type) && $t->source_type === 'article' && !in_array($t->id, $exactArticlesIds));

        if ($isDirectArticleRequest) {
            $contextTasks = $exactArticlesPart->isNotEmpty() ? $exactArticlesPart : $articlesPart->take(1);
        } else {
            $contextTasks = $exactArticlesPart->merge($casesPart)->merge($articlesPart);
        }

        // 7. بناء نص السياق والـ Citations
        $contextText = '';
        $citations   = [];

        foreach ($contextTasks as $task) {
            $typeLabel  = 'مرجع قانوني';
            $badgeLabel = 'أحكام قضائية';

            if (isset($task->source_type)) {
                if ($task->source_type === 'judgment')     { $typeLabel = 'حكم قضائي';      $badgeLabel = 'أحكام قضائية'; }
                elseif ($task->source_type === 'article')  { $typeLabel = 'مادة نظامية';     $badgeLabel = 'نص نظام'; }
                elseif ($task->source_type === 'consultation') { $typeLabel = 'استشارة سابقة'; $badgeLabel = 'استشارة سابقة'; }
            }

            $ref = isset($task->source_type) && $task->source_type === 'article'
                ? ($task->question . (!empty($task->law_system_name) ? ' - ' . $task->law_system_name : ''))
                : ($task->case_reference ?? "مرجع #{$task->id}");

            $textToShow = $task->case_text ?: $task->correct_answer;
            if (!$textToShow || trim($textToShow) === '') continue;

            $contextText .= "--- {$typeLabel} [{$ref}] ---\n";
            $contextText .= "الموضوع: {$task->question}\n";
            $contextText .= "النص: {$textToShow}\n\n";

            $citations[] = [
                'type'   => $task->source_type ?? 'judgment',
                'title'  => $ref,
                'text'   => $textToShow,
                'system' => $task->law_system_name ?? '',
            ];
        }

        // 8. إضافة إحصائيات النظام إن طُلبت
        $statsSummary = $this->referenceService->getSystemStatsSummary($searchQuery);
        if ($statsSummary) {
            $contextText .= "--- إحصائيات النظام ---\n{$statsSummary}\n\n";
        }

        // 9. بناء الـ Prompt
        $articleOnlyRule = '';
        if ($isDirectArticleRequest) {
            $articleOnlyRule = "- طلب نص مادة مباشرة: اعرض اسم النظام ورقم المادة متبوعاً بنص المادة المباشر فقط.\n";
        }

        $prompt = "أنت المستشار القانوني الذكي لمنصة 'رديف'. أجب باللغة العربية فقط، بصيغة مباشرة ونهائية للمستخدم.

قواعد عمل صارمة:
- لا تكتب أفكارك الداخلية أو تفكيرك بصوت عالٍ (THOUGHT: ممنوع).
{$articleOnlyRule}- إذا كان السؤال خارج النطاق القانوني السعودي، أجب بلطف بجملة واحدة أنك متخصص بالأنظمة السعودية فقط.
- لا تستشهد بمراجع غير موجودة في السياق المتاح.

هيكلية الإجابة للأسئلة الاستشارية:
- الرأي القانوني: إجابة مباشرة ومختصرة.
- الاستدلال: الأسانيد من السياق (أرقام قضايا ومواد).
- التحليل: ربط المبادئ بالواقعة.

السياق المتاح:
{$contextText}

سؤال المستخدم: {$question}";

        // 10. بناء تاريخ المحادثة لـ Gemini
        $contents = [];
        foreach ($history as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $prompt]]];

        // 11. استدعاء الذكاء الاصطناعي
        $answer = $this->callGemini($contents);
        $answer = $this->cleanResponse($answer);

        // 12. تنقية المراجع من التكرار
        $uniqueCitations = collect($citations)->unique(fn($c) => md5($c['title'] . $c['system']))->values()->toArray();

        return [
            'answer'    => $answer,
            'citations' => $uniqueCitations,
        ];
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    private function rewriteQuery(string $question, array $history): string
    {
        $geminiKey = trim(config('services.gemini.key'));
        if (empty($geminiKey)) return $question;

        $conversationText = '';
        foreach (array_slice($history, -6) as $msg) {
            $role = $msg['role'] === 'user' ? 'العميل' : 'المساعد';
            $conversationText .= "{$role}: {$msg['content']}\n";
        }

        $prompt = "بناءً على المحادثة:\n{$conversationText}\nالسؤال الجديد: '{$question}'\nأعد صياغته كعبارة بحث قانونية مستقلة. أرجع العبارة فقط بدون شرح.";

        try {
            $response = Http::withoutVerifying()->timeout(12)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$geminiKey}",
                ['contents' => [['parts' => [['text' => $prompt]]]]]
            );

            if ($response->successful()) {
                $rewritten = trim($response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '');
                if (!empty($rewritten)) return $rewritten;
            }
        } catch (\Exception $e) {
            Log::warning('[WhatsAppRag] Query rewrite failed: ' . $e->getMessage());
        }

        return $question;
    }

    private function callGemini(array $contents): string
    {
        $apiKey = trim(config('services.gemini.key'));
        if (empty($apiKey)) return 'مرحباً، يرجى تفعيل GEMINI_API_KEY.';

        try {
            $response = Http::withoutVerifying()->timeout(80)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                ['contents' => $contents]
            );

            if ($response->successful()) {
                $parts    = $response->json()['candidates'][0]['content']['parts'] ?? [];
                $textParts = [];
                foreach ($parts as $part) {
                    if (!empty($part['thought'])) continue;
                    if (isset($part['text'])) $textParts[] = $part['text'];
                }
                return trim(implode('', $textParts)) ?: trim($parts[0]['text'] ?? '');
            }

            Log::error('[WhatsAppRag] Gemini error: ' . $response->status());
            return 'عذراً، حدث خطأ فني. يرجى المحاولة لاحقاً.';

        } catch (\Exception $e) {
            Log::error('[WhatsAppRag] Exception: ' . $e->getMessage());
            return 'خطأ في الاتصال بالمحرك الذكي.';
        }
    }

    private function cleanResponse(string $answer): string
    {
        if (empty($answer)) return $answer;

        // إزالة وسوم التفكير
        $answer = preg_replace('/<thought>[\s\S]*?<\/thought>/iu', '', $answer);

        if (preg_match('/^(THOUGHT|Reasoning):/i', trim($answer))) {
            $cleaned = preg_replace('/^(THOUGHT|Reasoning):[\s\S]*?(?=(\r?\n\r?\n[أ-ي]|\r?\n[أ-ي]|\bالرأي القانوني\b|$))/u', '', $answer);
            if (!empty(trim($cleaned))) {
                $answer = trim($cleaned);
            }
        }

        return trim($answer);
    }
}
