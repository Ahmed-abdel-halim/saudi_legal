@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-dark-navy text-white pt-24 pb-16 border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl font-black mb-4 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">@lang('legal.AUTH_TERMS')</h1>
        <p class="text-slate-400 font-medium">@lang('legal.LAST_UPDATED'): {{ date("Y-m-d") }}</p>
    </div>
</div>

<div class="py-16 bg-dark-navy">
    <div class="container mx-auto px-6 max-w-4xl expert-card p-10 leading-relaxed text-slate-300 space-y-8">
        
        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">1. @lang('legal.LEGAL_SECTION_INTRODUCTION')</h2>
            <p class="text-slate-400">@lang('legal.LEGAL_TERMS_INTRO')</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">2. @lang('legal.LEGAL_SECTION_RELATIONSHIP') (B2B Model)</h2>
            <p class="text-slate-400 mb-4">@lang('legal.LEGAL_RELATIONSHIP_DESC')</p>
            <ul class="list-disc list-inside space-y-2 text-slate-400">
                <li><strong class="text-brand-green">@lang('legal.LEGAL_SUPPLIER_TITLE'):</strong> @lang('legal.LEGAL_SUPPLIER_DESC')</li>
                <li><strong class="text-brand-green">@lang('legal.LEGAL_REQUESTER_TITLE'):</strong> @lang('legal.LEGAL_REQUESTER_DESC')</li>
                <li><strong class="text-brand-green">@lang('legal.LEGAL_EXPERT_TITLE'):</strong> @lang('legal.LEGAL_EXPERT_DESC')</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">3. @lang('legal.LEGAL_SECTION_OBLIGATIONS')</h2>
            <p class="text-slate-400 mb-3"><strong class="text-brand-green">@lang('legal.LEGAL_OBLIGATION_SUPPLIER'):</strong> @lang('legal.LEGAL_OBLIGATION_SUPPLIER_DESC')</p>
            <p class="text-slate-400"><strong class="text-brand-green">@lang('legal.LEGAL_OBLIGATION_REQUESTER'):</strong> @lang('legal.LEGAL_OBLIGATION_REQUESTER_DESC')</p>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">4. @lang('legal.LEGAL_SECTION_IP')</h2>
            <p class="text-slate-400">@lang('legal.LEGAL_IP_DESC')</p>
        </section>
        
        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">5. @lang('legal.LEGAL_SECTION_PAYMENT')</h2>
            <p class="text-slate-400">@lang('legal.LEGAL_PAYMENT_DESC')</p>
        </section>
    </div>
</div>
@endsection
