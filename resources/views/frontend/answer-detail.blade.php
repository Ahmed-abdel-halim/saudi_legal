@extends('layouts.app')

@php
    $isArabic = $answer->locale === 'ar';
    $dir      = $isArabic ? 'rtl' : 'ltr';
    $lang     = $answer->locale;
    $metaDesc = mb_substr(strip_tags($answer->answer), 0, 160);

    // معالجة آمنة لـ citations سواء كانت array أو json string أو null
    $rawCitations = is_array($answer->citations)
        ? $answer->citations
        : (is_string($answer->citations) ? (json_decode($answer->citations, true) ?? []) : []);
    $rawCitations = is_array($rawCitations) ? $rawCitations : [];

    $pageUrl       = request()->url();
    $publishedDate = $answer->created_at ? $answer->created_at->toIso8601String() : now()->toIso8601String();
    $plainQuestion = trim(strip_tags($answer->question));
    $rawAnswer     = trim($answer->answer);
    $plainAnswer   = trim(strip_tags($rawAnswer));

    // ── 1. تطبيق هيكل "الإجابة أولاً" (Answer-First Formatting) ──
    // فصل الفقرة الأولى (الخلاصة الفورية المباشرة) عن بقية الحيثيات
    $immediateSummary = '';
    $detailedContent = '';

    if (preg_match('/###\s*[^\n]*خلاصة[^\n]*\n*(.+?)(?=\n+\s*---\s*\n+|\n+###|\z)/us', $rawAnswer, $summaryMatch)) {
        $immediateSummary = trim($summaryMatch[1]);
        $detailedContent = trim(str_replace($summaryMatch[0], '', $rawAnswer));
        $detailedContent = preg_replace('/^\s*---\s*/u', '', $detailedContent);
    } elseif (preg_match('/^(###\s*[^\n]+\n+)(.+?)(?=\n+\s*---\s*\n+|\n+###|\z)/us', $rawAnswer, $summaryMatch)) {
        $immediateSummary = trim($summaryMatch[2]);
        $detailedContent = trim(str_replace($summaryMatch[0], '', $rawAnswer));
        $detailedContent = preg_replace('/^\s*---\s*/u', '', $detailedContent);
    } else {
        $paragraphs = preg_split('/\n{2,}/u', $rawAnswer);
        if (count($paragraphs) > 1) {
            if (preg_match('/^\*\*.*?\*\*\s*$/u', trim($paragraphs[0])) && isset($paragraphs[1])) {
                $immediateSummary = trim($paragraphs[0]) . "\n\n" . trim($paragraphs[1]);
                $detailedContent = trim(implode("\n\n", array_slice($paragraphs, 2)));
            } else {
                $immediateSummary = trim($paragraphs[0]);
                $detailedContent = trim(implode("\n\n", array_slice($paragraphs, 1)));
            }
        } else {
            $wordsList = preg_split('/\s+/u', $rawAnswer);
            if (count($wordsList) > 40) {
                $immediateSummary = implode(' ', array_slice($wordsList, 0, 35)) . '...';
                $detailedContent = $rawAnswer;
            } else {
                $immediateSummary = $rawAnswer;
                $detailedContent = '';
            }
        }
    }

    $cleanImmediateText = trim(strip_tags($immediateSummary));

    // ── 2. كشف النظام السعودي الأساسي والتصنيف الدلالي المتقدم ──
    $detectedLaw = null;
    try {
        $detectedLaw = app(\App\Services\Legal\LegalQueryExpansionService::class)->detectTargetLawSystem($plainQuestion);
    } catch (\Throwable $e) {
        $detectedLaw = null;
    }

    if (!$detectedLaw) {
        $normText = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $plainQuestion . ' ' . $cleanImmediateText);
        $normText = preg_replace('/[أإآٱ]/u', 'ا', $normText);
        $normText = str_replace(['ة', 'ى'], ['ه', 'ي'], $normText);
        $normText = mb_strtolower($normText);

        if (preg_match('/(محاكم تجاريه|محكمه تجاريه|اختصاص المحاكم التجاريه|اختصاص تجاري|دعوى تجاريه|دعاوى تجاريه|عمل تجاري|اعمال تجاريه|عقود تجاريه|عقد تجاري|محرر عادي|تقادم تجاري|سماع الدعوى|افلاس|اتعاب محاماه|توريد عماله|تجاريه|تجاري)/u', $normText) && !preg_match('/(تستر|تستر تجاري)/u', $normText)) {
            $detectedLaw = 'نظام المحاكم التجارية';
        } elseif (preg_match('/(شركات|شركه مساهمه|شركه ذات مسؤوليه|مدير الشركه|تصفيه شركه|مجلس اداره|شريك|شركاء)/u', $normText)) {
            $detectedLaw = 'نظام الشركات';
        } elseif (preg_match('/(عقد عمل|علاقه عمل|صاحب العمل|مكتب العمل|محكمه عماليه|فصل تعسفي|طردوني|فصلوني|مكافاه نهايه الخدمه|حمايه الاجور|تاخير رواتب|راتبي|بدل سكن|اوفر تايم|ساعات عمل|تغيب عن العمل|بلاغ هروب|كفيلي|اصابه عمل)/u', $normText)) {
            $detectedLaw = 'نظام العمل';
        } elseif (preg_match('/(معاملات مدنيه|مدني|عربون|شرط جزائي|بيع ملك الغير|عقد بيع|شفعه|حواله حق|مسؤوليه تقصيريه|فسخ عقد|انفساخ|عقد مقاوله|عقد ايجار|مقاول)/u', $normText)) {
            $detectedLaw = 'نظام المعاملات المدنية';
        } elseif (preg_match('/(احوال شخصيه|طلاق|خلع|حضانه|نفقه|فسخ نكاح|عضل|ارث|تركه|زوجه|زوج|نكاح|زواج|مهر)/u', $normText)) {
            $detectedLaw = 'نظام الأحوال الشخصية';
        } elseif (preg_match('/(تنفيذ|سند تنفيذي|سند لامر|شيك|كمبياله|ايقاف خدمات|قاضي التنفيذ|حجز اموال)/u', $normText)) {
            $detectedLaw = 'نظام التنفيذ';
        } elseif (preg_match('/(اثبات|شهاده|يمين حاسمه|محرر رسمي|حجيه الاثبات|بينه|شهود)/u', $normText)) {
            $detectedLaw = 'نظام الإثبات';
        } elseif (preg_match('/(مرافعات شرعيه|اختصاص ولائي|اختصاص نوعي|التماس اعاده نظر)/u', $normText)) {
            $detectedLaw = 'نظام المرافعات الشرعية';
        } elseif (preg_match('/(ديوان المظالم|قرار اداري|الغاء قرار|محكمه اداريه)/u', $normText)) {
            $detectedLaw = 'نظام ديوان المظالم';
        } elseif (preg_match('/(تستر|تستر تجاري)/u', $normText)) {
            $detectedLaw = 'نظام مكافحة التستر';
        }
    }

    $primaryLawName = $detectedLaw ?: 'الأنظمة واللوائح السعودية';

    // ── 3. تنظيف وتصحيح المراجع واستبعاد كلمة "مبدأ" كـ Placeholder ──
    $cleanedCitations = [];
    $highlightedLaws = [];
    $highlightedCases = [];

    foreach ($rawCitations as $cit) {
        if (!is_array($cit)) continue;

        $rawSys = trim($cit['law_system'] ?? '');
        $rawArt = trim($cit['article_number'] ?? '');
        $citText = trim($cit['text'] ?? '');
        $caseRef = trim($cit['case_reference'] ?? ($cit['case_number'] ?? ''));

        // معالجة اسم النظام (إذا كان "مبدأ قضائي" نستبدله بالنظام الحقيقي)
        $cleanSys = (!empty($rawSys) && $rawSys !== 'مبدأ قضائي' && $rawSys !== 'null' && $rawSys !== 'غير محدد')
            ? $rawSys
            : $primaryLawName;

        // معالجة رقم المادة (استبعاد "مبدأ" أو "null" أو النصوص الشكلية نهائياً)
        $cleanArt = null;
        if (!empty($rawArt) && !in_array($rawArt, ['مبدأ', 'null', 'غير محدد', 'عام', '-', 'undefined'])) {
            $cleanArt = $rawArt;
        }

        // استخراج السابقة القضائية / القرار من النص إن وجد
        $precedent = null;
        if (preg_match('/(قرار\s+المحكمة\s+العليا[^\n\r،]+)/u', $citText, $pm)) {
            $precedent = trim($pm[1]);
        } elseif (preg_match('/(حكم\s+محكمة\s+الاستئناف[^\n\r،]+)/u', $citText, $pm)) {
            $precedent = trim($pm[1]);
        } elseif (preg_match('/(مبدأ\s+قضائي[^\n\r:،]+[:]?\s*[^\n\r،]+)/u', $citText, $pm)) {
            $precedent = trim($pm[1]);
        } elseif (!empty($cit['precedent'])) {
            $precedent = trim($cit['precedent']);
        }

        $cleanedCitations[] = [
            'law_system'     => $cleanSys,
            'article_number' => $cleanArt,
            'precedent'      => $precedent,
            'text'           => $citText,
            'case_reference' => $caseRef,
        ];

        // شارات السند في أعلى الصفحة (Answer-First Badges)
        if ($cleanArt) {
            $highlightedLaws[] = $cleanSys . ' (المادة ' . $cleanArt . ')';
        } else {
            $highlightedLaws[] = $cleanSys;
        }

        if ($precedent) {
            $highlightedCases[] = mb_substr($precedent, 0, 45);
        } elseif (!empty($caseRef) && is_numeric($caseRef)) {
            $highlightedCases[] = 'القضية رقم ' . $caseRef;
        }
    }

    if (empty($highlightedLaws)) {
        $highlightedLaws[] = $primaryLawName;
    }

    $highlightedLaws = array_values(array_unique($highlightedLaws));
    $highlightedCases = array_values(array_unique($highlightedCases));
    $citationsList = $cleanedCitations;

    // ── 4. الكيانات الدلالية والكلمات المفتاحية (Topic Entities & Meta Keywords) ──
    $topicEntities = [
        $primaryLawName,
        'الأنظمة السعودية',
        'وزارة العدل السعودية',
        'منصة رديف القانونية',
        'استشارات قانونية موثقة',
    ];
    if (str_contains($primaryLawName, 'المحاكم التجارية')) {
        $topicEntities[] = 'المحكمة التجارية';
        $topicEntities[] = 'اختصاص المحاكم التجارية';
        $topicEntities[] = 'القضاء التجاري السعودي';
    } elseif (str_contains($primaryLawName, 'العمل')) {
        $topicEntities[] = 'المحكمة العمالية';
        $topicEntities[] = 'حقوق العمال في السعودية';
        $topicEntities[] = 'نظام العمل ولائحته التنفيذية';
    } elseif (str_contains($primaryLawName, 'الشركات')) {
        $topicEntities[] = 'نظام الشركات السعودي الجديد';
        $topicEntities[] = 'مسؤولية الشركاء والمديرين';
    } elseif (str_contains($primaryLawName, 'المعاملات المدنية')) {
        $topicEntities[] = 'العقود والالتزامات في القانون السعودي';
        $topicEntities[] = 'التعويض والضمان';
    } elseif (str_contains($primaryLawName, 'الأحوال الشخصية')) {
        $topicEntities[] = 'محكمة الأحوال الشخصية';
        $topicEntities[] = 'الأسرة وحقوق الزوجين';
    } elseif (str_contains($primaryLawName, 'التنفيذ')) {
        $topicEntities[] = 'محكمة التنفيذ';
        $topicEntities[] = 'السندات التنفيذية';
    }
    $topicEntities = array_unique($topicEntities);

    // ── 5. كود الـ Schema Markup المتقدم (QAPage + about + mentions + Text Consistency) ──
    $schemaAnswerText = !empty($cleanImmediateText)
        ? ($cleanImmediateText . "\n\n" . strip_tags($detailedContent))
        : $plainAnswer;

    $cleanQaSchema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'QAPage',
        'mainEntity' => [
            '@type'          => 'Question',
            'name'           => $answer->question,
            'text'           => $plainQuestion,
            'answerCount'    => 1,
            'about'          => [
                '@type'                   => 'Legislation',
                'name'                    => $primaryLawName,
                'legislationType'         => 'Statute',
                'legislationJurisdiction' => [
                    '@type'          => 'AdministrativeArea',
                    'name'           => 'المملكة العربية السعودية',
                    'addressCountry' => 'SA',
                ],
            ],
            'mentions'       => array_map(fn($entity) => [
                '@type' => 'Thing',
                'name'  => $entity,
            ], array_values(array_slice($topicEntities, 0, 4))),
            'acceptedAnswer' => [
                '@type'       => 'Answer',
                'text'        => $schemaAnswerText,
                'dateCreated' => $publishedDate,
                'author'      => [
                    '@type' => 'Organization',
                    'name'  => 'رديف (Radiif)',
                    'url'   => 'https://radiif.com',
                ],
            ],
        ],
    ];
    $cleanQaJsonLd = json_encode($cleanQaSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    // ── 6. LegalService Schema ──
    $legalSchema = json_encode([
        '@context'          => 'https://schema.org',
        '@type'             => 'LegalService',
        'name'              => $isArabic ? 'رديف - المساعد القانوني الذكي' : 'Radiif - AI Legal Assistant',
        'url'               => 'https://radiif.com',
        'areaServed'        => 'SA',
        'availableLanguage' => ['Arabic', 'English'],
        'description'       => $isArabic
            ? 'منصة رديف متخصصة في الأنظمة السعودية، تقدم إجابات موثقة بالمواد والمراجع النظامية.'
            : 'Radiif is an AI legal platform specialized in Saudi Arabian law, providing verified answers with legal references.',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // ── 7. Speakable Schema ──
    $speakableSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'url'      => $pageUrl,
        'speakable' => [
            '@type'       => 'SpeakableSpecification',
            'cssSelector' => ['#qa-quick-answer', 'h1'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@endphp

@push('seo_head')
    {{-- Meta Author + Brand Entity Optimization --}}
    <meta name="author" content="Radiif - رديف">
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="keywords" content="{{ implode(', ', $topicEntities) }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">

    {{-- Open Graph (لمشاركة أفضل وثقة أعلى للذكاء الاصطناعي) --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $answer->question }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:site_name" content="Radiif (رديف) - Saudi Legal AI">
    <meta property="og:locale" content="{{ $answer->locale === 'ar' ? 'ar_SA' : 'en_US' }}">
    <meta property="article:section" content="{{ $primaryLawName }}">
    @foreach($topicEntities as $tag)
    <meta property="article:tag" content="{{ $tag }}">
    @endforeach
    <meta property="article:published_time" content="{{ $answer->created_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $answer->updated_at->toAtomString() }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@radiifcom">
    <meta name="twitter:title" content="{{ $answer->question }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">

    {{-- hreflang --}}
    @if(isset($arabicCounterpart) && $arabicCounterpart && $arabicCounterpart->slug)
        <link rel="alternate" hreflang="ar" href="{{ route('public.qa.ar', $arabicCounterpart->slug) }}" />
    @endif
    @if(isset($englishCounterpart) && $englishCounterpart && $englishCounterpart->slug)
        <link rel="alternate" hreflang="en" href="{{ route('public.qa.en', $englishCounterpart->slug) }}" />
    @endif
    <link rel="alternate" hreflang="x-default" href="{{ $pageUrl }}" />
    <link rel="canonical" href="{{ $pageUrl }}" />

    {{-- JSON-LD Schema (Google Search Console & AI Overviews Standard) --}}
    <script type="application/ld+json">{!! $cleanQaJsonLd !!}</script>
    <script type="application/ld+json">{!! $legalSchema !!}</script>
    <script type="application/ld+json">{!! $speakableSchema !!}</script>
@endpush

@section('content')
<div class="min-h-screen" dir="{{ $dir }}" lang="{{ $lang }}">

    {{-- ══ Hero Section ══ --}}
    <section class="relative pt-28 pb-12 overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 {{ $isArabic ? 'right-0' : 'left-0' }} w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 {{ $isArabic ? 'left-0' : 'right-0' }} w-80 h-80 bg-brand-secondary/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="hover:text-brand-primary transition-colors">
                    {{ $isArabic ? 'الرئيسية' : 'Home' }}
                </a>
                <span class="text-gray-600">/</span>
                <span class="text-gray-300 truncate max-w-xs">
                    {{ mb_substr($answer->question, 0, 50) }}{{ mb_strlen($answer->question) > 50 ? '...' : '' }}
                </span>
            </nav>

            {{-- Badges --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-primary/20 text-brand-primary border border-brand-primary/30">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    {{ $isArabic ? 'إجابة قانونية موثقة' : 'Verified Legal Answer' }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    {{ $isArabic ? 'النظام السعودي' : 'Saudi Law' }}
                </span>

                {{-- Language Toggle --}}
                @if(!$isArabic && isset($arabicCounterpart) && $arabicCounterpart)
                    <a href="{{ route('public.qa.ar', $arabicCounterpart->slug) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-400 border border-amber-500/30 hover:bg-amber-500/30 transition-colors">
                        🇸🇦 العربية
                    </a>
                @elseif($isArabic && isset($englishCounterpart) && $englishCounterpart)
                    <a href="{{ route('public.qa.en', $englishCounterpart->slug) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-400 border border-amber-500/30 hover:bg-amber-500/30 transition-colors">
                        🇺🇸 English
                    </a>
                @endif
            </div>

            {{-- Question Title --}}
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white leading-snug mb-4">
                {{ $answer->question }}
            </h1>

            {{-- Meta --}}
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span>{{ number_format($answer->views_count) }} {{ $isArabic ? 'مشاهدة' : 'views' }}</span>
                <span class="text-gray-700">·</span>
                <span>{{ $answer->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </section>

    {{-- ══ Main Content ══ --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Answer Column --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- ══ Answer Card with Answer-First Formatting ══ --}}
                <div class="relative bg-dark-card border border-dark-border rounded-2xl overflow-hidden shadow-xl">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-dark-border bg-gradient-to-r from-brand-primary/10 to-transparent">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center flex-shrink-0 shadow-glow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                                {{ $isArabic ? 'الإجابة القانونية الموثقة' : 'Verified Legal Answer' }}
                            </p>
                            <p class="text-xs text-brand-primary font-semibold">
                                {{ $isArabic ? 'رديف - المساعد القانوني الذكي' : 'Radiif AI Legal Assistant' }}
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-6 space-y-6">

                        {{-- ══ 1. الخلاصة الفورية المباشرة (Answer-First Formatting) لا تتجاوز 30-40 كلمة موثقة بالأنظمة ══ --}}
                        <div id="qa-quick-answer"
                             class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-brand-primary/10 to-teal-500/10 border-2 border-emerald-500/40 shadow-lg">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-3 pb-3 border-b border-emerald-500/20">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-sm font-bold shadow-glow">
                                        ⚡
                                    </span>
                                    <h2 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">
                                        {{ $isArabic ? 'الخلاصة الفورية المباشرة (Answer-First):' : 'Direct Quick Answer (Answer-First):' }}
                                    </h2>
                                </div>

                                {{-- شارات السند النظامي (اسم النظام ورقم المادة ورقم القضية) في السطر الأول لتعظيم الاقتباس --}}
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($highlightedLaws as $law)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            📜 {{ $law }}
                                        </span>
                                    @endforeach
                                    @foreach($highlightedCases as $case)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            ⚖️ {{ $case }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="text-gray-100 text-sm sm:text-base leading-relaxed font-medium">
                                {!! \Illuminate\Support\Str::markdown($immediateSummary) !!}
                            </div>
                        </div>

                        {{-- ══ 2. التفاصيل والحيثيات النظامية الكاملة ══ --}}
                        @if(!empty($detailedContent))
                        <div class="space-y-3 pt-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                                {{ $isArabic ? 'الحيثيات والتفاصيل النظامية والإجرائية:' : 'Detailed Legal Merits & Procedural Steps:' }}
                            </h3>
                            <div class="text-gray-300 leading-relaxed text-sm space-y-3 prose prose-invert max-w-none prose-headings:text-emerald-400 prose-headings:font-bold prose-headings:text-sm prose-p:my-2 prose-ul:my-2 prose-li:my-1 prose-strong:text-white">
                                {!! \Illuminate\Support\Str::markdown($detailedContent) !!}
                            </div>
                        </div>
                        @endif

                        {{-- Brand Attribution (لربط ميزات AI Overviews ومحركات البحث برئيسية رديف كمصدر رسمي) --}}
                        <div class="mt-5 pt-4 border-t border-dark-border flex items-center gap-2">
                            <span class="text-xs text-gray-500">
                                {{ $isArabic ? 'المصدر:' : 'Source:' }}
                            </span>
                            <a href="https://radiif.com" class="text-xs text-brand-primary hover:underline font-semibold">
                                Radiif.com (رديف)
                            </a>
                            <span class="text-gray-600 text-xs">·</span>
                            <span class="text-xs text-gray-500">
                                {{ $isArabic ? 'منصة ذكاء اصطناعي للأنظمة السعودية' : 'Saudi Legal AI Platform' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Citations --}}
                @if(count($citationsList) > 0)
                <div class="bg-dark-card border border-dark-border rounded-2xl overflow-hidden shadow-lg">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-dark-border">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white">
                            {{ $isArabic ? 'المراجع والمواد النظامية' : 'Legal References & Citations' }}
                        </h2>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        @foreach($citationsList as $citation)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/20">
                            <span class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xs font-bold">
                                {{ $loop->iteration }}
                            </span>
                            <div class="text-sm text-gray-300 leading-relaxed">
                                @if(is_array($citation))
                                    @isset($citation['law_system'])
                                        <span class="font-semibold text-emerald-400">{{ $citation['law_system'] }}</span>
                                    @endisset
                                    @if(!empty($citation['article_number']) && !in_array($citation['article_number'], ['مبدأ', 'null', 'غير محدد', 'عام', '-']))
                                        <span class="text-gray-400"> - {{ $isArabic ? 'المادة' : 'Article' }} <span class="font-semibold text-white">{{ $citation['article_number'] }}</span></span>
                                    @endif
                                    @if(!empty($citation['precedent']))
                                        <div class="mt-1.5 flex items-center gap-1.5 text-xs text-amber-300 font-medium">
                                            <span>⚖️</span>
                                            <span>{{ $citation['precedent'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($citation['case_reference']) && is_numeric($citation['case_reference']))
                                        <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-400/90 font-medium">
                                            <span>🏛️</span>
                                            <span>{{ $isArabic ? 'القضية رقم' : 'Case Ref' }} {{ $citation['case_reference'] }}</span>
                                        </div>
                                    @endif
                                    @isset($citation['text'])
                                        <p class="mt-2 text-gray-400 text-xs leading-relaxed">{{ \Illuminate\Support\Str::limit($citation['text'], 250) }}</p>
                                    @endisset
                                @else
                                    {{ $citation }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Share Buttons --}}
                <div class="bg-dark-card border border-dark-border rounded-2xl px-6 py-4">
                    <p class="text-sm text-gray-400 mb-3">{{ $isArabic ? 'شارك هذه الإجابة:' : 'Share this answer:' }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($answer->question) }}&url={{ urlencode(request()->url()) }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sky-500/10 border border-sky-500/30 text-sky-400 text-sm hover:bg-sky-500/20 transition-colors">
                            X (Twitter)
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($answer->question . ' - ' . request()->url()) }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm hover:bg-green-500/20 transition-colors">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">

                {{-- About Radiif --}}
                <div class="bg-dark-card border border-dark-border rounded-2xl p-5">
                    <h3 class="text-sm font-bold text-white mb-3">
                        {{ $isArabic ? 'عن رديف' : 'About Radiif' }}
                    </h3>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        {{ $isArabic
                            ? 'رديف منصة ذكاء اصطناعي قانوني متخصصة في الأنظمة السعودية. تقدم إجابات موثقة بالمواد والمراجع النظامية.'
                            : 'Radiif is an AI legal platform specialized in Saudi Arabian law, providing answers verified by legal references and articles.'
                        }}
                    </p>
                </div>

                {{-- Disclaimer --}}
                <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs text-amber-300/80 leading-relaxed">
                            {{ $isArabic
                                ? 'هذه الإجابة للتثقيف القانوني العام وليست استشارة قانونية. يرجى التواصل مع محامٍ معتمد لقضيتك.'
                                : 'This answer is for general legal education and is not legal advice. Please consult a licensed attorney for your specific case.'
                            }}
                        </p>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="bg-dark-card border border-dark-border rounded-2xl p-5">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">
                        {{ $isArabic ? 'إحصائيات الصفحة' : 'Page Stats' }}
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ $isArabic ? 'المشاهدات' : 'Views' }}</span>
                            <span class="text-sm font-bold text-white">{{ number_format($answer->views_count) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ $isArabic ? 'المراجع' : 'Citations' }}</span>
                            <span class="text-sm font-bold text-white">{{ count($citationsList) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ $isArabic ? 'اللغة' : 'Language' }}</span>
                            <span class="text-sm font-bold text-white">{{ $isArabic ? 'العربية 🇸🇦' : 'English 🇺🇸' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ 3. Conversion Funnel CTA Box (صندوق قمع التحويل التفاعلي لزوار محركات البحث) ══ --}}
        <div class="mt-12 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950/70 via-dark-card to-brand-primary/20 border-2 border-emerald-500/40 p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-brand-primary/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
                    <div class="flex items-center gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-glow flex-shrink-0 text-xl font-bold">
                            🤖
                        </span>
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-white leading-snug">
                                {{ $isArabic ? 'هل لديك تفاصيل إضافية تخص قضيتك؟ اسأل مساعد رديف الذكي الآن...' : 'Have additional details about your case? Ask Radiif AI Legal Assistant now...' }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-300 mt-1">
                                {{ $isArabic ? 'اطرح سؤالك أو وقائع نزاعك لتحصل فوراً على استشارة ذكية مدعومة بالمواد النظامية والسوابق القضائية المماثلة.' : 'Enter your case facts or query to get an instant AI analysis verified with Saudi law articles.' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Interactive Search / Chat Box --}}
                <form action="{{ route('legal_assistant.public') }}" method="GET" class="mt-4">
                    <div class="relative flex flex-col sm:flex-row items-stretch gap-2.5 bg-dark-bg/90 border border-emerald-500/40 rounded-2xl p-2 shadow-inner focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                        <input type="text"
                               name="q"
                               placeholder="{{ $isArabic ? 'اكتب وقائع قضيتك هنا وسيقوم المساعد القانوني بدراستها فوراً...' : 'Type your specific case details here to ask Radiif AI...' }}"
                               class="w-full bg-transparent border-none text-white text-sm sm:text-base px-4 py-3 placeholder-gray-400 focus:outline-none focus:ring-0 text-right font-medium"
                               dir="{{ $dir }}"
                               required>
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-sm shadow-green-glow hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer flex-shrink-0">
                            <span>{{ $isArabic ? 'اسأل مساعد رديف الذكي الآن' : 'Ask Radiif AI Now' }}</span>
                            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Quick Suggestion Pills --}}
                <div class="mt-3.5 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                    <span class="text-gray-500 font-semibold">{{ $isArabic ? 'استفسارات شائعة:' : 'Popular queries:' }}</span>
                    <a href="{{ route('legal_assistant.public', ['q' => 'ما هي إجراءات فسخ العقد لعدم الالتزام بالسداد؟']) }}"
                       class="px-3 py-1 rounded-lg bg-white/5 hover:bg-emerald-500/15 hover:text-emerald-300 border border-white/10 transition-colors">
                        {{ $isArabic ? 'فسخ عقد لعدم السداد' : 'Contract Termination' }}
                    </a>
                    <a href="{{ route('legal_assistant.public', ['q' => 'كيف أطالب بأتعاب المحاماة والتعويض عن المماطلة؟']) }}"
                       class="px-3 py-1 rounded-lg bg-white/5 hover:bg-emerald-500/15 hover:text-emerald-300 border border-white/10 transition-colors">
                        {{ $isArabic ? 'أتعاب المحاماة والمماطلة' : 'Attorney Fees & Delay' }}
                    </a>
                    <a href="{{ route('legal_assistant.public', ['q' => 'ما هي شروط وحجية السند لأمر في محكمة التنفيذ؟']) }}"
                       class="px-3 py-1 rounded-lg bg-white/5 hover:bg-emerald-500/15 hover:text-emerald-300 border border-white/10 transition-colors">
                        {{ $isArabic ? 'حجية السند لأمر' : 'Promissory Note Rules' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- ══ Accredited Lawyer Consultation Section ══ --}}
        <div class="mt-8 relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-950/40 via-dark-card to-emerald-950/30 border border-emerald-500/30 p-8 shadow-xl">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-center {{ $isArabic ? 'md:text-right' : 'md:text-left' }}">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ $isArabic ? 'استشارة قانونية مباشرة' : 'Direct Legal Consultation' }}
                    </div>
                    <h3 class="text-xl font-bold text-white">
                        {{ $isArabic ? 'هل تريد استشارة محامي معتمد في هذه الحالة؟' : 'Do you want to consult a certified lawyer for this case?' }}
                    </h3>
                    <p class="text-gray-300 text-sm max-w-xl leading-relaxed">
                        {{ $isArabic
                            ? 'يمكننا اقتراح قائمة محامين معتمدين لديهم خبرات واسعة في هذا المجال تحديداً.'
                            : 'We can suggest a list of certified lawyers with extensive expertise specifically in this domain.'
                        }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('public.accredited_offices') }}"
                       class="inline-flex items-center gap-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3.5 rounded-xl shadow-green-glow hover:shadow-lg transition-all duration-200 text-sm">
                        <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0V5"/>
                        </svg>
                        {{ $isArabic ? 'قائمة المكاتب الاستشارية المعتمدة' : 'Accredited Consulting Offices List' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
