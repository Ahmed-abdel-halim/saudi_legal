@extends('layouts.app')

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush

@section('content')
<section class="bg-dark-navy py-24 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="w-full px-4 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-black text-white mb-4 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">@lang('faq.FAQ_TITLE')</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
            @lang('faq.FAQ_SUBTITLE')
        </p>
    </div>
</section>

<section class="py-20 bg-dark-navy relative">
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="w-full px-4 max-w-5xl mx-auto relative z-10">
        <div class="space-y-6">
            
            <!-- Question 1 -->
            <div class="expert-card bg-slate-900/40 p-1 border border-white/5" x-data="{ open: true }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-lg md:text-xl font-bold text-white group-hover:text-brand-green transition-colors">@lang('faq.FAQ_Q1')</span>
                    <span class="text-brand-green text-2xl font-black" x-text="open ? '−' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-slate-400 text-sm md:text-base leading-relaxed border-t border-white/5 pt-4" x-transition>
                    <p>@lang('faq.FAQ_A1')</p>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="expert-card bg-slate-900/40 p-1 border border-white/5" x-data="{ open: false }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-lg md:text-xl font-bold text-white group-hover:text-brand-green transition-colors">@lang('faq.FAQ_Q2')</span>
                    <span class="text-brand-green text-2xl font-black" x-text="open ? '−' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-slate-400 text-sm md:text-base leading-relaxed border-t border-white/5 pt-4" x-transition style="display: none;">
                    <p>@lang('faq.FAQ_A2')</p>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="expert-card bg-slate-900/40 p-1 border border-white/5" x-data="{ open: false }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-lg md:text-xl font-bold text-white group-hover:text-brand-green transition-colors">@lang('faq.FAQ_Q3')</span>
                    <span class="text-brand-green text-2xl font-black" x-text="open ? '−' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-slate-400 text-sm md:text-base leading-relaxed border-t border-white/5 pt-4" x-transition style="display: none;">
                    <p>@lang('faq.FAQ_A3')</p>
                </div>
            </div>
            
            <!-- Question 4 -->
            <div class="expert-card bg-slate-900/40 p-1 border border-white/5" x-data="{ open: false }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-lg md:text-xl font-bold text-white group-hover:text-brand-green transition-colors">@lang('faq.FAQ_Q4')</span>
                    <span class="text-brand-green text-2xl font-black" x-text="open ? '−' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-slate-400 text-sm md:text-base leading-relaxed border-t border-white/5 pt-4" x-transition style="display: none;">
                    <p>@lang('faq.FAQ_A4')</p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
