@extends('layouts.app')

@section('content')
<div class="bg-dark-navy text-white py-16">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl font-bold mb-4">@lang('legal.MSA_TITLE')</h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            @lang('legal.MSA_SUBTITLE')
        </p>
    </div>
</div>

<div class="py-16 bg-slate-50">
    <div class="container mx-auto px-6 max-w-4xl">
        
        <div class="bg-yellow-50 border-r-4 border-l-0 rtl:border-r-0 rtl:border-l-4 border-yellow-400 p-4 mb-8 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="{{ app()->getLocale() == 'ar' ? 'mr-3' : 'ml-3' }}">
                    <p class="text-sm text-yellow-700">
                        @lang('legal.MSA_DISCLAIMER')
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white p-10 rounded-xl shadow-lg border border-gray-200 text-gray-800 font-serif leading-loose">
            <h2 class="text-2xl font-bold text-center mb-8 underline">@lang('legal.MSA_AGREEMENT_TITLE')</h2>
            
            <p class="mb-6"><strong>@lang('legal.MSA_PARTIES'):</strong></p>
            <ol class="list-decimal list-inside space-y-2 mb-6">
                <li><strong>@lang('legal.MSA_PARTY_1'):</strong> [@lang('legal.MSA_SUPPLIER_NAME')] ("@lang('legal.MSA_SUPPLIER_SHORT')")</li>
                <li><strong>@lang('legal.MSA_PARTY_2'):</strong> [@lang('legal.MSA_CLIENT_NAME')] ("@lang('legal.MSA_CLIENT_SHORT')")</li>
                <li><strong>@lang('legal.MSA_PARTY_3'):</strong> @lang('legal.PLATFORM_NAME') ("@lang('legal.MSA_PLATFORM_SHORT')" - @lang('legal.MSA_PLATFORM_ROLE'))</li>
            </ol>

            <h3 class="font-bold text-lg mb-2">1. @lang('legal.MSA_SECTION_1_TITLE')</h3>
            <p class="mb-4">@lang('legal.MSA_SECTION_1_DESC')</p>

            <h3 class="font-bold text-lg mb-2">2. @lang('legal.MSA_SECTION_2_TITLE')</h3>
            <p class="mb-4">@lang('legal.MSA_SECTION_2_DESC')</p>

            <h3 class="font-bold text-lg mb-2">3. @lang('legal.MSA_SECTION_3_TITLE')</h3>
            <p class="mb-4">@lang('legal.MSA_SECTION_3_DESC')</p>

            <h3 class="font-bold text-lg mb-2">4. @lang('legal.MSA_SECTION_4_TITLE')</h3>
            <p class="mb-4">@lang('legal.MSA_SECTION_4_DESC')</p>

            <h3 class="font-bold text-lg mb-2">5. @lang('legal.MSA_SECTION_5_TITLE')</h3>
            <p class="mb-4">@lang('legal.MSA_SECTION_5_DESC')</p>

            <div class="mt-10 pt-6 border-t border-gray-200 text-center text-gray-500 italic text-sm">
                @lang('legal.MSA_END_OF_TEMPLATE')
            </div>
        </div>
    </div>
</div>
@endsection
