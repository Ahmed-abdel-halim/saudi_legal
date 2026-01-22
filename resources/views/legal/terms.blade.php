@extends('layouts.app')

@section('content')
<div class="bg-dark-navy text-white py-16">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold mb-4">@lang('legal.AUTH_TERMS')</h1>
        <p class="text-gray-400">@lang('legal.LAST_UPDATED'): {{ date("Y-m-d") }}</p>
    </div>
</div>

<div class="py-16 bg-slate-50">
    <div class="container mx-auto px-6 max-w-4xl bg-white p-10 rounded-xl shadow-sm leading-relaxed text-gray-700 space-y-6">
        
        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">1. @lang('legal.LEGAL_SECTION_INTRODUCTION')</h2>
            <p>@lang('legal.LEGAL_TERMS_INTRO')</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">2. @lang('legal.LEGAL_SECTION_RELATIONSHIP') (B2B Model)</h2>
            <p>@lang('legal.LEGAL_RELATIONSHIP_DESC')</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li><strong>@lang('legal.LEGAL_SUPPLIER_TITLE'):</strong> @lang('legal.LEGAL_SUPPLIER_DESC')</li>
                <li><strong>@lang('legal.LEGAL_REQUESTER_TITLE'):</strong> @lang('legal.LEGAL_REQUESTER_DESC')</li>
                <li><strong>@lang('legal.LEGAL_EXPERT_TITLE'):</strong> @lang('legal.LEGAL_EXPERT_DESC')</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">3. @lang('legal.LEGAL_SECTION_OBLIGATIONS')</h2>
            <p><strong>@lang('legal.LEGAL_OBLIGATION_SUPPLIER'):</strong> @lang('legal.LEGAL_OBLIGATION_SUPPLIER_DESC')</p>
            <p class="mt-2"><strong>@lang('legal.LEGAL_OBLIGATION_REQUESTER'):</strong> @lang('legal.LEGAL_OBLIGATION_REQUESTER_DESC')</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">4. @lang('legal.LEGAL_SECTION_IP')</h2>
            <p>@lang('legal.LEGAL_IP_DESC')</p>
        </section>
        
        <section>
            <h2 class="text-2xl font-bold text-dark-navy mb-4">5. @lang('legal.LEGAL_SECTION_PAYMENT')</h2>
            <p>@lang('legal.LEGAL_PAYMENT_DESC')</p>
        </section>
    </div>
</div>
@endsection
