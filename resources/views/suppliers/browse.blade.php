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
<div class="bg-dark-navy text-white pt-24 pb-16 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-green/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="container mx-auto px-4 text-center max-w-[1400px] relative z-10">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black mb-6 leading-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
            {{ __('suppliers.SUPPLIERS_HERO_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-slate-400 max-w-2xl mx-auto text-base md:text-lg">
            {{ __('suppliers.SUPPLIERS_HERO_DESC', [], $currentLang) }}
        </p>
    </div>
    <div class="container mx-auto px-4 max-w-[1400px] text-center mt-6 relative z-10">
        <span class="inline-flex items-center gap-2 py-2 px-5 rounded-full bg-slate-900/60 border border-white/10 backdrop-blur-md text-sm font-semibold text-brand-green shadow-sm">
            ★ {{ str_replace('[COUNT]', $companies->count(), __('suppliers.SUPPLIERS_DISPLAY_COUNT', [], $currentLang)) }}
        </span>
    </div>
</div>

{{-- Main --}}
<div class="py-12 md:py-20 bg-dark-navy min-h-screen">
    <div class="container mx-auto px-4 max-w-[1400px] w-full">
        <div class="flex flex-col md:flex-row gap-8 lg:gap-12">

            {{-- Filters --}}
            <aside class="w-full md:w-1/4">
                <form action="{{ route('suppliers.browse') }}" method="GET" class="bg-slate-900/40 p-6 rounded-2xl border border-white/5 backdrop-blur-md sticky-filter">
                    <h3 class="text-lg font-black text-white mb-5 border-b border-white/5 pb-3">
                        {{ __('suppliers.SUPPLIERS_FILTER_TITLE', [], $currentLang) }}
                    </h3>

                    {{-- Industry --}}
                    <div class="mb-6">
                        <label for="industry" class="font-bold text-slate-300 mb-3 block text-sm">
                            {{ __('suppliers.SUPPLIERS_FILTER_INDUSTRY', [], $currentLang) }}
                        </label>
                        <select id="industry" name="industry" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white focus:ring-1 focus:ring-brand-green/40 focus:border-brand-green/40 focus:outline-none transition">
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
                        <label for="size" class="font-bold text-slate-300 mb-3 block text-sm">
                            {{ __('suppliers.SUPPLIERS_FILTER_SIZE', [], $currentLang) }}
                        </label>
                        <select id="size" name="size" class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white focus:ring-1 focus:ring-brand-green/40 focus:border-brand-green/40 focus:outline-none transition">
                            <option value="">{{ __('suppliers.SUPPLIERS_ALL_SIZES', [], $currentLang) }}</option>
                            @foreach($sizes as $size)
                            <option value="{{ $size }}" {{ $sizeFilter === $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy py-3.5 rounded-xl font-black hover:opacity-90 transition-all shadow-green-glow mb-4">
                        {{ __('suppliers.BTN_APPLY_FILTERS', [], $currentLang) }}
                    </button>
                    @if(!empty($industryFilter) || !empty($sizeFilter))
                    <a href="{{ route('suppliers.browse') }}" class="block text-center text-sm text-slate-400 hover:text-white transition duration-200">
                        {{ __('suppliers.BTN_CANCEL_FILTERS', [], $currentLang) }}
                    </a>
                    @endif
                </form>
            </aside>

            {{-- List --}}
            <main class="w-full md:w-3/4">
                <div class="mb-6">
                    <h2 class="text-xl md:text-2xl font-black text-white">
                        {{ str_replace('[COUNT]', $companies->count(), __('suppliers.SUPPLIERS_DISPLAY_COUNT', [], $currentLang)) }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($companies as $company)
                    <div class="expert-card p-6 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center {{ $direction === 'rtl' ? 'space-x-reverse' : '' }} space-x-3 mb-5 border-b border-white/5 pb-4">
                                <img src="{{ $company->company_logo }}"
                                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($company->name) }}&background=E2E8F0&color=334155'"
                                    alt="{{ $company->name }}"
                                    class="w-12 h-12 rounded-full flex-shrink-0 bg-slate-900 object-cover border border-white/10">
                                <div class="min-w-0">
                                    <h3 class="text-base font-black text-white truncate" title="{{ $company->name }}">
                                        {{ $company->name }}
                                    </h3>
                                    <span class="text-xs text-amber-500 font-bold flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-star"></i> {{ number_format($company->avg_rating ?? 0, 1) }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <p class="text-xs text-slate-400">
                                    {{ __('suppliers.SUPPLIERS_CARD_INDUSTRY', [], $currentLang) }}
                                    <span class="text-slate-300 font-semibold ml-1">{{ $company->industry ?? ($direction === 'rtl' ? 'غير محدد' : 'Not specified') }}</span>
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ __('suppliers.SUPPLIERS_CARD_EXPERTS', [], $currentLang) }}
                                    <span class="text-slate-300 font-semibold ml-1">
                                        {{ $company->service_count }} {{ __('suppliers.SUPPLIERS_CARD_EXPERT_AVAILABLE', [], $currentLang) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('suppliers.show', $company->company_id) }}"
                            class="ec-btn w-full justify-center">
                            <span>{{ __('suppliers.SUPPLIERS_CARD_VIEW_SERVICES', [], $currentLang) }}</span>
                            @if($direction === 'rtl')
                                <i class="fa-solid fa-arrow-left-long text-xs"></i>
                            @else
                                <i class="fa-solid fa-arrow-right-long text-xs"></i>
                            @endif
                        </a>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-16 bg-slate-900/40 border border-white/5 rounded-2xl">
                        <p class="text-slate-400 text-lg">{{ __('suppliers.SUPPLIERS_NO_RESULTS', [], $currentLang) }}</p>
                    </div>
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                <div class="mt-12 pagination-wrapper">
                    @if(method_exists($companies, 'links'))
                        {{ $companies->appends(request()->query())->links() }}
                    @endif
                </div>
            </main>
        </div>
    </div>
</div>
@endsection
