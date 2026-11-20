<?php

namespace App\Services;

use App\Services\Legal\LegalSearchService;
use App\Services\Legal\LegalQueryExpansionService;
use App\Services\AzureSearchService;
use App\Services\QdrantSearchService;
use App\Services\Legal\LegalReferenceService;
use App\Models\WhatsAppConversation;
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
        protected LegalSearchService          $searchService,
        protected AzureSearchService          $azureService,
        protected QdrantSearchService         $qdrantService,
        protected LegalReferenceService       $referenceService,
        protected GeminiApiService            $geminiService,
        protected BedrockApiService           $bedrockService,
        protected LegalQueryExpansionService  $expansionService
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

        // 0. كشف الخيارات الرئيسية والتحية المجردة للرد المباشر دون بحث في قاعدة البيانات
        $normalized = mb_strtolower(trim($question));

        if ($normalized === '1' || in_array($normalized, ['الاطلاع على الباقات', 'الاطلاع على الباقات 💳', 'الباقات', 'باقات', 'الاسعار', 'الأسعار', 'عرض الباقات'], true)) {
            $appUrl = config('app.url', 'https://radiif.com');
            return [
                'answer'      => "💳 *باقات منصة رديف للذكاء الاصطناعي:*\n\nيمكنك الاطلاع على كافة الباقات والمميزات والأسعار المتاحة عبر الرابط التالي:\n🔗 {$appUrl}/plans\n\nيرجى الاختيار من الخيارات التالية:\n1️⃣ تجربة المساعد القانوني ⚖️\n2️⃣ طلب تنقيح بيانات 📝\n3️⃣ القائمة الرئيسية 🏠",
                'citations'   => [],
                'is_greeting' => true,
            ];
        }

        if ($normalized === '3' || in_array($normalized, ['طلب تنقيح بيانات', 'طلب تنقيح بيانات 📝', 'تنقيح بيانات', 'تنقيح البيانات', 'طلب تنقيح'], true)) {
            return [
                'answer'      => "📝 *طلب تنقيح البيانات:*\n\nنرجو تزويدنا بالمتطلبات والبريد الالكتروني وسيتم إفادتكم بالرد خلال يوم عمل.\n\nيرجى الاختيار من الخيارات التالية:\n1️⃣ تجربة المساعد القانوني ⚖️\n2️⃣ الاطلاع على الباقات 💳\n3️⃣ القائمة الرئيسية 🏠",
                'citations'   => [],
                'is_greeting' => true,
            ];
        }

        if ($normalized === '2' || in_array($normalized, ['تجربة المساعد القانوني', 'تجربة المساعد القانوني ⚖️', 'المساعد القانوني', 'مساعد قانوني', 'تنشيط المحادثة', 'تنشيط المحادثة 🔄', 'ابدأ الاستشارة'], true)) {
            return [
                'answer'      => "🌟 *مرحباً بك في المساعد القانوني لمنصة رديف!*\n\n⚖️ يمكنك طرح أي سؤال قانوني يخص الأنظمة والقضايا السعودية، وسأجيبك بناءً على أحدث المراجع والقضايا القضائية.\n\n📌 *أمثلة على الأسئلة:*\n• على من يقع عبء إثبات التزوير؟\n• ما نص المادة 15 من نظام الشركات؟\n• ما شروط تملك العقار للأجانب في السعودية؟\n\n👇 *يرجى كتابة سؤالك القانوني الآن:*",
                'citations'   => [],
                'is_greeting' => true,
            ];
        }

        if ($this->isPureGreeting($question)) {
            return [
                'answer'       => $this->getGreetingReply($question),
                'citations'    => [],
                'is_greeting' => true,
            ];
        }

        // 1. إعادة صياغة الاستعلام دلالياً إذا كان هناك تاريخ
        $searchQuery = $question;
        if (!empty($history)) {
            $searchQuery = $this->rewriteQuery($question, $history);
        }

        // 1.1. التوسيع الدلالي والاسترجاع الهجين
        $expansion = $this->expansionService->expand($searchQuery);
        $expandedSearchQuery = $expansion['expanded_query'];

        // 2. استخراج المواد المذكورة مباشرة في الاستعلام أو الاستعلام الموسع
        $queryArticles = $this->referenceService->getMentionedArticles($searchQuery);
        if ($queryArticles->isEmpty() && $expandedSearchQuery !== $searchQuery) {
            $queryArticles = $this->referenceService->getMentionedArticles($expandedSearchQuery);
        }
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

        // 4. البحث متعدد المستويات (Hybrid Search with Expansion)
        $searchMethod = 'keyword';
        $contextTasks = collect();

        if ($this->azureService->isEnabled()) {
            $cases       = $isDirectArticleRequest ? collect() : $this->azureService->hybridSearch($expandedSearchQuery, 5, ['!source_type' => 'article']);
            $lawArticles = $this->azureService->hybridSearch($expandedSearchQuery, 3, ['source_type' => 'article']);
            $contextTasks = $cases->merge($lawArticles);
            if ($contextTasks->isNotEmpty()) {
                $searchMethod = 'azure_vector';
            }
        }

        if ($contextTasks->isEmpty() && $this->qdrantService->isEnabled()) {
            $cases       = $isDirectArticleRequest ? collect() : $this->qdrantService->search($expandedSearchQuery, 5, ['!source_type' => 'article']);
            $lawArticles = $this->qdrantService->search($expandedSearchQuery, 3, ['source_type' => 'article']);
            $contextTasks = $cases->merge($lawArticles);
            if ($contextTasks->isNotEmpty()) {
                $searchMethod = 'qdrant_vector';
            }
        }

        if ($contextTasks->isEmpty()) {
            $contextTasks = $this->searchService->search($expandedSearchQuery, 5);
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

        // 9. بناء الـ Prompt الاحترافي عالي الذكاء
        $articleOnlyRule = '';
        if ($isDirectArticleRequest) {
            $articleOnlyRule = "⚠️ (تنبيه حاسم) طلب نص مادة مباشرة: سؤال العميل يطلب نص مادة نظامية محددة. يُمنع منعاً باتاً تقسيم الإجابة ويُمنع إضافة مقدمات أو تحليلات زوائد. اعرض اسم النظام ورقم المادة متبوعاً بنص المادة المباشر والدقيق فقط كما هو وارد في السياق.\n";
        }

        $prompt = "أنت المستشار القانوني الذكي الأول لمنصة 'رديف' والمتخصص الحصري في الأنظمة واللوائح والسوابق القضائية السعودية.

🌍 تعليمات تعدد اللغات واللغة المستهدفة للإجابة (صارمة بنسبة 100%):
- مهما كانت لغة سؤال المستخدم (سواء كانت العربية، الإنجليزية، الفرنسية، الألمانية، الأوردو، أو غيرها)، قم بالآتي:
  1. ابحث واسترجع السوابق والمواد النظامية الدقيقة من قاعدة المعرفة المرفقة بالسياق أدناه.
  2. صُغ الإجابة النهائية مباشرة بنفس لغة سؤال المستخدم تماماً.
  3. يجب الاحتفاظ بأسماء الأنظمة السعودية الرسمية وأرقام المواد والمراجع القضائية بدقة مطلقة.

🎯 نبرة الصوت وشخصية المستشار:
- احترافية، قانونية، واثقة، وأنيقة وموجزة بأسلوب مناسب للقرّاء على منصة الواتساب.

🔒 القواعد الأصولية والمحددات القانونية الصارمة (System Guardrails):
1. **أولوية المواد والأنظمة (Statute-First):**
   - نصوص الأنظمة واللوائح التنفيذية الصريحة هي الأساس والتشريع الإلزامي الأول، وتُعطى الأولوية المطلقة في التأسيس.
   - السوابق والأحكام القضائية تُذكر كتطبيق عملي واستئناس لتأكيد فهم المادة، وفي حال عدم وجود سابقة قضائية مماثلة، أجب مباشرة وبثقة تامة بناءً على نصوص المواد دون ارتباك ولا تذكر 'لا توجد قضايا'.

2. **منع التلوث المعرفي والعزل السياقي (Noise Reduction & Domain Isolation):**
   - الالتزام الحصري بالنظام ولائحته التنفيذية ذات الصلة المباشرة بموضوع السؤال؛ فعند معالجة الدعاوى والاعتراضات التجارية يتم الاعتماد حصراً على (نظام المحاكم التجارية ولائحته التنفيذية، نظام الشركات، نظام المعاملات المدنية)، وفي قضايا العمل على (نظام العمل ولائحته)، وفي الأحوال الشخصية على (نظام الأحوال الشخصية).
   - يُمنع منعاً باتاً استدعاء أو إقحام نصوص من أنظمة غير مطبقة (كالأنظمة الجزائية أو الإدارية أو الأوراق التجارية أو التخصيص) لمجرد تشابه كلمات مفتاحية عامة مثل 'اعتراض'، 'إخطار'، 'تبليغ'، 'مهلة'، 'شطب'. اعزل كل نزاع داخل نظامه الموضوعي والإجرائي المختص.

3. **قاعدة حجية الأمر المقضي به (Res Judicata - حاسمة وإلزامية):**
   - إذا تبين من استعلام المستفيد أو الوقائع وجود حكم قضائي نهائي ومؤيد من محكمة الاستئناف (أو مكتسب القطعية)، يُمنع منعاً باتاً اقتراح رفع أي دعوى موضوعية جديدة حول نفس الحق والسبب والخصوم، ويجب إرشاد المستفيد حصراً إلى المسارين التاليين:
     أ) مسار التنفيذ الجبري لدى محكمة التنفيذ وفقاً لنظام التنفيذ.
     ب) مسار تقديم (التماس إعادة النظر) في حالاته الحصرية والضيقة المنصوص عليها نظاماً (مثل: ظهور أوراق قاطعة تعذر إبرازها، ثبوت غش أو تزوير).

