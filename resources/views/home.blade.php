@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    html,
    body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        min-height: 100vh;
    }

    .footer,
    footer {
        display: block !important;
        margin-top: 50px !important;
        background: #fff;
        padding: 20px;
        text-align: center;
        border-top: 1px solid #eee;
    }

    /* Remove main padding for home page */
    main {
        padding-top: 0 !important;
    }

    /* Hero section fills entire viewport - Fully Responsive */
    .hero-fullscreen {
        min-height: 100vh;
        min-height: 100dvh;
        /* Dynamic viewport height for mobile */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 80px 0 40px;
        margin-top: 0 !important;
        position: relative;
    }

    @media (min-width: 768px) {
        .hero-fullscreen {
            padding: 100px 0 60px;
        }
    }

    @media (min-width: 1024px) {
        .hero-fullscreen {
            padding: 0;
            height: 100vh;
            height: 100dvh;
        }
    }

    /* Ensure content sections are full width */
    section {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Professional centered content - Fully Responsive */
    .hero-content-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: clamp(1rem, 2vw, 2rem);
        gap: clamp(2rem, 4vw, 3rem);
    }

    @media (min-width: 768px) {
        .hero-content-wrapper {
            padding: clamp(1.5rem, 3vw, 2.5rem);
            gap: clamp(2.5rem, 5vw, 4rem);
        }
    }

    @media (min-width: 1024px) {
        .hero-content-wrapper {
            flex-direction: row;
            align-items: center;
            gap: clamp(4rem, 6vw, 6rem);
            padding: clamp(2rem, 4vw, 4rem);
            max-width: 1600px;
            text-align: left;
        }

        .hero-text-content {
            text-align: left;
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-image-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 0;
            position: relative;
        }
    }

    @media (min-width: 1440px) {
        .hero-content-wrapper {
            gap: 8rem;
            padding: 0 5rem;
        }
    }

    /* Professional hero text styling - Fully Responsive */
    .hero-tagline {
        display: inline-flex !important;
        align-items: center;
        gap: clamp(0.5rem, 1vw, 0.75rem);
        padding: clamp(0.5rem, 1.2vw, 0.625rem) clamp(1rem, 2vw, 1.25rem);
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.05);
        border: 0.5px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        margin-bottom: 2rem !important;
        transition: all 0.3s ease;
        font-size: clamp(0.75rem, 1.2vw, 0.875rem);
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 10;
        position: relative;
        min-height: auto;
        overflow: visible;
        order: -1;
    }

    /* Arabic (RTL) specific styling - Position down more */
    [dir="rtl"] .hero-tagline {
        align-items: center;
        text-align: right;
        transform: translateY(4px);
        margin-bottom: 1.75rem !important;
    }

    /* English (LTR) specific styling - Position up more */
    [dir="ltr"] .hero-tagline {
        align-items: center;
        text-align: left;
        transform: translateY(-4px);
        margin-bottom: 2.25rem !important;
    }

    /* Ensure tagline is visible at all zoom levels */
    @media (min-width: 320px) {
        .hero-tagline {
            display: inline-flex !important;
            visibility: visible !important;
        }
    }

    @media (max-width: 768px) {
        .hero-tagline {
            font-size: clamp(0.7rem, 2vw, 0.875rem);
            padding: clamp(0.4rem, 1vw, 0.625rem) clamp(0.8rem, 2vw, 1.25rem);
        }
    }

    .hero-tagline:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .hero-title {
        font-size: clamp(2rem, 6vw + 0.5rem, 4.5rem);
        line-height: clamp(1.1, 1.2, 1.15);
        font-weight: 900;
        letter-spacing: -0.02em;
        margin-bottom: clamp(1rem, 2.5vw, 1.5rem);
        word-break: break-word;
        hyphens: auto;
    }

    @media (min-width: 1024px) {
        .hero-title {
            font-size: clamp(3rem, 5.5vw, 4.5rem);
        }
    }

    .hero-description {
        font-size: clamp(0.875rem, 1.8vw + 0.25rem, 1.25rem);
        line-height: clamp(1.6, 1.8, 1.7);
        margin-bottom: clamp(1.5rem, 3vw, 2.5rem);
        opacity: 0.9;
        max-width: 100%;
    }

    @media (min-width: 768px) {
        .hero-description {
            font-size: clamp(1rem, 2vw, 1.25rem);
        }
    }

    /* Professional button group - Fully Responsive */
    .hero-cta-group {
        display: flex;
        flex-wrap: wrap;
        gap: clamp(0.75rem, 1.5vw, 1rem);
        margin-bottom: clamp(2rem, 4vw, 3rem);
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    @media (min-width: 640px) {
        .hero-cta-group {
            flex-wrap: nowrap;
        }
    }

    @media (min-width: 1024px) {
        .hero-cta-group {
            justify-content: flex-start;
            width: auto;
        }
    }

    /* Responsive buttons */
    .hero-cta-group a {
        padding: clamp(0.75rem, 1.5vw, 1rem) clamp(1.5rem, 3vw, 2rem);
        font-size: clamp(0.875rem, 1.3vw, 1.125rem);
        border-radius: clamp(1rem, 2vw, 1.5rem);
        white-space: nowrap;
        flex-shrink: 0;
    }

    @media (max-width: 639px) {
        .hero-cta-group {
            flex-direction: column;
        }

        .hero-cta-group a {
            width: 100%;
            max-width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .hero-cta-group a {
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
        }
    }

    /* Professional stats grid - Fully Responsive */
    .hero-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: clamp(1rem, 2vw, 2rem);
        padding-top: clamp(1.5rem, 3vw, 2rem);
        margin-top: clamp(2rem, 4vw, 3rem);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        width: 100%;
    }

    @media (max-width: 480px) {
        .hero-stats {
            gap: 0.75rem;
            padding-top: 1.5rem;
            margin-top: 2rem;
        }
    }

    .hero-stat-item {
        text-align: center;
        padding: 0 clamp(0.5rem, 1vw, 1rem);
    }

    .hero-stat-number {
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 800;
        line-height: 1;
        margin-bottom: clamp(0.375rem, 0.8vw, 0.5rem);
        background: linear-gradient(135deg, #ffffff 0%, rgba(255, 255, 255, 0.8) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        word-break: keep-all;
    }

    @media (min-width: 768px) {
        .hero-stat-number {
            font-size: clamp(2rem, 3.5vw, 2.5rem);
        }
    }

    .hero-stat-label {
        font-size: clamp(0.625rem, 1.2vw, 0.75rem);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        opacity: 0.7;
        line-height: 1.3;
    }

    /* Center all section content - Wider */
    .section-content {
        text-align: center;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    @media (min-width: 1280px) {
        .section-content {
            max-width: 1600px;
            padding: 0 4rem;
        }
    }

    /* Wider container */
    .container {
        max-width: 100%;
    }

    @media (min-width: 1536px) {
        .container {
            max-width: 1600px;
        }
    }

    /* Center buttons professionally */
    .btn-group-centered {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    @keyframes pulse-slow {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .animate-pulse-slow {
        animation: pulse-slow 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out;
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .glass-dark {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="hero-fullscreen relative bg-dark-navy text-white overflow-hidden flex items-center">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[30%] -right-[10%] w-[70%] h-[70%] bg-brand-primary/20 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-[10%] -left-[10%] w-[50%] h-[50%] bg-brand-secondary/20 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>
        <div class="absolute top-[20%] left-[20%] w-[30%] h-[30%] bg-brand-magenta/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 4s;"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-center opacity-10"></div>
    </div>

    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 relative z-10 w-full max-w-[1920px]">
        <div class="hero-content-wrapper">
            {{-- Hero Text Content --}}
            <div class="hero-text-content w-full lg:w-1/2">
                {{-- Tagline Badge --}}
                <div class="hero-tagline animate-fade-in-up mx-auto lg:mx-0" style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important;">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-teal opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-teal"></span>
                    </span>
                    <span class="text-brand-teal text-sm font-semibold tracking-wide">🚀 {{ __('home.HERO_TAGLINE', [], $currentLang) }}</span>
                </div>

                {{-- Main Heading --}}
                <h1 class="hero-title text-white text-center lg:text-right">
                    @guest<br>@endguest
                    {{ __('home.HERO_TITLE_LINE1', [], $currentLang) }} <br>
                    <span class="inline-block text-transparent bg-clip-text bg-gradient-to-r from-brand-teal via-brand-primary to-brand-secondary">
                        {{ __('home.HERO_TITLE_HIGHLIGHT', [], $currentLang) }}
                    </span>
                </h1>

                {{-- Description --}}
                <p class="hero-description text-gray-300 max-w-2xl mx-auto lg:mx-0 font-light text-center lg:text-left">
                    {{ __('home.HERO_DESCRIPTION', [], $currentLang) }}
                </p>

                {{-- Call to Action Buttons --}}
                <div class="hero-cta-group justify-center lg:justify-start">
                    <a href="{{ $exploreUrl }}"
                        class="group relative px-6 py-4 bg-brand-primary rounded-2xl font-bold text-base lg:text-lg text-white shadow-lg shadow-brand-primary/30 hover:shadow-brand-primary/50 transition-all duration-300 hover:-translate-y-1 overflow-hidden focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 focus:ring-offset-dark-navy whitespace-nowrap flex-shrink-0"
                        aria-label="{{ __('home.BROWSE_TALENT', [], $currentLang) }}">
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        <span class="relative flex items-center justify-center gap-2">
                            {{ __('home.BROWSE_TALENT', [], $currentLang) }}
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform {{ $direction === 'rtl' ? 'rotate-180' : '' }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </span>
                    </a>
                    <a href="{{ $supplierUrl }}"
                        class="px-6 py-4 bg-white/5 border border-white/10 backdrop-blur-sm rounded-2xl font-bold text-base lg:text-lg text-white hover:bg-white/10 transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-white/20 focus:ring-offset-2 focus:ring-offset-dark-navy whitespace-nowrap flex-shrink-0"
                        aria-label="{{ __('home.REGISTER_COMPANY_NOW', [], $currentLang) }}">
                        {{ __('home.REGISTER_COMPANY_NOW', [], $currentLang) }}
                    </a>
                </div>

                {{-- Statistics Grid --}}
                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <div class="hero-stat-number">500+</div>
                        <div class="hero-stat-label">{{ __('home.STAT_EXPERTS', [], $currentLang) }}</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-number">120+</div>
                        <div class="hero-stat-label">{{ __('home.STAT_COMPANIES', [], $currentLang) }}</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-number">5k+</div>
                        <div class="hero-stat-label">{{ __('home.STAT_HOURS', [], $currentLang) }}</div>
                    </div>
                </div>
            </div>

            {{-- Hero Image Content --}}
            <div class="hero-image-content w-full lg:w-1/2 relative hidden lg:flex items-center justify-center">
                <div class="relative z-10 animate-float w-full max-w-lg">
                    {{-- Floating Card - Responsive --}}
                    <div class="absolute -top-8 lg:-top-12 {{ $direction === 'rtl' ? '-right-8 lg:-right-12' : '-left-8 lg:-left-12' }} glass-dark p-4 lg:p-6 rounded-2xl shadow-2xl z-20 max-w-[280px] lg:max-w-xs">
                        <div class="flex items-center gap-3 lg:gap-4 mb-3">
                            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-bold text-lg lg:text-xl shadow-lg flex-shrink-0">
                                R
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs lg:text-sm font-bold text-white truncate">{{ __('home.CARD_CERTIFIED_EXPERT', [], $currentLang) }}</div>
                                <div class="text-[10px] lg:text-xs text-gray-400 truncate">{{ __('home.CARD_HIGH_RATING', [], $currentLang) }}</div>
                            </div>
                        </div>
                        <div class="h-1.5 lg:h-2 bg-white/10 rounded-full w-full mb-2 overflow-hidden">
                            <div class="h-full bg-brand-teal w-3/4 rounded-full transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between text-[10px] lg:text-xs text-gray-400">
                            <span class="truncate mr-2">{{ __('home.CARD_TASK_COMPLETION', [], $currentLang) }}</span>
                            <span class="text-brand-teal font-semibold flex-shrink-0">75%</span>
                        </div>
                    </div>

                    {{-- Main Hero Image --}}
                    <div class="relative rounded-2xl lg:rounded-3xl overflow-hidden border border-white/10 shadow-2xl shadow-brand-primary/20">
                        <div class="absolute inset-0 bg-gradient-to-t from-dark-navy/80 to-transparent z-10"></div>
                        <img src="{{ asset('images/home-img.png') }}"
                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80';"
                            alt="{{ __('home.HERO_TAGLINE', [], $currentLang) }}"
                            class="w-full h-auto object-cover"
                            loading="eager"
                            style="aspect-ratio: 4/3;">
                        {{-- Trust Badge - Responsive --}}
                        <div class="absolute bottom-4 lg:bottom-8 {{ $direction === 'rtl' ? 'right-4 lg:right-8' : 'left-4 lg:left-8' }} z-20 bg-white/10 backdrop-blur-md border border-white/20 p-3 lg:p-4 rounded-lg lg:rounded-xl flex items-center gap-2 lg:gap-3 max-w-[85%]">
                            <div class="bg-green-500/20 text-green-400 p-1.5 lg:p-2 rounded-lg flex-shrink-0">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs lg:text-sm font-bold text-white truncate">{{ __('home.TRUST_BADGE_TITLE', [], $currentLang) }}</div>
                                <div class="text-[10px] lg:text-xs text-gray-300 truncate">{{ __('home.TRUST_BADGE_SUBTITLE', [], $currentLang) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Services Section --}}
@if(count($services) > 0)
<section class="py-24 bg-slate-light relative">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 xl:px-12">
        <div class="section-content">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12">
                <div class="max-w-2xl text-center md:text-left mb-6 md:mb-0">
                    <h2 class="text-3xl md:text-4xl font-bold text-dark-navy mb-4">{{ __('home.RECENT_SERVICES_TITLE', [], $currentLang) }}</h2>
                    <p class="text-lg text-gray-600">{{ __('home.RECENT_SERVICES_SUBTITLE', [], $currentLang) }}</p>
                </div>
                <a href="{{ $exploreUrl }}" class="inline-flex items-center justify-center text-brand-primary font-bold hover:underline mt-4 md:mt-0">
                    {{ __('home.BROWSE_ALL_BTN', [], $currentLang) }}
                    <svg class="w-4 h-4 {{ $direction === 'rtl' ? 'mr-2 rotate-180' : 'ml-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6 lg:gap-8 justify-items-center">
                @foreach($services as $service)
                <div class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 w-full max-w-md">
                    <a href="#" class="block">
                        <div class="relative h-48 overflow-hidden bg-gray-200">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent z-10"></div>
                            @php
                            $serviceImg = !empty($service['image'])
                            ? $service['image']
                            : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80';
                            $onError = "this.onerror=null; this.src='https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80';";
                            @endphp
                            <img src="{{ $serviceImg }}"
                                alt="{{ $service['title'] }}"
                                class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700"
                                onerror="{{ $onError }}">

                            <div class="absolute bottom-4 {{ $direction === 'rtl' ? 'right-4' : 'left-4' }} z-20">
                                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    {{ $service['company_name'] }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-dark-navy mb-2 line-clamp-1 group-hover:text-brand-primary transition-colors">{{ $service['title'] }}</h3>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600 overflow-hidden">
                                    @if(!empty($service['expert_image']))
                                    <img src="{{ $service['expert_image'] }}" class="w-full h-full object-cover" alt="{{ $service['expert_name'] }}">
                                    @else
                                    {{ mb_substr($service['expert_name'], 0, 1, "UTF-8") }}
                                    @endif
                                </div>
                                <span class="text-sm text-gray-500">{{ $service['expert_name'] }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 uppercase tracking-wider">{{ __('home.PRICE_LABEL', [], $currentLang) }}</span>
                                    <span class="text-lg font-bold text-dark-navy">{{ $service['hourly_rate'] }} <span class="text-sm font-normal text-gray-500">{{ __('home.CURRENCY_HOUR', [], $currentLang) }}</span></span>
                                </div>
                                <span class="text-brand-primary bg-brand-primary/10 p-2 rounded-lg group-hover:bg-brand-primary group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5 {{ $direction === 'rtl' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
</section>
@endif

{{-- How It Works Section --}}
<section class="py-24 bg-dark-navy text-white relative overflow-hidden">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 xl:px-12 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ __('home.HOW_IT_WORKS_TITLE', [], $currentLang) }}</h2>
            <p class="text-gray-400 max-w-2xl mx-auto">{{ __('home.HOW_IT_WORKS_SUBTITLE', [], $currentLang) }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 xl:gap-16 relative max-w-6xl mx-auto">
            <div class="relative text-center group">
                <div class="w-24 h-24 mx-auto bg-brand-dark border border-white/10 rounded-full flex items-center justify-center mb-6 relative z-10">
                    <span class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-br from-brand-primary to-brand-secondary">1</span>
                </div>
                <h3 class="text-xl font-bold mb-3">{{ __('home.STEP_1_TITLE', [], $currentLang) }}</h3>
                <p class="text-gray-400 text-sm leading-relaxed">{{ __('home.STEP_1_DESC', [], $currentLang) }}</p>
            </div>
            <div class="relative text-center group">
                <div class="w-24 h-24 mx-auto bg-brand-dark border border-white/10 rounded-full flex items-center justify-center mb-6 relative z-10">
                    <span class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-br from-brand-secondary to-brand-magenta">2</span>
                </div>
                <h3 class="text-xl font-bold mb-3">{{ __('home.STEP_2_TITLE', [], $currentLang) }}</h3>
                <p class="text-gray-400 text-sm leading-relaxed">{{ __('home.STEP_2_DESC', [], $currentLang) }}</p>
            </div>
            <div class="relative text-center group">
                <div class="w-24 h-24 mx-auto bg-brand-dark border border-white/10 rounded-full flex items-center justify-center mb-6 relative z-10">
                    <span class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-br from-brand-magenta to-white">3</span>
                </div>
                <h3 class="text-xl font-bold mb-3">{{ __('home.STEP_3_TITLE', [], $currentLang) }}</h3>
                <p class="text-gray-400 text-sm leading-relaxed">{{ __('home.STEP_3_DESC', [], $currentLang) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-primary"></div>
    <div class="container mx-auto px-4 md:px-6 lg:px-8 xl:px-12 relative z-10 text-center max-w-5xl">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-8">{{ __('home.CTA_BANNER_TITLE', [], $currentLang) }}</h2>
        <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto">{{ __('home.CTA_BANNER_SUBTITLE', [], $currentLang) }}</p>
        <a href="{{ $supplierUrl }}" class="bg-white text-brand-primary px-12 py-5 rounded-full font-bold text-xl shadow-2xl hover:bg-gray-50 transition transform hover:scale-105 inline-flex items-center gap-2">
            {{ __('home.CTA_BANNER_BTN', [], $currentLang) }}
        </a>
    </div>
</section>
@endsection