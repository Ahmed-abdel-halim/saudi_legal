@extends('layouts.app')

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush

@section('content')
<section class="bg-white py-24">
    <div class="w-full px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-dark-navy mb-4">@lang('faq.FAQ_TITLE')</h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
            @lang('faq.FAQ_SUBTITLE')
        </p>
    </div>
</section>

<section class="py-24 bg-slate-50">
    <div class="w-full px-4 max-w-5xl mx-auto">
        <div class="space-y-6">
            
            <!-- Question 1 -->
            <div class="bg-white rounded-lg shadow-sm" x-data="{ open: true }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-xl font-bold text-dark-navy">@lang('faq.FAQ_Q1')</span>
                    <span class="text-brand-teal text-2xl font-bold" x-text="open ? '-' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-gray-600 leading-relaxed" x-transition>
                    <p>@lang('faq.FAQ_A1')</p>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="bg-white rounded-lg shadow-sm" x-data="{ open: false }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-xl font-bold text-dark-navy">@lang('faq.FAQ_Q2')</span>
                    <span class="text-brand-teal text-2xl font-bold" x-text="open ? '-' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-gray-600 leading-relaxed" x-transition style="display: none;">
                    <p>@lang('faq.FAQ_A2')</p>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="bg-white rounded-lg shadow-sm" x-data="{ open: false }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-xl font-bold text-dark-navy">@lang('faq.FAQ_Q3')</span>
                    <span class="text-brand-teal text-2xl font-bold" x-text="open ? '-' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-gray-600 leading-relaxed" x-transition style="display: none;">
                    <p>@lang('faq.FAQ_A3')</p>
                </div>
            </div>
            
            <!-- Question 4 -->
            <div class="bg-white rounded-lg shadow-sm" x-data="{ open: false }">
                <button @click="open = !open" class="flex justify-between items-center w-full px-6 py-5 text-left rtl:text-right focus:outline-none">
                    <span class="text-xl font-bold text-dark-navy">@lang('faq.FAQ_Q4')</span>
                    <span class="text-brand-teal text-2xl font-bold" x-text="open ? '-' : '+'"></span>
                </button>
                <div x-show="open" class="px-6 pb-5 text-gray-600 leading-relaxed" x-transition style="display: none;">
                    <p>@lang('faq.FAQ_A4')</p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
