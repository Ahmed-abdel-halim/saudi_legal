@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    /* Full-width home page overrides */
    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
        padding: 0;
        background-color: #0F172A !important;
    }

    body {
        min-height: 100vh;
        background-color: #0F172A !important;
    }

    /* Remove main wrapper constraints */
    main {
        padding-top: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        flex: 1;
    }

    /* All sections fill the viewport width */
    section {
        width: 100vw;
        max-width: 100vw;
        margin-left: 0;
        margin-right: 0;
        box-sizing: border-box;
        overflow-x: hidden;
    }


    /* ─── HERO SECTION ──────────────────────────────────── */
    .hero-bg {
        background-color: #0d1224;
        background-image:
            radial-gradient(ellipse at 70% 50%, rgba(79, 70, 229, 0.07) 0%, transparent 60%),
            radial-gradient(ellipse at 20% 80%, rgba(6, 182, 212, 0.05) 0%, transparent 50%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        padding-top: 72px;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    /* subtle dot grid overlay */
    .hero-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 32px 32px;
        pointer-events: none;
    }

    .hero-content {
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        gap: 3rem;
        padding: 4rem clamp(1.5rem, 6vw, 7rem);
        max-width: 1300px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
    }

    @media (min-width: 1024px) {
        .hero-content {
            flex-direction: row;
            align-items: center;
            gap: 4rem;
        }
        /* In RTL flex-row: items flow right→left, so:
           hero-text (1st child)  = RIGHT side  ✓
           hero-visual (2nd child) = LEFT side   ✓
           No order overrides needed. */
    }

    /* ── Text Side ── */
    .hero-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    [dir="rtl"] .hero-text { align-items: flex-start; text-align: right; }
    [dir="ltr"] .hero-text { align-items: flex-start; text-align: left;  }

    @media (max-width: 1023px) {
        .hero-text { align-items: center; text-align: center; }
    }


    /* tagline pill - matches target screenshot */
    .tagline-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 9999px;
        color: rgba(255,255,255,0.80);
        font-size: 0.8rem;
        margin-bottom: 1.5rem;
        letter-spacing: 0.01em;
        backdrop-filter: blur(4px);
    }

    .tagline-badge svg { color: #818cf8; flex-shrink: 0; }

    /* main title */
    .hero-title {
        font-size: clamp(2.6rem, 5vw, 4.5rem);
        line-height: 1.15;
        font-weight: 900;
        color: #f8fafc;
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }

    .hero-title .highlight {
        color: #06b6d4;   /* cyan */
        display: block;
    }

    /* description */
    .hero-desc {
        font-size: clamp(0.9rem, 1.4vw, 1.05rem);
        line-height: 1.9;
        color: #94a3b8;
        margin-bottom: 2.5rem;
        max-width: 480px;
    }

    /* buttons */
    .btn-group {
        display: flex;
        gap: 0.875rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-primary {
        background: #4F46E5 !important;
        color: #fff !important;
        padding: 0.875rem 1.75rem;
        border-radius: 0.625rem;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s;
        white-space: nowrap;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-primary:hover {
        background: #4338ca !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(79,70,229,0.45);
    }

    .btn-outline {
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.20);
        color: #e2e8f0;
        padding: 0.875rem 1.75rem;
        border-radius: 0.625rem;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.25s;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-outline:hover {
        background: rgba(255,255,255,0.14);
        transform: translateY(-2px);
    }

    /* ── Stats Card (Visual Side) ── */
    .hero-visual {
        flex: 0 0 auto;
        width: 100%;
        max-width: 500px;
    }

    .stats-panel {
        background: #141c2e;
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 1.25rem;
        padding: 2rem;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    /* green glow blob inside card */
    .stats-panel::after {
        content: '';
        position: absolute;
        bottom: -40px;
        right: 30px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, #10b981 0%, transparent 70%);
        opacity: 0.25;
        filter: blur(40px);
        pointer-events: none;
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.07);
    }

    .stats-header-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #e2e8f0;
    }

    .stars {
        display: flex;
        gap: 3px;
        color: #10b981;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        direction: ltr; /* keep stat boxes in visual left→right order in both RTL and LTR */
    }


    .stat-box {
        background: rgba(255,255,255,0.04);
        border-radius: 0.75rem;
        padding: 1.1rem 1rem;
        text-align: center;
    }

    .stat-val {
        font-size: 1.6rem;
        font-weight: 900;
        color: #fff;
        margin-bottom: 0.2rem;
        letter-spacing: -0.01em;
    }

    .stat-val.green { color: #10b981; }

    .stat-lbl {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.4;
    }

    /* Section headings */
    .section-title {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 900;
        color: #0F172A;
        margin-bottom: 1rem;
    }

    .section-subtitle {
        font-size: 1.125rem;
        color: #64748b;
        max-width: 800px;
        margin: 0 auto 3rem;
        line-height: 1.6;
    }

    /* Why Section */
    .why-section {
        background: white;
        padding: 5rem clamp(1.5rem, 5vw, 6rem);
        text-align: center;
        width: 100%;
        box-sizing: border-box;
    }

    .why-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .why-card {
        background: #F8FAFC;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 2.5rem 2rem;
        text-align: center;
    }

    .why-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
    }

    .icon-blue { background: #EEF2FF; color: #4F46E5; }
    .icon-green { background: #DCFCE7; color: #10b981; }
    
    .why-card h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0F172A;
        margin-bottom: 1rem;
    }

    .why-card p {
        color: #64748b;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* Steps Section */
    .steps-section {
        background: white;
        padding: 5rem clamp(1.5rem, 5vw, 6rem);
        text-align: center;
        border-top: 1px solid #f1f5f9;
        width: 100%;
        box-sizing: border-box;
    }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .step-item {
        text-align: center;
    }

    .step-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: white;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 900;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    
    .step-1 { border: 2px solid #e2e8f0; color: #4F46E5; }
    .step-2 { border: 2px solid #e2e8f0; color: #4F46E5; }
    .step-3 { border: 2px solid #DCFCE7; color: #10b981; }

    .step-item h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0F172A;
        margin-bottom: 1rem;
    }

    .step-item p {
        color: #64748b;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* CTA Section */
    .cta-section {
        background: #4F46E5;
        padding: 5rem 2rem;
        text-align: center;
        color: white;
    }

    .cta-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .cta-title {
        font-size: clamp(2rem, 3vw, 2.75rem);
        font-weight: 900;
        margin-bottom: 1.5rem;
    }

    .cta-subtitle {
        font-size: 1.125rem;
        line-height: 1.8;
        opacity: 0.9;
        margin-bottom: 2.5rem;
    }

    .btn-white {
        background: white;
        color: #4F46E5;
        padding: 1rem 2.5rem;
        border-radius: 0.5rem;
        font-weight: 800;
        font-size: 1.125rem;
        display: inline-block;
        transition: transform 0.3s;
    }

    .btn-white:hover {
        transform: translateY(-2px);
    }

    /* Services Section */
    .services-section {
        background: #F8FAFC;
        padding: 5rem clamp(1.5rem, 5vw, 6rem);
        width: 100%;
        box-sizing: border-box;
    }

    .services-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        max-width: 1200px;
        margin: 0 auto 3rem;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .expert-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        transition: box-shadow 0.3s;
    }

    .expert-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .ec-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .ec-avatar {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .eca-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #EEF2FF;
        color: #4F46E5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.25rem;
    }

    .eca-info h4 {
        font-weight: 800;
        color: #0F172A;
    }
    .eca-info p {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .ec-badge {
        background: #DCFCE7;
        color: #10b981;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .ec-body h3 {
        font-weight: 800;
        font-size: 1.125rem;
        color: #0F172A;
        margin-bottom: 0.5rem;
    }

    .ec-body p {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .ec-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        padding-top: 1rem;
    }

    .ecf-price {
        display: flex;
        flex-direction: column;
    }
    
    .ecf-price strong {
        font-size: 1.25rem;
        color: #0F172A;
    }

    .ecf-price span {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .ec-btn {
        color: #4F46E5;
        font-weight: bold;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="hero-bg" dir="{{ $direction }}">
    <div class="hero-content">

        {{-- ── Text Side (right in RTL) ── --}}
        <div class="hero-text">
            {{-- Tagline badge --}}
            <div class="tagline-badge">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                <span>{{ __('home.HERO_TAGLINE', [], $currentLang) }}</span>
            </div>

            {{-- Main heading --}}
            <h1 class="hero-title">
                {{ __('home.HERO_TITLE_LINE1', [], $currentLang) }}
                <span class="highlight">{{ __('home.HERO_TITLE_HIGHLIGHT', [], $currentLang) }}</span>
            </h1>

            {{-- Description --}}
            <p class="hero-desc">
                {{ __('home.HERO_DESCRIPTION', [], $currentLang) }}
            </p>

            {{-- Buttons --}}
            <div class="btn-group">
                <a href="{{ route('services.browse') }}" class="btn-primary">
                    {{ __('home.TRAIN_MODELS_NOW', [], $currentLang) }}
                    @if($direction === 'rtl')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                    @endif
                </a>
                <a href="{{ route('register.company', ['type' => 'supplier']) }}" class="btn-outline">
                    {{ __('home.INVEST_TEAM_TIME', [], $currentLang) }}
                    @if($direction === 'rtl')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 7L7 17"/><path d="M17 17H7V7"/></svg>
                    @endif
                </a>
            </div>
        </div>

        {{-- ── Stats Card (left in RTL) ── --}}
        <div class="hero-visual">
            <div class="stats-panel">
                {{-- Card header: title + 5 stars --}}
                <div class="stats-header">
                    <span class="stats-header-title">{{ __('home.TRUST_PERFORMANCE_INDICATORS', [], $currentLang) }}</span>
                    <div class="stars">
                        @for($i = 0; $i < 5; $i++)
                        <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                </div>

                {{-- 2×2 Stats Grid: same order as screenshot --}}
                <div class="stats-grid">
                    {{-- +500 / خبير مؤسسي موثق --}}
                    <div class="stat-box">
                        <div class="stat-val">{{ __('home.STAT_1_NUMBER', [], $currentLang) }}</div>
                        <div class="stat-lbl">{{ __('home.STAT_1_TEXT', [], $currentLang) }}</div>
                    </div>
                    {{-- +120 / شركة مزودة للكفاءات --}}
                    <div class="stat-box">
                        <div class="stat-val">{{ __('home.STAT_2_NUMBER', [], $currentLang) }}</div>
                        <div class="stat-lbl">{{ __('home.STAT_2_TEXT', [], $currentLang) }}</div>
                    </div>
                    {{-- NDA / عقود B2B صارمة --}}
                    <div class="stat-box">
                        <div class="stat-val">{{ __('home.STAT_4_NUMBER', [], $currentLang) }}</div>
                        <div class="stat-lbl">{{ __('home.STAT_4_TEXT', [], $currentLang) }}</div>
                    </div>
                    {{-- 99.8% green / دقة تقييم النماذج --}}
                    <div class="stat-box">
                        <div class="stat-val green">{{ __('home.STAT_3_NUMBER', [], $currentLang) }}</div>
                        <div class="stat-lbl">{{ __('home.STAT_3_TEXT', [], $currentLang) }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- Why Radiif Section --}}
<section class="why-section" dir="{{ $direction }}">
    <div class="container mx-auto px-4">
        <h2 class="section-title">{{ __('home.WHY_RADIIF_TITLE', [], $currentLang) }}</h2>
        <p class="section-subtitle">{{ __('home.WHY_RADIIF_SUBTITLE', [], $currentLang) }}</p>

        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                </div>
                <h3>{{ __('home.WHY_CARD_1_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.WHY_CARD_1_DESC', [], $currentLang) }}</p>
            </div>
            <div class="why-card">
                <div class="why-icon icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>{{ __('home.WHY_CARD_2_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.WHY_CARD_2_DESC', [], $currentLang) }}</p>
            </div>
            <div class="why-card">
                <div class="why-icon icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h3>{{ __('home.WHY_CARD_3_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.WHY_CARD_3_DESC', [], $currentLang) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Services Section --}}
@if(isset($services) && count($services) > 0)
<section class="services-section" dir="{{ $direction }}">
    <div class="services-header">
        <div>
            <h2 class="section-title text-left" style="margin-bottom:0.5rem">{{ __('home.RECENT_SERVICES_TITLE', [], $currentLang) }}</h2>
            <p style="color:#64748b;">{{ __('home.RECENT_SERVICES_SUBTITLE', [], $currentLang) }}</p>
        </div>
        <a href="{{ route('services.browse') }}" class="text-brand-primary font-bold flex items-center gap-2 hover:underline">
            @if($direction === 'rtl')
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            @endif
            {{ __('home.BROWSE_ALL_BTN', [], $currentLang) }}
            @if($direction === 'ltr')
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            @endif
        </a>
    </div>

    <div class="services-grid">
        @foreach($services as $service)
        <div class="expert-card">
            <div class="ec-top">
                <div class="ec-avatar">
                    <div class="eca-circle">
                        @php
                            $names = explode(' ', $service->expert_name);
                            $initials = mb_substr($names[0], 0, 1);
                            if(count($names) > 1) {
                                $initials .= mb_substr(end($names), 0, 1);
                            }
                            echo mb_strtoupper($initials);
                        @endphp
                    </div>
                    <div class="eca-info">
                        <h4>{{ $service->expert_name }}</h4>
                        <p>{{ $service->company_name ?? 'شركة التقنية الحديثة' }}</p>
                    </div>
                </div>
                <div class="ec-badge">موثق مؤسسياً</div>
            </div>
            
            <div class="ec-body">
                <h3>{{ $service->title }}</h3>
                <p>{{ Str::limit($service->description ?? 'مراجعة وتقييم المخرجات البرمجية للنماذج اللغوية الكبيرة', 100) }}</p>
            </div>
            
            <div class="ec-footer">
                <a href="{{ route('services.show', ['id' => $service->service_id]) }}" class="ec-btn">
                    {{ __('home.REQUEST_COMPETENCE', [], $currentLang) }}
                </a>
                <div class="ecf-price">
                    <strong>{{ number_format($service->hourly_rate, 2) }}</strong>
                    <span>{{ __('home.CURRENCY_HOUR', [], $currentLang) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- How It Works Section --}}
<section class="steps-section" dir="{{ $direction }}">
    <div class="container mx-auto px-4">
        <h2 class="section-title">{{ __('home.HOW_IT_WORKS_TITLE', [], $currentLang) }}</h2>
        <p class="section-subtitle">{{ __('home.HOW_IT_WORKS_SUBTITLE', [], $currentLang) }}</p>

        <div class="steps-grid">
            <div class="step-item">
                <div class="step-circle step-1">1</div>
                <h3>{{ __('home.STEP_1_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.STEP_1_DESC', [], $currentLang) }}</p>
            </div>
            <div class="step-item">
                <div class="step-circle step-2">2</div>
                <h3>{{ __('home.STEP_2_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.STEP_2_DESC', [], $currentLang) }}</p>
            </div>
            <div class="step-item">
                <div class="step-circle step-3">3</div>
                <h3>{{ __('home.STEP_3_TITLE', [], $currentLang) }}</h3>
                <p>{{ __('home.STEP_3_DESC', [], $currentLang) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="cta-section" dir="{{ $direction }}">
    <div class="container mx-auto px-4">
        <div class="cta-content">
            <h2 class="cta-title">{{ __('home.CTA_BANNER_TITLE', [], $currentLang) }}</h2>
            <p class="cta-subtitle">{{ __('home.CTA_BANNER_SUBTITLE', [], $currentLang) }}</p>
            <a href="{{ route('register.company', ['type' => 'supplier']) }}" class="btn-white">
                {{ __('home.CTA_BANNER_BTN', [], $currentLang) }}
            </a>
        </div>
    </div>
</section>

@endsection
