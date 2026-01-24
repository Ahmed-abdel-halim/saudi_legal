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
{{-- Page Header with Search --}}
<div class="bg-dark-navy text-white pt-20 pb-16">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 text-center max-w-[1920px]">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-6">
            {{ __('services.SERVICES_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-gray-300 max-w-2xl mx-auto mb-8 text-lg">
            {{ __('services.SERVICES_SUBTITLE', [], $currentLang) }}
        </p>

        {{-- Main Search Bar --}}
        <div class="max-w-2xl mx-auto">
            <form action="{{ route('services.browse') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                <input type="text"
                    name="search"
                    value="{{ old('search', $filterSearch) }}"
                    class="flex-1 px-5 py-4 rounded-lg sm:rounded-r-none text-gray-800 focus:ring-2 focus:ring-brand-primary focus:outline-none transition"
                    placeholder="{{ __('services.SERVICES_SEARCH_PLACEHOLDER', [], $currentLang) }}">
                <button type="submit"
                    class="bg-brand-magenta text-white px-8 py-4 rounded-lg sm:rounded-l-none font-bold hover:bg-opacity-90 transition-all duration-300 shadow-lg hover:shadow-xl whitespace-nowrap">
                    {{ __('services.BTN_SEARCH', [], $currentLang) }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="py-12 md:py-20 bg-slate-light min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 max-w-[1920px]">
        <div class="flex flex-col md:flex-row gap-6 md:gap-10">

            {{-- Sidebar Filters --}}
            <aside class="w-full md:w-1/4">
                <form action="{{ route('services.browse') }}" method="GET" class="bg-white p-6 rounded-xl shadow-sm sticky-filter">
                    <h3 class="text-xl font-bold text-dark-navy mb-5 border-b pb-3">
                        {{ __('services.SERVICES_FILTER_TITLE', [], $currentLang) }}
                    </h3>

                    {{-- Preserve search parameter --}}
                    <input type="hidden" name="search" value="{{ $filterSearch }}">

                    {{-- Industry Filter --}}
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-3 text-sm">
                            {{ __('services.SERVICES_FILTER_INDUSTRY', [], $currentLang) }}
                        </h4>
                        <ul class="space-y-2 text-sm max-h-48 overflow-y-auto">
                            @forelse($industries as $industry)
                            <li>
                                <label class="flex items-center cursor-pointer hover:text-brand-primary transition">
                                    <input type="checkbox"
                                        name="industry[]"
                                        value="{{ $industry }}"
                                        class="{{ $direction === 'rtl' ? 'ml-2' : 'mr-2' }} text-brand-teal focus:ring-brand-teal rounded border-gray-300"
                                        {{ in_array($industry, $filterIndustries) ? 'checked' : '' }}>
                                    <span>{{ $industry }}</span>
                                </label>
                            </li>
                            @empty
                            <li class="text-gray-500 text-xs">{{ $direction === 'rtl' ? 'لا توجد صناعات' : 'No industries' }}</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Apply Filter Button --}}
                    <button type="submit"
                        class="w-full bg-brand-magenta text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all duration-300 shadow-md hover:shadow-lg mb-3">
                        {{ __('services.BTN_FILTER', [], $currentLang) }}
                    </button>

                    {{-- Reset Link --}}
                    <a href="{{ route('services.browse') }}"
                        class="block text-center text-sm text-gray-600 hover:text-brand-primary hover:underline transition">
                        {{ __('services.BTN_CANCEL_FILTER', [], $currentLang) }}
                    </a>
                </form>
            </aside>

            {{-- Main Content Area --}}
            <main class="w-full md:w-3/4">
                {{-- Results Count --}}
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-dark-navy">
                        {{ str_replace('[COUNT]', $services->count(), __('services.SERVICES_DISPLAY_COUNT', [], $currentLang)) }}
                    </h2>
                </div>

                {{-- Services Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($services as $service)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-teal-glow border border-gray-100">
                        <a href="{{ route('services.show', ['id' => $service->service_id]) }}" class="block">

                            {{-- Service Image --}}
                            <div class="relative h-48 overflow-hidden bg-gray-200">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent z-10"></div>
                                <img src="{{ $service->service_image ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80' }}"
                                    alt="{{ $service->title }}"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80';">

                                {{-- Company Badge --}}
                                <div class="absolute bottom-4 {{ $direction === 'rtl' ? 'right-4' : 'left-4' }} z-20">
                                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $service->company_name }}
                                    </span>
                                </div>
                            </div>

                            {{-- Service Content --}}
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-dark-navy truncate mb-2" title="{{ $service->title }}">
                                    {{ $service->title }}
                                </h3>

                                {{-- Expert Info --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <img src="{{ $service->expert_image ?? 'https://ui-avatars.com/api/?name=User&background=ccc&color=fff' }}"
                                        alt="{{ $service->expert_name }}"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm"
                                        onerror="this.src='https://ui-avatars.com/api/?name=User&background=ccc&color=fff';">
                                    <div class="flex flex-col flex-1 min-w-0">
                                        <span class="text-sm font-bold text-gray-700 truncate">{{ $service->expert_name }}</span>
                                        <span class="text-xs text-gray-500 truncate">{{ $service->industry }}</span>
                                    </div>
                                </div>

                                {{-- Skills Tags --}}
                                @if(!empty($service->skills_array) && count($service->skills_array) > 0)
                                <div class="mb-3 flex flex-wrap gap-1">
                                    @foreach(array_slice($service->skills_array, 0, 2) as $skill)
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-1 rounded border border-slate-200">
                                        {{ trim($skill) }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Rating and Price --}}
                                <div class="flex justify-between items-end border-t border-gray-100 pt-4 mt-2">
                                    <div class="flex items-center gap-1 text-yellow-500 font-bold text-sm">
                                        <span>★</span>
                                        <span>{{ number_format($service->avg_rating ?? 5.0, 1) }}</span>
                                    </div>
                                    <div class="text-{{ $direction === 'rtl' ? 'left' : 'right' }}">
                                        <span class="block text-xs text-gray-400 mb-0.5">
                                            {{ __('services.PRICE_LABEL', [], $currentLang) }}
                                        </span>
                                        <span class="text-xl font-extrabold text-brand-primary">
                                            {{ number_format($service->hourly_rate, 2) }}
                                            <span class="text-xs font-normal text-gray-500">
                                                {{ __('services.CURRENCY_HOUR', [], $currentLang) }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    {{-- No Results --}}
                    <div class="col-span-full text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-4">
                            {{ __('services.SERVICES_NO_RESULTS', [], $currentLang) }}
                        </p>
                        @if(!empty($filterSearch) || !empty($filterIndustries))
                        <a href="{{ route('services.browse') }}"
                            class="inline-block text-brand-primary hover:underline font-semibold">
                            {{ $direction === 'rtl' ? 'عرض جميع الخدمات' : 'View All Services' }}
                        </a>
                        @endif
                    </div>
                    @endforelse
                </div>
            </main>
        </div>
    </div>
</div>
@endsection