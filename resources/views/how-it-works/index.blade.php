@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    /* Full dark theme integration */
    html, body {
        background-color: #0b1120 !important;
        color: #f1f5f9 !important;
    }

    /* Connecting line style */
    .connecting-line {
        background: linear-gradient(90deg, rgba(79, 70, 229, 0.15), rgba(139, 92, 246, 0.15), rgba(13, 148, 136, 0.15));
    }

    .step-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.45) 0%, rgba(15, 23, 42, 0.2) 100%);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.5rem;
        padding: 2.5rem 1.75rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .step-card:hover {
        transform: translateY(-6px);
        border-color: rgba(13, 148, 136, 0.25);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35), 0 0 25px rgba(13, 148, 136, 0.08);
    }

    /* Testimonial glassmorphic style */
    .premium-testimonial-card {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.5) 0%, rgba(30, 41, 59, 0.2) 100%);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s ease;
    }

    .premium-testimonial-card:hover {
        transform: translateY(-4px);
        border-color: rgba(79, 70, 229, 0.2);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="relative bg-dark-navy text-white py-24 overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-green/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center max-w-4xl">
        <span class="text-brand-green font-bold text-xs uppercase tracking-widest bg-brand-green/10 px-3.5 py-1.5 rounded-full border border-brand-green/20 mb-6 inline-block">
            {{ $currentLang === 'ar' ? 'رحلتك معنا' : 'Our Workflow' }}
        </span>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black mb-6 leading-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
            {{ __('how-it-works.HOW_IT_WORKS_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
            {{ __('how-it-works.HOW_IT_WORKS_SUBTITLE', [], $currentLang) }}
        </p>
    </div>
</section>

{{-- Steps Section --}}
<section class="py-20 bg-dark-navy relative">
    <div class="container mx-auto px-4 relative z-10 max-w-[1400px] w-full">
        {{-- Connecting Line (Desktop Only) --}}
        <div class="hidden md:block absolute top-1/2 left-0 w-full h-[2px] connecting-line -translate-y-1/2 z-0"></div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            {{-- Step 1 --}}
            <div class="step-card group text-center">
                <div class="w-20 h-20 mx-auto bg-slate-900 border-2 border-brand-primary/30 rounded-full flex items-center justify-center text-2xl font-black text-brand-primary shadow-glow mb-6 step-number group-hover:bg-brand-primary group-hover:text-white transition-all duration-300 relative">
                    1
                    <div class="absolute -bottom-2 {{ $direction === 'rtl' ? '-left-2' : '-right-2' }} w-8 h-8 bg-slate-950 rounded-full flex items-center justify-center border border-white/10">
                        <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-white mb-3 group-hover:text-brand-primary transition-colors">
                    {{ __('how-it-works.STEP_1_TITLE', [], $currentLang) }}
                </h3>
                <p class="text-slate-400 leading-relaxed px-4 text-sm md:text-base">
                    {{ __('how-it-works.STEP_1_DESC', [], $currentLang) }}
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="step-card group text-center">
                <div class="w-20 h-20 mx-auto bg-slate-900 border-2 border-brand-secondary/30 rounded-full flex items-center justify-center text-2xl font-black text-brand-secondary shadow-glow mb-6 step-number group-hover:bg-brand-secondary group-hover:text-white transition-all duration-300 relative">
                    2
                    <div class="absolute -bottom-2 {{ $direction === 'rtl' ? '-left-2' : '-right-2' }} w-8 h-8 bg-slate-950 rounded-full flex items-center justify-center border border-white/10">
                        <svg class="w-4 h-4 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-white mb-3 group-hover:text-brand-secondary transition-colors">
                    {{ __('how-it-works.STEP_2_TITLE', [], $currentLang) }}
                </h3>
                <p class="text-slate-400 leading-relaxed px-4 text-sm md:text-base">
                    {{ __('how-it-works.STEP_2_DESC', [], $currentLang) }}
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="step-card group text-center">
                <div class="w-20 h-20 mx-auto bg-slate-900 border-2 border-brand-teal/30 rounded-full flex items-center justify-center text-2xl font-black text-brand-teal shadow-glow mb-6 step-number group-hover:bg-brand-teal group-hover:text-white transition-all duration-300 relative">
                    3
                    <div class="absolute -bottom-2 {{ $direction === 'rtl' ? '-left-2' : '-right-2' }} w-8 h-8 bg-slate-950 rounded-full flex items-center justify-center border border-white/10">
                        <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-white mb-3 group-hover:text-brand-teal transition-colors">
                    {{ __('how-it-works.STEP_3_TITLE', [], $currentLang) }}
                </h3>
                <p class="text-slate-400 leading-relaxed px-4 text-sm md:text-base">
                    {{ __('how-it-works.STEP_3_DESC', [], $currentLang) }}
                </p>
            </div>
        </div>

        {{-- Read More Link --}}
        <div class="text-center mt-12 md:mt-16">
            <a href="#"
                class="inline-flex items-center text-brand-green font-bold hover:text-white transition-colors text-base md:text-lg border-b-2 border-brand-green/20 hover:border-brand-green pb-1 group">
                {{ __('how-it-works.READ_FULL_DETAILS', [], $currentLang) }}
                <svg class="w-5 h-5 {{ $direction === 'rtl' ? 'mr-2 rotate-180 group-hover:-translate-x-1' : 'ml-2 group-hover:translate-x-1' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Success Stories Section --}}
<section class="py-20 bg-dark-navy/60 relative border-t border-white/5">
    <div class="container mx-auto px-4 w-full max-w-[1400px]">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">
                {{ __('how-it-works.SEC_SUCCESS_STORIES', [], $currentLang) }}
            </h2>
            <p class="text-slate-400 text-base md:text-lg max-w-2xl mx-auto leading-relaxed">
                {{ __('how-it-works.TESTIMONIALS_DESC', [], $currentLang) }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-[1400px] mx-auto">
            {{-- Testimonial 1 --}}
            <div class="premium-testimonial-card p-6 md:p-8 rounded-2xl relative overflow-hidden">
                <div class="absolute top-0 {{ $direction === 'rtl' ? 'right-0' : 'left-0' }} w-1 h-full bg-brand-primary"></div>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-brand-primary/10 text-brand-primary rounded-xl flex items-center justify-center font-black text-lg flex-shrink-0 border border-brand-primary/20">
                        TS
                    </div>
                    <div class="{{ $direction === 'rtl' ? 'mr-4' : 'ml-4' }} flex-1 min-w-0">
                        <h4 class="font-black text-white text-base md:text-lg truncate">
                            {{ $currentLang === 'ar' ? 'شركة الحلول التقنية' : 'Tech Solutions Inc.' }}
                        </h4>
                        <span class="text-xs text-slate-400 uppercase bg-white/5 px-2 py-0.5 rounded inline-block mt-1 font-bold">
                            {{ __('how-it-works.INDUSTRY_IT', [], $currentLang) }}
                        </span>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <strong class="block text-xs text-slate-500 uppercase tracking-wider mb-1 font-bold">
                            {{ __('how-it-works.PREVIOUS_CHALLENGE', [], $currentLang) }}
                        </strong>
                        <p class="text-slate-300 text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'الحاجة إلى مصممي واجهات مستخدم محترفين لمشروع مدته 3 أشهر.' 
                                : 'Need for senior UI designers for a 3-month project.' }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/5">
                        <strong class="block text-xs text-brand-primary uppercase tracking-wider mb-1 font-bold">
                            {{ __('how-it-works.SOLUTION_WITH_TS', [], $currentLang) }}
                        </strong>
                        <p class="text-emerald-400 font-semibold text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'تم توظيف خبيرين من وكالة تصميم رائدة خلال 48 ساعة.' 
                                : 'Hired 2 experts from a leading design agency within 48 hours.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Testimonial 2 --}}
            <div class="premium-testimonial-card p-6 md:p-8 rounded-2xl relative overflow-hidden">
                <div class="absolute top-0 {{ $direction === 'rtl' ? 'right-0' : 'left-0' }} w-1 h-full bg-brand-secondary"></div>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-brand-secondary/10 text-brand-secondary rounded-xl flex items-center justify-center font-black text-lg flex-shrink-0 border border-brand-secondary/20">
                        MP
                    </div>
                    <div class="{{ $direction === 'rtl' ? 'mr-4' : 'ml-4' }} flex-1 min-w-0">
                        <h4 class="font-black text-white text-base md:text-lg truncate">
                            {{ $currentLang === 'ar' ? 'التسويق المحترف' : 'Marketing Pro' }}
                        </h4>
                        <span class="text-xs text-slate-400 uppercase bg-white/5 px-2 py-0.5 rounded inline-block mt-1 font-bold">
                            {{ __('how-it-works.INDUSTRY_MARKETING', [], $currentLang) }}
                        </span>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <strong class="block text-xs text-slate-500 uppercase tracking-wider mb-1 font-bold">
                            {{ __('how-it-works.PREVIOUS_CHALLENGE', [], $currentLang) }}
                        </strong>
                        <p class="text-slate-300 text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'فريق إبداعي عاطل خلال الموسم المنخفض.' 
                                : 'Idle creative team during off-season.' }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-white/5">
                        <strong class="block text-xs text-brand-secondary uppercase tracking-wider mb-1 font-bold">
                            {{ __('how-it-works.SOLUTION_WITH_TS', [], $currentLang) }}
                        </strong>
                        <p class="text-emerald-400 font-semibold text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'تم توليد 150,000 ر.س من الإيرادات من خلال إعارة الفريق لشركات أخرى.' 
                                : 'Generated 150k SAR revenue by lending team to other firms.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Final CTA Section --}}
<section class="py-20 md:py-24 bg-gradient-to-br from-brand-green-dim via-dark-navy to-dark-navy text-white text-center relative overflow-hidden border-t border-white/5">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-[0.03]"></div>
    <div class="container mx-auto px-4 relative z-10 max-w-4xl">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-black mb-6">
            {{ __('how-it-works.SEC_FINAL_CTA', [], $currentLang) }}
        </h2>
        <p class="text-base md:text-lg text-slate-300 mb-10 max-w-xl mx-auto leading-relaxed">
            {{ __('how-it-works.FINAL_CTA_DESC', [], $currentLang) }}
        </p>
        <a href="{{ route('login') }}"
            class="inline-flex items-center bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy px-8 md:px-10 py-4 rounded-full font-black text-base md:text-lg hover:opacity-90 hover:scale-[1.03] transition-all duration-300 shadow-green-glow group">
            {{ __('how-it-works.BTN_START_NOW', [], $currentLang) }}
            <svg class="w-5 h-5 {{ $direction === 'rtl' ? 'mr-2 rotate-180 group-hover:-translate-x-1' : 'ml-2 group-hover:translate-x-1' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
</section>
@endsection