4. **الالتزام بالسياق ومنع الهلوسة والتفكير الداخلي:**
   - لا تذكر مواداً أو أرقام أنظمة أو سوابق وهمية من خارج السياق المرفق أدناه.
   - يُمنع منعاً باتاً إخراج أي تفكير داخلي أو نصوص تبدأ بـ 'THOUGHT:' أو 'Reasoning:' أو استخدام وسم '<thought>'.
   - تحديد نطاق العمل: إذا كان سؤال العميل خارج نطاق الأنظمة والقضايا السعودية أو سؤالاً عاماً غير قانوني، أجب باختصار وبلطف وبفقرة واحدة ودية بنفس لغة العميل موضحاً أنك متخصص حصراً في الأنظمة والقضايا القضائية السعودية.

{$articleOnlyRule}
🏛️ الهيكلية الإلزامية للمخرجات (للاستشارات العامة):
• *📌 الرأي والمشورة القانونية:* خلاصة واضحة ومباشرة تجيب على سؤال العميل بدقة وحسم.
• *⚖️ التحليل والتطبيق النظامي:* ربط الوقائع بالأسانيد والنصوص النظامية والسوابق القضائية.
• *📚 المراجع والنصوص المستشهد بها:*
  - *[اسم النظام]:* المادة ([رقم المادة]) - [ملخص مقتضب لحكم المادة].
  - *[السوابق القضائية المعتمدة (إن وجدت)]:* حكم الاستئناف رقم ([الرقم]) لعام ([السنة])هـ.

