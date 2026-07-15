@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-dark-navy text-white pt-24 pb-16 border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl font-black mb-4 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">@lang('legal.AUTH_PRIVACY')</h1>
        <p class="text-slate-400 font-medium">@lang('legal.LEGAL_PRIVACY_SUBTITLE')</p>
    </div>
</div>

<div class="py-16 bg-dark-navy">
    <div class="container mx-auto px-6 max-w-4xl expert-card p-10 leading-relaxed text-slate-300 space-y-8">
        
        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">1. @lang('legal.LEGAL_PRIVACY_SECTION_1')</h2>
            <p class="text-slate-400 mb-3">@lang('legal.LEGAL_PRIVACY_COLLECT_DESC')</p>
            <ul class="list-disc list-inside space-y-2 text-slate-400">
                <li>@lang('legal.LEGAL_PRIVACY_ITEM_1')</li>
                <li>@lang('legal.LEGAL_PRIVACY_ITEM_2')</li>
                <li>@lang('legal.LEGAL_PRIVACY_ITEM_3')</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">2. @lang('legal.LEGAL_PRIVACY_SECTION_2')</h2>
            <p class="text-slate-400 mb-3">@lang('legal.LEGAL_PRIVACY_USE_DESC')</p>
            <ul class="list-disc list-inside space-y-2 text-slate-400">
                <li>@lang('legal.LEGAL_PRIVACY_USE_ITEM_1')</li>
                <li>@lang('legal.LEGAL_PRIVACY_USE_ITEM_2')</li>
                <li>@lang('legal.LEGAL_PRIVACY_USE_ITEM_3')</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">3. @lang('legal.LEGAL_PRIVACY_SECTION_3')</h2>
            <p class="text-slate-400 mb-3">@lang('legal.LEGAL_PRIVACY_SHARE_DESC')</p>
            <ul class="list-disc list-inside space-y-2 text-slate-400">
                <li>@lang('legal.LEGAL_PRIVACY_SHARE_ITEM_1')</li>
                <li>@lang('legal.LEGAL_PRIVACY_SHARE_ITEM_2')</li>
                <li>@lang('legal.LEGAL_PRIVACY_SHARE_ITEM_3')</li>
            </ul>
        </section>
        
        <section>
            <h2 class="text-2xl font-black text-white mb-4 border-b border-white/5 pb-2">4. @lang('legal.LEGAL_PRIVACY_SECTION_4')</h2>
            <p class="text-slate-400">@lang('legal.LEGAL_PRIVACY_SECURITY_DESC')</p>
        </section>
    </div>
</div>
@endsection
