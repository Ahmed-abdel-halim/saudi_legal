@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    .sticky-filter {
        position: sticky;
        top: 100px;
    }

    @media (max-width: 768px) {
        .sticky-filter {
            position: relative;
            top: 0;
        }
    }
</style>
@endpush

@section('content')
{{-- Hero --}}
<div class="bg-dark-navy text-white pt-20 pb-16">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 text-center w-full">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-6">
            {{ __('suppliers.SUPPLIERS_HERO_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-gray-300 max-w-2xl mx-auto text-lg md:text-xl">
            {{ __('suppliers.SUPPLIERS_HERO_DESC', [], $currentLang) }}
        </p>
    </div>
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 w-full text-center mt-6">
        <span class="inline-flex items-center gap-2 py-2 px-4 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-sm font-semibold text-brand-teal">
            ★ {{ str_replace('[COUNT]', $companies->count(), __('suppliers.SUPPLIERS_DISPLAY_COUNT', [], $currentLang)) }}
        </span>
    </div>
</div>

{{-- Main --}}
<div class="py-12 md:py-20 bg-slate-light min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 w-full">
        <div class="flex flex-col md:flex-row gap-6 md:gap-10">

            {{-- Filters --}}
            <aside class="w-full md:w-1/4">
                <form action="{{ route('suppliers.browse') }}" method="GET" class="bg-white p-6 rounded-xl shadow-sm sticky-filter">
                    <h3 class="text-xl font-bold text-dark-navy mb-5 border-b pb-3">
                        {{ __('suppliers.SUPPLIERS_FILTER_TITLE', [], $currentLang) }}
                    </h3>

                    {{-- Industry --}}
                    <div class="mb-6">
                        <label for="industry" class="font-semibold text-gray-800 mb-3 block">
                            {{ __('suppliers.SUPPLIERS_FILTER_INDUSTRY', [], $currentLang) }}
                        </label>
                        <select id="industry" name="industry" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-teal">
                            <option value="">{{ __('suppliers.SUPPLIERS_ALL_INDUSTRIES', [], $currentLang) }}</option>
                            @foreach($industries as $industry)
                            <option value="{{ $industry }}" {{ $industryFilter === $industry ? 'selected' : '' }}>
                                {{ $industry }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Size --}}
                    <div class="mb-6">
                        <label for="size" class="font-semibold text-gray-800 mb-3 block">
                            {{ __('suppliers.SUPPLIERS_FILTER_SIZE', [], $currentLang) }}
                        </label>
                        <select id="size" name="size" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-teal">
                            <option value="">{{ __('suppliers.SUPPLIERS_ALL_SIZES', [], $currentLang) }}</option>
                            @foreach($sizes as $size)
                            <option value="{{ $size }}" {{ $sizeFilter === $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-brand-magenta text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                        {{ __('suppliers.BTN_APPLY_FILTERS', [], $currentLang) }}
                    </button>
                    @if(!empty($industryFilter) || !empty($sizeFilter))
                    <a href="{{ route('suppliers.browse') }}" class="block text-center text-sm text-gray-600 mt-3 hover:underline">
                        {{ __('suppliers.BTN_CANCEL_FILTERS', [], $currentLang) }}
                    </a>
                    @endif
                </form>
            </aside>

            {{-- List --}}
            <main class="w-full md:w-3/4">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold text-dark-navy">
                        {{ str_replace('[COUNT]', $companies->count(), __('suppliers.SUPPLIERS_DISPLAY_COUNT', [], $currentLang)) }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($companies as $company)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform transform hover:-translate-y-1 hover:shadow-teal-glow border border-gray-100">
                        <div class="p-5">
                            <div class="flex items-center {{ $direction === 'rtl' ? 'space-x-reverse' : '' }} space-x-3 mb-4">
                                <img src="{{ $company->company_logo }}"
                                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($company->name) }}&background=E2E8F0&color=334155'"
                                    alt="{{ $company->name }}"
                                    class="w-12 h-12 rounded-full flex-shrink-0 bg-gray-50 object-cover border border-gray-100">
                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-dark-navy truncate" title="{{ $company->name }}">
                                        {{ $company->name }}
                                    </h3>
                                    <span class="text-sm text-brand-teal font-semibold">
                                        ★ {{ number_format($company->avg_rating ?? 0, 1) }}
                                    </span>
                                </div>
                            </div>

                            <p class="text-sm text-gray-500 mb-1">
                                {{ __('suppliers.SUPPLIERS_CARD_INDUSTRY', [], $currentLang) }}
                                <span class="text-gray-700 font-medium">{{ $company->industry ?? ($direction === 'rtl' ? 'غير محدد' : 'Not specified') }}</span>
                            </p>
                            <p class="text-sm text-gray-500 mb-4">
                                {{ __('suppliers.SUPPLIERS_CARD_EXPERTS', [], $currentLang) }}
                                <span class="text-gray-700 font-medium">
                                    {{ $company->service_count }} {{ __('suppliers.SUPPLIERS_CARD_EXPERT_AVAILABLE', [], $currentLang) }}
                                </span>
                            </p>

                            <a href="{{ route('suppliers.show', $company->company_id) }}"
                                class="block w-full text-center bg-brand-teal/10 text-brand-teal py-2 px-4 rounded-lg font-semibold hover:bg-brand-teal/20 transition-all">
                                {{ __('suppliers.SUPPLIERS_CARD_VIEW_SERVICES', [], $currentLang) }}
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-10 bg-white rounded-xl shadow-sm border border-gray-100">
                        <p class="text-gray-500 text-lg">{{ __('suppliers.SUPPLIERS_NO_RESULTS', [], $currentLang) }}</p>
                    </div>
                    @endforelse
                </div>
            </main>
        </div>
    </div>
</div>
@endsection
