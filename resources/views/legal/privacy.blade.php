@extends('layouts.app')

@section('content')
<div class="bg-dark-navy text-white py-16">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold mb-4">@lang('legal.AUTH_PRIVACY')</h1>
        <p class="text-gray-400">@lang('legal.LEGAL_PRIVACY_SUBTITLE')</p>
    </div>
</div>

<div class="py-16 bg-slate-50">
    <div class="container mx-auto px-6 max-w-4xl bg-white p-10 rounded-xl shadow-sm leading-relaxed text-gray-700 space-y-6">
        
        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">1. @lang('legal.LEGAL_PRIVACY_SECTION_1')</h2>
            <p>@lang('legal.LEGAL_PRIVACY_COLLECT_DESC')</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>@lang('legal.LEGAL_PRIVACY_ITEM_1')</li>
                <li>@lang('legal.LEGAL_PRIVACY_ITEM_2')</li>
                <li>@lang('legal.LEGAL_PRIVACY_ITEM_3')</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">2. @lang('legal.LEGAL_PRIVACY_SECTION_2')</h2>
            <p>@lang('legal.LEGAL_PRIVACY_USE_DESC')</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>@lang('legal.LEGAL_PRIVACY_USE_ITEM_1')</li>
                <li>@lang('legal.LEGAL_PRIVACY_USE_ITEM_2')</li>
                <li>@lang('legal.LEGAL_PRIVACY_USE_ITEM_3')</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">3. @lang('legal.LEGAL_PRIVACY_SECTION_3')</h2>
            <p>@lang('legal.LEGAL_PRIVACY_SHARE_DESC')</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>@lang('legal.LEGAL_PRIVACY_SHARE_ITEM_1')</li>
                <li>@lang('legal.LEGAL_PRIVACY_SHARE_ITEM_2')</li>
                <li>@lang('legal.LEGAL_PRIVACY_SHARE_ITEM_3')</li>
            </ul>
        </section>
        
        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">4. @lang('legal.LEGAL_PRIVACY_SECTION_4')</h2>
            <p>@lang('legal.LEGAL_PRIVACY_SECURITY_DESC')</p>
        </section>
    </div>
</div>
@endsection
