@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-slate-900 text-white pt-20 pb-16">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 max-w-7xl relative z-10 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand-cyan/30 bg-brand-cyan/10 text-brand-cyan text-xs font-semibold mb-6">
            <span class="w-2 h-2 rounded-full bg-brand-cyan animate-pulse"></span>
            Real-time Verification
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-none mb-6">
            {{ __('pages.HITL_TITLE') }}
        </h1>
        <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
            {{ __('pages.HITL_SUBTITLE') }}
        </p>
    </div>
</div>

<div class="bg-white py-20">
    <div class="container mx-auto px-6 max-w-7xl">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-20">
            <div class="lg:col-span-7">
                <h2 class="text-3xl font-bold text-dark-navy mb-6">{{ __('pages.HITL_TITLE') }}</h2>
                <p class="text-gray-600 leading-relaxed text-lg mb-8">
                    {{ __('pages.HITL_DESC') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('contact') }}" class="bg-brand-primary text-white hover:bg-opacity-90 px-8 py-4 rounded-full font-bold shadow-lg text-center transition-all">
                        {{ __('pages.GET_STARTED') }}
                    </a>
                </div>
            </div>
            
            <div class="lg:col-span-5 relative">
                <div class="absolute -inset-4 bg-brand-cyan/10 rounded-3xl transform rotate-3"></div>
                <div class="relative bg-slate-900 text-white rounded-3xl p-8 border border-white/10 shadow-xl font-mono text-xs">
                    <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                        <span class="text-brand-cyan font-bold">Inference Queue</span>
                        <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold">Confidence < 75%</span>
                    </div>
                    <div class="space-y-3">
                        <div class="bg-white/5 p-3 rounded-lg border border-white/5">
                            <div class="text-gray-400 mb-1">Input Prompt:</div>
                            <div class="text-white">"اكتب عقد إيجار موثق متوافق مع نظام المعاملات المدنية"</div>
                        </div>
                        <div class="bg-white/5 p-3 rounded-lg border border-white/5">
                            <div class="text-gray-400 mb-1">Model Inference:</div>
                            <div class="text-white font-serif">"عقد الإيجار يخضع لنظام..." <span class="text-red-400 font-bold">[Confidence: 62%]</span></div>
                        </div>
                        <div class="bg-teal-500/10 p-3 rounded-lg border border-teal-500/20 flex items-center justify-between">
                            <span class="text-teal-400 font-bold">✔️ Routed to Legal Expert for verification</span>
                            <span class="animate-pulse w-2.5 h-2.5 rounded-full bg-teal-400"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-gray-100 my-16">

        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-dark-navy mb-4">{{ __('pages.HITL_FEATURES_TITLE') }}</h2>
            <div class="w-16 h-1 bg-brand-cyan mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- Feature 1 --}}
            <div class="bg-slate-50 border border-gray-100 rounded-3xl p-8 hover:shadow-md transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-brand-cyan flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.HITL_FEAT_1_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ __('pages.HITL_FEAT_1_DESC') }}
                </p>
            </div>

            {{-- Feature 2 --}}
            <div class="bg-slate-50 border border-gray-100 rounded-3xl p-8 hover:shadow-md transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-brand-primary flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.HITL_FEAT_2_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ __('pages.HITL_FEAT_2_DESC') }}
                </p>
            </div>

            {{-- Feature 3 --}}
            <div class="bg-slate-50 border border-gray-100 rounded-3xl p-8 hover:shadow-md transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-teal-100 text-brand-teal flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-dark-navy mb-3">{{ __('pages.HITL_FEAT_3_TITLE') }}</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    {{ __('pages.HITL_FEAT_3_DESC') }}
                </p>
            </div>

        </div>

    </div>
</div>
@endsection
