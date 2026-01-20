@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    /* Ensure all sections are full width */
    section {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .step-number {
        transition: all 0.3s ease;
    }

    .step-group:hover .step-number {
        transform: translateY(-8px) scale(1.05);
    }

    .testimonial-card {
        transition: all 0.3s ease;
    }

    .testimonial-card:hover {
        transform: translateY(-4px);
    }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="relative bg-dark-navy text-white py-20 md:py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-primary/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-secondary/20 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 relative z-10 text-center w-full">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
            {{ __('how-it-works.HOW_IT_WORKS_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
            {{ __('how-it-works.HOW_IT_WORKS_SUBTITLE', [], $currentLang) }}
        </p>
    </div>
</section>

{{-- Steps Section --}}
<section class="py-16 md:py-20 bg-white relative">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 w-full">
        {{-- Connecting Line (Desktop Only) --}}
        <div class="hidden md:block absolute top-1/2 left-0 w-full h-1 bg-gray-100 -translate-y-1/2 z-0"></div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 relative z-10">
            {{-- Step 1 --}}
            <div class="step-group text-center">
                <div class="w-20 h-20 mx-auto bg-white border-4 border-brand-primary rounded-full flex items-center justify-center text-2xl font-black text-brand-primary shadow-lg mb-6 step-number group-hover:bg-brand-primary group-hover:text-white transition-all duration-300 relative">
                    1
                    <div class="absolute -bottom-2 {{ $direction === 'rtl' ? '-left-2' : '-right-2' }} w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center border border-gray-200">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-dark-navy mb-3 group-hover:text-brand-primary transition-colors">
                    {{ __('how-it-works.STEP_1_TITLE', [], $currentLang) }}
                </h3>
                <p class="text-gray-600 leading-relaxed px-4 text-sm md:text-base">
                    {{ __('how-it-works.STEP_1_DESC', [], $currentLang) }}
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="step-group text-center">
                <div class="w-20 h-20 mx-auto bg-white border-4 border-brand-secondary rounded-full flex items-center justify-center text-2xl font-black text-brand-secondary shadow-lg mb-6 step-number group-hover:bg-brand-secondary group-hover:text-white transition-all duration-300 relative">
                    2
                    <div class="absolute -bottom-2 {{ $direction === 'rtl' ? '-left-2' : '-right-2' }} w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center border border-gray-200">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-dark-navy mb-3 group-hover:text-brand-secondary transition-colors">
                    {{ __('how-it-works.STEP_2_TITLE', [], $currentLang) }}
                </h3>
                <p class="text-gray-600 leading-relaxed px-4 text-sm md:text-base">
                    {{ __('how-it-works.STEP_2_DESC', [], $currentLang) }}
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="step-group text-center">
                <div class="w-20 h-20 mx-auto bg-white border-4 border-brand-teal rounded-full flex items-center justify-center text-2xl font-black text-brand-teal shadow-lg mb-6 step-number group-hover:bg-brand-teal group-hover:text-white transition-all duration-300 relative">
                    3
                    <div class="absolute -bottom-2 {{ $direction === 'rtl' ? '-left-2' : '-right-2' }} w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center border border-gray-200">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-dark-navy mb-3 group-hover:text-brand-teal transition-colors">
                    {{ __('how-it-works.STEP_3_TITLE', [], $currentLang) }}
                </h3>
                <p class="text-gray-600 leading-relaxed px-4 text-sm md:text-base">
                    {{ __('how-it-works.STEP_3_DESC', [], $currentLang) }}
                </p>
            </div>
        </div>

        {{-- Read More Link --}}
        <div class="text-center mt-12 md:mt-16">
            <a href="#"
                class="inline-flex items-center text-brand-primary font-bold hover:text-dark-navy transition-colors text-base md:text-lg border-b-2 border-brand-primary/20 hover:border-brand-primary pb-1 group">
                {{ __('how-it-works.READ_FULL_DETAILS', [], $currentLang) }}
                <svg class="w-5 h-5 {{ $direction === 'rtl' ? 'mr-2 rotate-180 group-hover:-translate-x-1' : 'ml-2 group-hover:translate-x-1' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Success Stories Section --}}
<section class="py-16 md:py-20 bg-slate-50">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 w-full">
        <div class="text-center mb-12 md:mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-dark-navy mb-4">
                {{ __('how-it-works.SEC_SUCCESS_STORIES', [], $currentLang) }}
            </h2>
            <p class="text-gray-600 text-base md:text-lg max-w-2xl mx-auto">
                {{ __('how-it-works.TESTIMONIALS_DESC', [], $currentLang) }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 max-w-5xl mx-auto">
            {{-- Testimonial 1 --}}
            <div class="testimonial-card bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 {{ $direction === 'rtl' ? 'right-0' : 'left-0' }} w-1 h-full bg-brand-primary"></div>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-blue-50 text-brand-primary rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0">
                        TS
                    </div>
                    <div class="{{ $direction === 'rtl' ? 'mr-4' : 'ml-4' }} flex-1 min-w-0">
                        <h4 class="font-bold text-dark-navy text-base md:text-lg truncate">
                            {{ $currentLang === 'ar' ? 'شركة الحلول التقنية' : 'Tech Solutions Inc.' }}
                        </h4>
                        <span class="text-xs text-gray-500 uppercase tracking-wider bg-gray-100 px-2 py-1 rounded inline-block mt-1">
                            {{ __('how-it-works.INDUSTRY_IT', [], $currentLang) }}
                        </span>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <strong class="block text-xs text-gray-400 uppercase tracking-wider mb-1">
                            {{ __('how-it-works.PREVIOUS_CHALLENGE', [], $currentLang) }}
                        </strong>
                        <p class="text-gray-700 text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'الحاجة إلى مصممي واجهات مستخدم محترفين لمشروع مدته 3 أشهر.' 
                                : 'Need for senior UI designers for a 3-month project.' }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-100">
                        <strong class="block text-xs text-brand-primary uppercase tracking-wider mb-1">
                            {{ __('how-it-works.SOLUTION_WITH_TS', [], $currentLang) }}
                        </strong>
                        <p class="text-gray-900 font-medium text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'تم توظيف خبيرين من وكالة تصميم رائدة خلال 48 ساعة.' 
                                : 'Hired 2 experts from a leading design agency within 48 hours.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Testimonial 2 --}}
            <div class="testimonial-card bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 {{ $direction === 'rtl' ? 'right-0' : 'left-0' }} w-1 h-full bg-brand-secondary"></div>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-green-50 text-brand-secondary rounded-xl flex items-center justify-center font-bold text-lg flex-shrink-0">
                        MP
                    </div>
                    <div class="{{ $direction === 'rtl' ? 'mr-4' : 'ml-4' }} flex-1 min-w-0">
                        <h4 class="font-bold text-dark-navy text-base md:text-lg truncate">
                            {{ $currentLang === 'ar' ? 'التسويق المحترف' : 'Marketing Pro' }}
                        </h4>
                        <span class="text-xs text-gray-500 uppercase tracking-wider bg-gray-100 px-2 py-1 rounded inline-block mt-1">
                            {{ __('how-it-works.INDUSTRY_MARKETING', [], $currentLang) }}
                        </span>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <strong class="block text-xs text-gray-400 uppercase tracking-wider mb-1">
                            {{ __('how-it-works.PREVIOUS_CHALLENGE', [], $currentLang) }}
                        </strong>
                        <p class="text-gray-700 text-sm leading-relaxed">
                            {{ $currentLang === 'ar' 
                                ? 'فريق إبداعي عاطل خلال الموسم المنخفض.' 
                                : 'Idle creative team during off-season.' }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-100">
                        <strong class="block text-xs text-brand-secondary uppercase tracking-wider mb-1">
                            {{ __('how-it-works.SOLUTION_WITH_TS', [], $currentLang) }}
                        </strong>
                        <p class="text-gray-900 font-medium text-sm leading-relaxed">
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
<section class="py-20 md:py-24 bg-gradient-to-br from-brand-primary to-dark-navy text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 relative z-10 max-w-[1920px]">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-black mb-6">
            {{ __('how-it-works.SEC_FINAL_CTA', [], $currentLang) }}
        </h2>
        <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-2xl mx-auto font-light leading-relaxed">
            {{ __('how-it-works.FINAL_CTA_DESC', [], $currentLang) }}
        </p>
        <a href="{{ route('register.company', ['type' => 'supplier']) }}"
            class="inline-flex items-center bg-white text-brand-primary px-8 md:px-10 py-4 rounded-full font-bold text-base md:text-lg hover:bg-gray-50 hover:scale-105 transition-all duration-300 shadow-2xl group">
            {{ __('how-it-works.BTN_START_NOW', [], $currentLang) }}
            <svg class="w-5 h-5 {{ $direction === 'rtl' ? 'mr-2 rotate-180 group-hover:-translate-x-1' : 'ml-2 group-hover:translate-x-1' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
</section>
@endsection