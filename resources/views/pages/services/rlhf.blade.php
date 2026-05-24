@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-slate-900 text-white pt-20 pb-16">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-teal/10 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 max-w-7xl relative z-10 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand-primary/30 bg-brand-primary/10 text-brand-primary text-xs font-semibold mb-6">
            <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
            Supervised Fine-Tuning
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-none mb-6">
            {{ __('pages.RLHF_TITLE') }}
        </h1>
        <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
            {{ __('pages.RLHF_SUBTITLE') }}
        </p>
    </div>
</div>

<div class="bg-white py-20">
    <div class="container mx-auto px-6 max-w-7xl">
        
        {{-- Section 1: How it works --}}
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark-navy mb-4">{{ __('pages.RLHF_HOW_IT_WORKS') }}</h2>
            <div class="w-16 h-1 bg-brand-primary mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
            
            <div class="bg-slate-50 border border-gray-100 rounded-3xl p-8 text-center relative group">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-brand-primary text-white font-extrabold flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition-transform duration-300">
                    1
                </div>
                <h3 class="text-xl font-bold text-dark-navy mt-4 mb-3">{{ __('pages.RLHF_STEP_1_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ __('pages.RLHF_STEP_1_DESC') }}
                </p>
            </div>

            <div class="bg-slate-50 border border-gray-100 rounded-3xl p-8 text-center relative group">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-brand-primary text-white font-extrabold flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition-transform duration-300">
                    2
                </div>
                <h3 class="text-xl font-bold text-dark-navy mt-4 mb-3">{{ __('pages.RLHF_STEP_2_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ __('pages.RLHF_STEP_2_DESC') }}
                </p>
            </div>

            <div class="bg-slate-50 border border-gray-100 rounded-3xl p-8 text-center relative group">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-brand-primary text-white font-extrabold flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition-transform duration-300">
                    3
                </div>
                <h3 class="text-xl font-bold text-dark-navy mt-4 mb-3">{{ __('pages.RLHF_STEP_3_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ __('pages.RLHF_STEP_3_DESC') }}
                </p>
            </div>

        </div>

        {{-- Use cases --}}
        <div class="bg-slate-50 border border-gray-200 rounded-3xl p-8 lg:p-12">
            <h2 class="text-2xl lg:text-3xl font-bold text-dark-navy mb-8 text-center">{{ __('pages.RLHF_USE_CASE_TITLE') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-dark-navy mb-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                        Dialect & Language Tuning
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ __('pages.RLHF_USE_CASE_1') }}
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-dark-navy mb-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                        Code Validation
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ __('pages.RLHF_USE_CASE_2') }}
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-dark-navy mb-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                        Professional Domain Experts
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ __('pages.RLHF_USE_CASE_3') }}
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
