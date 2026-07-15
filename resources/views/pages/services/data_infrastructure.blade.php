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
            Enterprise Core
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
            {{ __('pages.INFRA_TITLE') }}
        </h1>
        <p class="text-base md:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
            {{ __('pages.INFRA_SUBTITLE') }}
        </p>
    </div>
</div>

<div class="bg-dark-navy py-20">
    <div class="container mx-auto px-6 max-w-7xl">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-20">
            <div class="lg:col-span-6 relative">
                <div class="absolute -inset-4 bg-brand-green/5 rounded-3xl transform -rotate-3"></div>
                <div class="relative bg-slate-900/60 text-white rounded-3xl p-8 border border-white/5 shadow-xl font-mono text-xs backdrop-blur-md">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3 mb-4">
                        <span class="text-brand-green font-bold">Radiif Data Flow Engine</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-teal-400 border border-white/10">1</span>
                            <span class="text-slate-300">Ingest: API Endpoint (JSON / JSONL)</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-indigo-400 border border-white/10">2</span>
                            <span class="text-slate-300">Orchestrate: Multi-expert consensus voting</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-violet-400 border border-white/10">3</span>
                            <span class="text-slate-300">Deliver: S3 bucket syncing / Webhook push</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <h2 class="text-3xl font-black text-white mb-6">{{ __('pages.INFRA_TITLE') }}</h2>
                <p class="text-slate-400 leading-relaxed text-lg mb-8">
                    {{ __('pages.INFRA_DESC') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('pages.api_docs') }}" class="inline-block bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black px-8 py-4 rounded-full shadow-lg shadow-brand-green/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 text-center">
                        {{ __('pages.DOCUMENTATION') }}
                    </a>
                </div>
            </div>
        </div>

        <hr class="border-white/5 my-16">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- Card 1 --}}
            <div class="expert-card p-8 hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-brand-green/10 text-brand-green flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">{{ __('pages.INFRA_FEAT_1_TITLE') }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('pages.INFRA_FEAT_1_DESC') }}
                </p>
            </div>

            {{-- Card 2 --}}
            <div class="expert-card p-8 hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-brand-green/10 text-brand-green flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">{{ __('pages.INFRA_FEAT_2_TITLE') }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('pages.INFRA_FEAT_2_DESC') }}
                </p>
            </div>

            {{-- Card 3 --}}
            <div class="expert-card p-8 hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl bg-brand-green/10 text-brand-green flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">{{ __('pages.INFRA_FEAT_3_TITLE') }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('pages.INFRA_FEAT_3_DESC') }}
                </p>
            </div>

        </div>

    </div>
</div>
@endsection