سياق المراجع والأنظمة المتاحة حالياً:
{$contextText}

سؤال العميل (أجب فوراً بنفس لغته):
{$question}";

        // 10. استدعاء الذكاء الاصطناعي مع دعم Bedrock كخيار أول لتوفير التكاليف
        $rawAnswer = null;

        if ($this->bedrockService->isEnabled()) {
            $bedrockMessages = [];
            foreach ($history as $msg) {
                $bedrockMessages[] = [
                    'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $msg['content'],
                ];
            }
            $bedrockMessages[] = ['role' => 'user', 'content' => $prompt];
            $rawAnswer = $this->bedrockService->generateContent($bedrockMessages, null, ['temperature' => 0.1]);
        }

        if (empty($rawAnswer)) {
            $contents = [];
            foreach ($history as $msg) {
                $role = $msg['role'] === 'assistant' ? 'model' : 'user';
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $msg['content']]],
                ];
            }
            $contents[] = ['role' => 'user', 'parts' => [['text' => $prompt]]];
            $rawAnswer = $this->callGemini($contents);
        }

        if (empty($rawAnswer) || $rawAnswer === 'FAIL') {
            if (!empty($citations)) {
                $topCitation = $citations[0];
                $title = $topCitation['title'] ?? 'مرجع قضائي';
                $rawText = $topCitation['text'] ?? '';
                $cleanText = preg_replace('/^---.*?---\s*/u', '', $rawText);
                $cleanText = preg_replace('/الحمد لله والصلاة والسلام على رسول ﷲ أما بعد:?\s*/u', '', $cleanText);
                $cleanText = preg_replace('/فلدى الدائرة.*?\((الوقائع|الأسباب)\)\s*/u', '', $cleanText);
                $cleanText = trim($cleanText);

                $textSnippet = mb_substr($cleanText, 0, 450);
                if (mb_strlen($cleanText) > 450) {
                    $textSnippet .= '...';
                }

                $answer = "⚖️ *نتيجة المستندات والأحكام القضائية المستخرجة:*\n\n" .
                          "بناءً على السجلات المتاحة في قاعدة البيانات القضائية لرديف حول موضوع استفسارك:\n\n" .
                          "📌 *المرجع:* {$title}\n" .
                          "📜 *النص المقتبس:* {$textSnippet}";
            } else {
                $answer = "عذراً، المحرك الذكي يواجه ضغطاً مؤقتاً ولم نتمكن من استخراج صياغة تفصيلية حالياً. يرجى كتابة السؤال بصيغة أخرى أو المحاولة لاحقاً.";
            }
        } else {
            $answer = $this->cleanResponse($rawAnswer);
        }

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
        $conversationText = '';
        foreach (array_slice($history, -6) as $msg) {
            $role = $msg['role'] === 'user' ? 'العميل' : 'المساعد';
            $conversationText .= "{$role}: {$msg['content']}\n";
        }

        $prompt = "بناءً على المحادثة السابقة:\n{$conversationText}\nالسؤال الجديد: '{$question}'\nأعد صياغة السؤال كعبارة بحث قانونية دقيقة ومستقلة تبحث في الأنظمة والأحكام السعودية. أرجع عبارة البحث فقط بدون أي مقدمات أو شرح.";

        $rewritten = $this->geminiService->generateContent([['parts' => [['text' => $prompt]]]], ['timeout' => 8, 'temperature' => 0.1]);

        return !empty($rewritten) ? trim($rewritten) : $question;
    }

    private function callGemini(array $contents): ?string
    {
        return $this->geminiService->generateContent($contents, ['timeout' => 15, 'temperature' => 0.2, 'topP' => 0.9]);
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

    private function isPureGreeting(string $text): bool
    {
        $clean = mb_strtolower(trim($text));
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);

        if (empty($clean)) {
            return false;
        }

        $greetingWords = [
            'السلام', 'عليكم', 'ورحمة', 'الله', 'وبركاته', 'سلام', 'سلامات',
            'مرحبا', 'مرحباً', 'مرحبتين', 'مراحيب',
            'اهلا', 'أهلا', 'اهلاً', 'أهلاً', 'اهلين', 'أهلين', 'اهدا', 'وسهلاً', 'وسهلا',
            'صباح', 'مساء', 'الخير', 'النور', 'الورد', 'السرور',
            'كيف', 'حالك', 'الحال', 'الحالية', 'كيفك', 'اخبارك', 'شخبارك', 'علومك', 'شلونك',
            'يعطيك', 'العافية', 'يعطيكوا', 'عافية', 'الله', 'يعافيك',
            'شكرا', 'شكراً', 'مشكور', 'مشكورة', 'تسلم', 'تسلمي', 'جزاك', 'خير', 'خيور', 'جزيل', 'الشكر',
            'يا', 'هلا', 'غالي', 'حياك', 'حياكم',
            'hi', 'hello', 'hey', 'good', 'morning', 'evening', 'afternoon', 'how', 'are', 'you', 'thanks', 'thank'
        ];

        $words = explode(' ', $clean);
        $nonGreetingWords = array_values(array_filter($words, function ($w) use ($greetingWords) {
            return !in_array($w, $greetingWords, true);
        }));

        if (empty($nonGreetingWords)) {
            return true;
        }

        if (count($nonGreetingWords) <= 2) {
            $legalKeywords = [
                'مادة', 'نظام', 'قضية', 'حكم', 'عقد', 'عمل', 'استئناف', 'محكمة', 'عقوبة',
                'تعويض', 'حق', 'تركة', 'طلاق', 'تزوير', 'شركة', 'شركات', 'عقار', 'إثبات',
                'اثبات', 'سؤال', 'استفسار', 'طلب', 'مساعدة', 'نص', 'شرط', 'شروط', 'لوائح',
                'لائحة', 'تنفيذية', 'مبلغ', 'حقوق', 'راتب', 'فصل', 'إنهاء', 'انهاء'
            ];

            $questionMarkers = [
                'ما', 'ماذا', 'هل', 'كيف', 'متى', 'اين', 'أين', 'كم', 'ليه', 'لماذا', 'مين', 'من'
            ];

            foreach ($nonGreetingWords as $w) {
                if (in_array($w, $legalKeywords, true) || in_array($w, $questionMarkers, true)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private function getGreetingReply(string $body): string
    {
        $clean = mb_strtolower(trim($body));

        if (mb_strpos($clean, 'شكرا') !== false || mb_strpos($clean, 'شكراً') !== false || mb_strpos($clean, 'مشكور') !== false || mb_strpos($clean, 'يعطيك') !== false || mb_strpos($clean, 'جزاك') !== false) {
            return "العفو! أهلاً وسهلاً بك في أي وقت. ⚖️ هل لديك أي استفسار قانوني آخر؟";
        }

        return "أهلاً بك في منصة رديف للذكاء الاصطناعي ✨\n\nيرجى الاختيار من الخيارات التالية:\n\n1️⃣ الاطلاع على الباقات 💳\n2️⃣ تجربة المساعد القانوني ⚖️\n3️⃣ طلب تنقيح بيانات 📝\n4️⃣ ديمو مياه بيرين (نادية) 💧";
    }

    /**
     * الإجابة على استفسار ديمو مياه بيرين بشخصية "نادية" (خدمة العملاء الذكية متعددة اللهجات واللغات)
     *
     * @param WhatsAppConversation|array $conversationOrHistory
     * @param string $query
     * @return string
     */
    public function handleBerainDemoQuery($conversationOrHistory, string $query): string
    {
        $jsonPath = storage_path('app/demo/berain_complete_structured_database.json');
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('code_artifact.md');
        }
        $berainData = file_exists($jsonPath) ? file_get_contents($jsonPath) : '{}';

        // استخراج سجل المحادثة
        $history = [];
        if ($conversationOrHistory instanceof WhatsAppConversation) {
            foreach ($conversationOrHistory->getRecentHistory(10) as $msg) {
                $history[] = [
                    'role'    => $msg->role === 'assistant' ? 'assistant' : 'user',
                    'content' => $msg->content,
                ];
            }
        } elseif (is_array($conversationOrHistory)) {
            $history = $conversationOrHistory;
        }

        // استخراج اسم العميل الفعلي المتصل
        $displayName = 'العميل';
        if ($conversationOrHistory instanceof WhatsAppConversation && !empty($conversationOrHistory->display_name)) {
            $displayName = trim($conversationOrHistory->display_name);
        }

        $systemPrompt = <<<EOT
أنتِ "نادية" (Nadia)، موظفة خدمة عملاء حقيقية، ذكية، وودودة جداً في شركة "مياه بيرين" (Berain Water) في المملكة العربية السعودية.
أنتِ تتحدثين الآن عبر الواتساب مع العميل: "{$displayName}".
خاطبي العميل دائماً باسمه الفعلي "{$displayName}" بتقدير ولباقة دافئة (مثلاً: يا هلا والله أستاذ {$displayName}، أو Hello Mr. {$displayName}).

### 1. قواعد الشخصية والأسلوب البشري (صارمة جداً):
- تحدثي كإنسان حقيقي 100% وليس كروبوت. لا تقولي أبداً "أنا مساعد ذكي" أو "بصفتي ذكاء اصطناعي". أنتِ "نادية" من فريق عناية العملاء في بيرين.
- اجعلي ردودك قصيرة، مريحة للعين، شبيهة برسائل البشر على واتساب (سطرين إلى 4 أسطر كحد أقصى إلا إذا طلب العميل قائمة أسعار أو تفاصيل كثيرة)، واستخدمي الإيموجي باعتدال وذوق (💧🤍✨👍).

### 2. التفوق في فهم اللهجات العربية (السعودية + المصرية + كافة اللهجات) واللغات الأجنبية:
- **إذا تحدث العميل باللهجة السعودية أو الخليجية:**
  افهمي كافة مصطلحاته العامية (مثل: "أبي موية أبو نص"، "جيك للبراد"، "موية قزاز"، "سقيا مسجد"، "كم حبة بالكرتون") وردي عليه بلهجة سعودية بيضاء راقية ودافئة ("يا هلا والله أستاذ {$displayName}"، "أبشر بسعدك"، "من عيوني"، "توصلك بالعافية").
- **إذا تحدث العميل باللهجة المصرية:**
  افهمي كافة المصطلحات والتعبيرات المصرية الدارجة فوراً (مثل: "عايز كرتونة مية"، "إزاي أطلب؟"، "قارورة كبيرة للمبرد / كولمن"، "إزيك يا نادية"، "الأوردر اتأخر ليه؟"، "مية معدنية صغيرة"، "بكام الكرتونة؟"، "تبرع لمسجد") وردي عليه بروح ترحيبية دافئة ومألوفة تجمع بين الذوق السعودي واللباقة القريبة للعميل المصري (مثل: "يا أهلاً وسهلاً بحضرتك يا أستاذ {$displayName}! منورنا والله 💧"، "من عيوني حاضر، طلبك هيكون عندك في الميعاد"، "تحت أمر حضرتك").
- **إذا تحدث العميل بأي لهجة عربية أخرى (شامية، مغاربية، سودانية، يمنية) أو بالعربية الفصحى:**
  افهمي مقصده بدقة تامة وردي عليه بلغة عربية بيضاء دافئة ومفهومة وطبيعية جداً.
- **إذا تحدث العميل بأي لغة غير العربية (English, Français, Urdu, Hindi, Tagalog, Español, etc.) أو حتى بالـ Franco-Arab:**
  اكتشفي لغته تلقائياً وردي عليه **بنفس لغته** بطلاقة واحترافية ودفء بشري كأنكِ موظفة خدمة عملاء تتحدث لغته الأم (مثلاً بالإنجليزية: "Hello Mr. {$displayName}! 👋💧 This is Nadia from Berain Customer Care...").

### 3. التفاعل الذكي والاستباقي مع بيانات العميل ({$displayName}):
- لأغراض هذا العرض التوضيحي (Demo Showcase)، العميل الحالي "{$displayName}" هو صاحب الحساب والملف الذهبي في قاعدة بيانات بيرين أدناه:
  - هو عميل ذهبي، ومنتجه المفضل هو "بيرين 600 مل (كرتون 24 عبوة بـ 18.86 ريال)"، ولديه اشتراك كل أسبوعين.
  - آخر طلب له رقمه (#695818066) بقيمة 48.77 ريال، ولديه 58.8 نقطة ولاء.
  - وقت توصيله المفضل في المساء (5 إلى 9 مساءً) مع ترك الطلب عند الباب الخارجي.
  - عناوينه المسجلة: حي الورود في الرياض، أو توصيل المساجد عبر جمعية جوار في مكة المكرمة.
- إذا طلب تكرار طلبه أو استفسر عن رصيده ونقاطه، رحبي به باسمه الفعلي "{$displayName}" وقدمي له تفاصيل طلبه الأخير (#695818066) ونقاطه بدقة.

### 4. التعاطف البشري في الشكاوى والاستفسارات والاعتماد الحصري على الحقائق:
- إذا اشتكى من تأخير أو مشكلة في الطعم أو استرداد مبلغ، أظهري تعاطفاً بشرياً حقيقياً أولاً قبل ذكر الإجراءات.
- استخرجي كافة الأسعار، الأحجام، نسبة الصوديوم (12.0) والـ pH (7.2)، شروط قطاف ومكافأة الراجحي، وتفاصيل عناوين العميل وطلباته الـ 63 ومحفظته من قاعدة البيانات المرفقة أدناه فقط دون أي اختلاق:

<berain_database>
{$berainData}
</berain_database>
EOT;

        $rawAnswer = null;

        // 1. تجربة Bedrock (Claude Sonnet)
        if ($this->bedrockService->isEnabled()) {
            $bedrockMessages = [];
            foreach ($history as $msg) {
                $bedrockMessages[] = [
                    'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $msg['content'],
                ];
            }
            $bedrockMessages[] = ['role' => 'user', 'content' => $query];

            try {
                $rawAnswer = $this->bedrockService->generateContent($bedrockMessages, $systemPrompt, [
                    'temperature' => 0.4,
                    'max_tokens'  => 1024,
                    'timeout'     => 45,
                ]);
            } catch (\Exception $e) {
                Log::warning('[WhatsAppRagService] Bedrock call for Berain failed: ' . $e->getMessage());
            }
        }

        // 2. تجربة Gemini كـ Fallback فائق السرعة
        if (empty($rawAnswer)) {
            $geminiContents = [];
            foreach ($history as $msg) {
                $geminiContents[] = [
                    'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            }

            if (empty($history)) {
                $geminiContents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => "### التعليمات والشخصية:\n{$systemPrompt}\n\n### رسالة العميل:\n{$query}"]],
                ];
            } else {
                $geminiContents[0]['parts'][0]['text'] = "### التعليمات والشخصية:\n{$systemPrompt}\n\n" . $geminiContents[0]['parts'][0]['text'];
                $geminiContents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => $query]],
                ];
            }

            try {
                $rawAnswer = $this->geminiService->generateContent($geminiContents, [
                    'temperature' => 0.4,
                    'timeout'     => 20,
                ]);
            } catch (\Exception $e) {
                Log::error('[WhatsAppRagService] Gemini call for Berain failed: ' . $e->getMessage());
            }
        }

        if (empty($rawAnswer)) {
            return "يا هلا والله أستاذ محمد! 🤍💧 اعتذر منك يبدو أن الشبكة عندي معلقة لثواني. أعد إرسال رسالتك وأنا معك بالخدمة فوراً!";
        }

        return $this->cleanResponse($rawAnswer);
    }

    /**
     * معالجة استفسار المستفيد مع "المفتي الذكي" (الاستشارات والأحكام الشرعية الموثقة)
     */
    public function handleShariaQuery(WhatsAppConversation $conversation, string $query): string
    {
        $systemPrompt = <<<EOT
أنت «المفتي الإسلامي الذكي» التابع لمنصة رديف في المملكة العربية السعودية.
مهمتك تقديم الفتاوى والأحكام الشرعية الموثقة بدقة تامة ورصانة فقهية وورع تام عبر تطبيق الواتساب.

### القواعد الفقهية والأسلوب:
1. استند إلى نصوص القرآن الكريم والسنة النبوية الصحيحة، وأقوال أئمة المذاهب الفقهية المعتبرة، وقرارات الهيئات الشرعية الرسمية (كالرئاسة العامة للبحوث العلمية والإفتاء، وهيئة كبار العلماء بالمملكة، ومجمع الفقه الإسلامي).
2. ابدأ بالبسملة أو الحمد، واذكر الحكم الفقهي المباشر أولاً بوضوح (جائز، محرم، واجب، مستحب، مكروه) مع التفصيل عند اختلاف الحالات.
3. اذكر الدليل الشرعي (آية أو حديث شريف) إن وجد باختصار ووضوح.
4. اجعل الإجابة مركزة، خالية من الحشو، ومريحة للقراءة في شاشات الجوال وواتساب (فقرات قصيرة ونقاط واضحة).
5. تجنب القطع في المسائل الخلافية الكبرى، وانسب الأقوال لأهل العلم بأمانة.
6. اختم دائماً بعبارة: "والله تعالى أعلم وأحكم".
EOT;

        // جلب سجل المحادثة للعميل
        $history = $conversation->getRecentHistory(6)->map(fn($m) => [
            'role'    => $m->role,
            'content' => $m->content,
        ])->toArray();

        $rawAnswer = '';

        // 1. تجربة AWS Bedrock (Claude)
        if ($this->bedrockService->isConfigured()) {
            try {
                $bedrockMessages = [];
                foreach ($history as $msg) {
                    $bedrockMessages[] = [
                        'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                        'content' => $msg['content'],
                    ];
                }
                $bedrockMessages[] = [
                    'role'    => 'user',
                    'content' => $query,
                ];

                $rawAnswer = $this->bedrockService->generateContent($bedrockMessages, $systemPrompt, [
                    'temperature' => 0.3,
                    'max_tokens'  => 1024,
                    'timeout'     => 45,
                ]);
            } catch (\Exception $e) {
                Log::warning('[WhatsAppRagService] Bedrock call for Sharia failed: ' . $e->getMessage());
            }
        }

        // 2. تجربة Gemini كـ Fallback فائق السرعة
        if (empty($rawAnswer)) {
            $geminiContents = [];
            foreach ($history as $msg) {
                $geminiContents[] = [
                    'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            }

            if (empty($history)) {
                $geminiContents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => "### التوجيهات الفقهية:\n{$systemPrompt}\n\n### سؤال المستفتي:\n{$query}"]],
                ];
            } else {
                $geminiContents[0]['parts'][0]['text'] = "### التوجيهات الفقهية:\n{$systemPrompt}\n\n" . $geminiContents[0]['parts'][0]['text'];
                $geminiContents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => $query]],
                ];
            }

            try {
                $rawAnswer = $this->geminiService->generateContent($geminiContents, [
                    'temperature' => 0.3,
                    'timeout'     => 20,
                ]);
            } catch (\Exception $e) {
                Log::error('[WhatsAppRagService] Gemini call for Sharia failed: ' . $e->getMessage());
            }
        }

        if (empty($rawAnswer)) {
            return "الحمد لله والصلاة والسلام على رسول الله..\nنعتذر عن حدوث ضغط مؤقت في الاتصال، يرجى إعادة إرسال مسألتك الشرعية لنفيدك بحكمها المعتمد فوراً.\n\nوالله تعالى أعلم.";
        }

        return $this->cleanResponse($rawAnswer);
    }
}
