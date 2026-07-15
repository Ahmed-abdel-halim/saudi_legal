@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-dark-navy text-white pt-24 pb-16 border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    {{-- Glow background --}}
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 max-w-7xl relative z-10 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand-green/20 bg-brand-green/10 text-brand-green text-xs font-semibold mb-6">
            <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
            Supervised Fine-Tuning
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
            {{ __('pages.RLHF_TITLE') }}
        </h1>
        <p class="text-base md:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
            {{ __('pages.RLHF_SUBTITLE') }}
        </p>
    </div>
</div>

<div class="bg-dark-navy py-20">
    <div class="container mx-auto px-6 max-w-7xl">
        
        {{-- Section 1: How it works --}}
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-white mb-4">{{ __('pages.RLHF_HOW_IT_WORKS') }}</h2>
            <div class="w-16 h-1 bg-brand-green mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
            
            <div class="expert-card p-8 text-center relative group pt-12">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black flex items-center justify-center text-lg shadow-lg shadow-brand-green/20 group-hover:scale-110 transition-transform duration-300">
                    1
                </div>
                <h3 class="text-xl font-bold text-white mb-3">{{ __('pages.RLHF_STEP_1_TITLE') }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('pages.RLHF_STEP_1_DESC') }}
                </p>
            </div>

            <div class="expert-card p-8 text-center relative group pt-12">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black flex items-center justify-center text-lg shadow-lg shadow-brand-green/20 group-hover:scale-110 transition-transform duration-300">
                    2
                </div>
                <h3 class="text-xl font-bold text-white mb-3">{{ __('pages.RLHF_STEP_2_TITLE') }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('pages.RLHF_STEP_2_DESC') }}
                </p>
            </div>

            <div class="expert-card p-8 text-center relative group pt-12">
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black flex items-center justify-center text-lg shadow-lg shadow-brand-green/20 group-hover:scale-110 transition-transform duration-300">
                    3
                </div>
                <h3 class="text-xl font-bold text-white mb-3">{{ __('pages.RLHF_STEP_3_TITLE') }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('pages.RLHF_STEP_3_DESC') }}
                </p>
            </div>

        </div>

        {{-- Use cases --}}
        <div class="expert-card bg-slate-900/40 p-8 lg:p-12 relative overflow-hidden backdrop-blur-md">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-brand-green/10 rounded-full blur-2xl pointer-events-none"></div>
            <h2 class="text-2xl lg:text-3xl font-black text-white mb-8 text-center">{{ __('pages.RLHF_USE_CASE_TITLE') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="bg-slate-950/60 border border-white/5 rounded-2xl p-6 shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <h3 class="font-bold text-white mb-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-green shadow-green-glow"></span>
                        Dialect & Language Tuning
                    </h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ __('pages.RLHF_USE_CASE_1') }}
                    </p>
                </div>

                <div class="bg-slate-950/60 border border-white/5 rounded-2xl p-6 shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <h3 class="font-bold text-white mb-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-green shadow-green-glow"></span>
                        Code Validation
                    </h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ __('pages.RLHF_USE_CASE_2') }}
                    </p>
                </div>

                <div class="bg-slate-950/60 border border-white/5 rounded-2xl p-6 shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <h3 class="font-bold text-white mb-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-green shadow-green-glow"></span>
                        Professional Domain Experts
                    </h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ __('pages.RLHF_USE_CASE_3') }}
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
