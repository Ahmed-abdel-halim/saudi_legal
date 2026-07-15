@extends('layouts.app')

@section('content')
<section class="bg-dark-navy py-20 relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="container mx-auto px-4 text-center mb-16 relative z-10">
        <span class="text-brand-green font-bold text-xs uppercase tracking-widest bg-brand-green/10 px-3.5 py-1.5 rounded-full border border-brand-green/20 inline-block mb-4">
            @lang('benefits.NAV_BENEFITS')
        </span>
        <h1 class="text-4xl md:text-5xl font-black text-white mt-2 mb-4 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">@lang('benefits.BENEFITS_TITLE')</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">@lang('benefits.BENEFITS_SUBTITLE')</p>
    </div>

    <div class="container mx-auto px-4 relative z-10 max-w-[1400px]">
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Supplier Benefits -->
            <div class="expert-card p-8 hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 bg-brand-green/10 text-brand-green rounded-xl flex items-center justify-center text-2xl mb-6 shadow-md shadow-brand-green/5">
                    🏢
                </div>
                <h3 class="text-2xl font-black text-white mb-6 border-b border-white/5 pb-2">@lang('benefits.BENEFITS_SUPPLIER_HEAD')</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_SUPPLIER_1')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_SUPPLIER_2')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_SUPPLIER_3')</span>
                    </li>
                </ul>
            </div>

            <!-- Requester Benefits -->
            <div class="expert-card p-8 hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 bg-brand-green/10 text-brand-green rounded-xl flex items-center justify-center text-2xl mb-6 shadow-md shadow-brand-green/5">
                    🤝
                </div>
                <h3 class="text-2xl font-black text-white mb-6 border-b border-white/5 pb-2">@lang('benefits.BENEFITS_REQUESTER_HEAD')</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_REQUESTER_1')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_REQUESTER_2')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_REQUESTER_3')</span>
                    </li>
                </ul>
            </div>

             <!-- Expert Benefits -->
             <div class="expert-card p-8 hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 bg-brand-green/10 text-brand-green rounded-xl flex items-center justify-center text-2xl mb-6 shadow-md shadow-brand-green/5">
                   👤
                </div>
                <h3 class="text-2xl font-black text-white mb-6 border-b border-white/5 pb-2">@lang('benefits.BENEFITS_EXPERT_HEAD')</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_EXPERT_1')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_EXPERT_2')</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-brand-green mt-1 shrink-0 {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-slate-400 text-sm leading-relaxed">@lang('benefits.BENEFITS_EXPERT_3')</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>
@endsection
