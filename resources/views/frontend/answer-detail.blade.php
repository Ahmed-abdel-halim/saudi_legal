@extends('layouts.app')

@php
    $isArabic = $answer->locale === 'ar';
    $dir      = $isArabic ? 'rtl' : 'ltr';
    $lang     = $answer->locale;
    $metaDesc = mb_substr(strip_tags($answer->answer), 0, 160);

    // معالجة آمنة لـ citations سواء كانت array أو json string أو null
    $citationsList = is_array($answer->citations)
        ? $answer->citations
        : (is_string($answer->citations) ? (json_decode($answer->citations, true) ?? []) : []);
    $citationsList = is_array($citationsList) ? $citationsList : [];
@endphp

@php
    // بناء JSON-LD Schema في PHP لتجنب تعارض @context مع Blade
    $faqSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => [[
            '@type' => 'Question',
            'name'  => $answer->question,
            'acceptedAnswer' => [
                '@type'  => 'Answer',
                'text'   => strip_tags($answer->answer),
                'author' => [
                    '@type' => 'Organization',
                    'name'  => $isArabic ? 'رديف (Radiif)' : 'Radiif (رديف)',
                    'url'   => url('/'),
                ],
            ],
        ]],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $legalSchema = json_encode([
        '@context'          => 'https://schema.org',
        '@type'             => 'LegalService',
        'name'              => $isArabic ? 'رديف - المساعد القانوني الذكي' : 'Radiif - AI Legal Assistant',
        'url'               => url('/'),
        'areaServed'        => 'SA',
        'availableLanguage' => ['Arabic', 'English'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@endphp

@push('seo_head')
    <meta name="description" content="{{ $metaDesc }}">

    @if(isset($arabicCounterpart) && $arabicCounterpart && $arabicCounterpart->slug)
        <link rel="alternate" hreflang="ar" href="{{ route('public.qa.ar', $arabicCounterpart->slug) }}" />
    @endif
    @if(isset($englishCounterpart) && $englishCounterpart && $englishCounterpart->slug)
        <link rel="alternate" hreflang="en" href="{{ route('public.qa.en', $englishCounterpart->slug) }}" />
    @endif
    <link rel="alternate" hreflang="x-default" href="{{ request()->url() }}" />
    <link rel="canonical" href="{{ request()->url() }}" />

    <script type="application/ld+json">{!! $faqSchema !!}</script>
    <script type="application/ld+json">{!! $legalSchema !!}</script>
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

                {{-- Answer Card --}}
                <div class="relative bg-dark-card border border-dark-border rounded-2xl overflow-hidden shadow-xl">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-dark-border bg-gradient-to-r from-brand-primary/10 to-transparent">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center flex-shrink-0 shadow-glow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                                {{ $isArabic ? 'الإجابة القانونية' : 'Legal Answer' }}
                            </p>
                            <p class="text-xs text-brand-primary font-semibold">
                                {{ $isArabic ? 'رديف - المساعد القانوني الذكي' : 'Radiif AI Legal Assistant' }}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 py-6">
                        <div class="text-gray-300 leading-relaxed text-sm space-y-3">
                            {!! nl2br(e($answer->answer)) !!}
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
                                    @isset($citation['article_number'])
                                        <span class="text-gray-400"> - {{ $isArabic ? 'المادة' : 'Article' }} <span class="font-semibold text-white">{{ $citation['article_number'] }}</span></span>
                                    @endisset
                                    @isset($citation['text'])
                                        <p class="mt-1 text-gray-400 text-xs">{{ \Illuminate\Support\Str::limit($citation['text'], 200) }}</p>
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

        {{-- CTA Section --}}
        <div class="mt-12 relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-primary/20 via-brand-secondary/15 to-brand-primary/10 border border-brand-primary/30 p-8 text-center">
            <div class="absolute inset-0 pointer-events-none opacity-30">
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand-primary/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-brand-secondary/20 rounded-full blur-3xl"></div>
            </div>
            <div class="relative">
                <h3 class="text-xl font-bold text-white mb-2">
                    {{ $isArabic ? 'هل لديك تفاصيل خاصة بقضيتك؟' : 'Do you have specific details about your case?' }}
                </h3>
                <p class="text-gray-300 mb-6 max-w-lg mx-auto text-sm leading-relaxed">
                    {{ $isArabic
                        ? 'اطرح استفسارك على المساعد القانوني الذكي في رديف واحصل على إجابة موثقة بالمصادر.'
                        : 'Ask Radiif AI Legal Assistant now and get verified answers with exact citations from Saudi law.'
                    }}
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('legal_assistant.public') }}"
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-semibold px-6 py-3 rounded-xl shadow-glow hover:scale-105 transition-all duration-200">
                        {{ $isArabic ? 'اسأل مساعد رديف الآن' : 'Ask Radiif AI Now' }}
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 bg-white/10 text-white font-medium px-5 py-3 rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-200">
                        {{ $isArabic ? 'إنشاء حساب مجاني' : 'Create Free Account' }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
