@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';

// Category label map — DB value → translated label
$categoryLabels = __('services.CATEGORY_LABELS', [], $currentLang);
if (!is_array($categoryLabels)) {
    $categoryLabels = [
        'Tech'       => 'Tech & Programming',
        'Design'     => 'Design & Creative',
        'Marketing'  => 'Marketing',
        'Consulting' => 'Consulting',
        'Auditing'   => 'Auditing & Review',
        'Other'      => 'Other',
    ];
}
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

    .kind-badge-expert {
        background: linear-gradient(135deg, #a78bfa, #7c3aed);
        color: #fff;
    }
    .kind-badge-company {
        background: linear-gradient(135deg, #34d399, #059669);
        color: #fff;
    }
</style>
@endpush

@section('content')
{{-- Page Header with Search --}}
<div class="bg-dark-navy text-white pt-24 pb-16 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-green/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="container mx-auto px-4 text-center max-w-[1400px] relative z-10">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black mb-6 leading-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
            {{ __('services.SERVICES_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-slate-400 max-w-2xl mx-auto mb-8 text-base md:text-lg">
            {{ __('services.SERVICES_SUBTITLE', [], $currentLang) }}
        </p>

        {{-- Main Search Bar --}}
        <div class="max-w-2xl mx-auto">
            <form action="{{ route('services.browse') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                <input type="text"
                    name="search"
                    value="{{ old('search', $filterSearch) }}"
                    class="flex-1 px-5 py-4 rounded-xl sm:rounded-r-xl sm:rounded-l-none bg-slate-900/60 text-white placeholder-slate-500 border border-white/10 focus:border-brand-green/40 focus:ring-1 focus:ring-brand-green/40 focus:outline-none transition-all duration-300"
                    placeholder="{{ __('services.SERVICES_SEARCH_PLACEHOLDER', [], $currentLang) }}">
                <button type="submit"
                    class="bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy px-8 py-4 rounded-xl sm:rounded-l-xl sm:rounded-r-none font-black hover:opacity-90 transition-all duration-300 shadow-green-glow whitespace-nowrap">
                    {{ __('services.BTN_SEARCH', [], $currentLang) }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="py-12 md:py-20 bg-dark-navy min-h-screen">
    <div class="container mx-auto px-4 max-w-[1400px]">
        <div class="flex flex-col md:flex-row gap-8 lg:gap-12">

            {{-- Sidebar Filters --}}
            <aside class="w-full md:w-1/4">
                <form action="{{ route('services.browse') }}" method="GET" class="bg-slate-900/40 p-6 rounded-2xl border border-white/5 backdrop-blur-md sticky-filter">
                    <h3 class="text-lg font-black text-white mb-5 border-b border-white/5 pb-3">
                        {{ __('services.SERVICES_FILTER_TITLE', [], $currentLang) }}
                    </h3>

                    {{-- Preserve search parameter --}}
                    <input type="hidden" name="search" value="{{ $filterSearch }}">

                    {{-- Service Category Filter --}}
                    <div class="mb-6">
                        <h4 class="font-bold text-slate-300 mb-3 text-sm">
                            {{ __('services.SERVICES_FILTER_CATEGORY', [], $currentLang) }}
                        </h4>
                        <ul class="space-y-3.5 text-sm">
                            @foreach($categoryLabels as $dbValue => $label)
                            <li>
                                <label class="flex items-center gap-2.5 cursor-pointer text-slate-400 hover:text-brand-green transition-all duration-200">
                                    <input type="checkbox"
                                        name="industry[]"
                                        value="{{ $dbValue }}"
                                        class="text-brand-green focus:ring-brand-green focus:ring-offset-slate-900 bg-slate-950 border-white/10 rounded"
                                        {{ in_array($dbValue, $filterIndustries) ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Industry Filter (for company services) --}}
                    @if($industries->isNotEmpty())
                    <div class="mb-6">
                        <h4 class="font-bold text-slate-300 mb-3 text-sm">
                            {{ __('services.SERVICES_FILTER_INDUSTRY', [], $currentLang) }}
                        </h4>
                        <ul class="space-y-3.5 text-sm max-h-40 overflow-y-auto">
                            @foreach($industries->diff(array_keys($categoryLabels)) as $industry)
                            <li>
                                <label class="flex items-center gap-2.5 cursor-pointer text-slate-400 hover:text-brand-green transition-all duration-200">
                                    <input type="checkbox"
                                        name="industry[]"
                                        value="{{ $industry }}"
                                        class="text-brand-green focus:ring-brand-green focus:ring-offset-slate-900 bg-slate-950 border-white/10 rounded"
                                        {{ in_array($industry, $filterIndustries) ? 'checked' : '' }}>
                                    <span>{{ $industry }}</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Apply Filter Button --}}
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy py-3.5 rounded-xl font-black hover:opacity-90 transition-all duration-300 shadow-green-glow mb-4">
                        {{ __('services.BTN_FILTER', [], $currentLang) }}
                    </button>

                    {{-- Reset Link --}}
                    <a href="{{ route('services.browse') }}"
                        class="block text-center text-sm text-slate-400 hover:text-white transition duration-200">
                        {{ __('services.BTN_CANCEL_FILTER', [], $currentLang) }}
                    </a>
                </form>
            </aside>

            {{-- Main Content Area --}}
            <main class="w-full md:w-3/4">
                {{-- Results Count --}}
                <div class="mb-6">
                    <h2 class="text-xl md:text-2xl font-black text-white">
                        {{ str_replace('[COUNT]', $services->count(), __('services.SERVICES_DISPLAY_COUNT', [], $currentLang)) }}
                    </h2>
                </div>

                {{-- Services Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($services as $service)
                    <div class="expert-card !p-0 overflow-hidden flex flex-col h-full">
                        <a href="{{ route('services.show', ['id' => $service->service_id]) }}" class="block flex-grow flex flex-col">

                            {{-- Service Image --}}
                            <div class="relative h-48 overflow-hidden bg-slate-950">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent z-10"></div>
                                <img src="{{ $service->service_image ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80' }}"
                                    alt="{{ $service->title }}"
                                    class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                    onerror="this.src='https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80';">

                                {{-- Service Kind Badge (Expert / Company) --}}
                                <div class="absolute top-3 {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} z-20">
                                    @if($service->type === 'expert')
                                    <span class="kind-badge-expert text-[9px] font-bold px-2 py-0.5 rounded-full shadow">
                                        {{ $currentLang === 'ar' ? 'خبير مستقل' : 'Expert' }}
                                    </span>
                                    @else
                                    <span class="kind-badge-company text-[9px] font-bold px-2 py-0.5 rounded-full shadow">
                                        {{ $currentLang === 'ar' ? 'شركة' : 'Company' }}
                                    </span>
                                    @endif
                                </div>

                                {{-- Provider Badge --}}
                                <div class="absolute bottom-4 {{ $direction === 'rtl' ? 'right-4' : 'left-4' }} z-20">
                                    <span class="bg-slate-900/60 backdrop-blur-md border border-white/10 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $service->company_name }}
                                    </span>
                                </div>
                            </div>

                            {{-- Service Content --}}
                            <div class="p-5 flex-grow flex flex-col">
                                {{-- Category Pill --}}
                                @php
                                    $rawCategory = $service->industry ?? '';
                                    $categoryLabel = $categoryLabels[$rawCategory] ?? $rawCategory;
                                @endphp
                                @if($categoryLabel)
                                <span class="inline-block text-[10px] font-extrabold bg-brand-green/10 text-brand-green border border-brand-green/20 px-2.5 py-0.5 rounded-full mb-3 self-start">
                                    {{ $categoryLabel }}
                                </span>
                                @endif

                                <h3 class="text-base font-black text-white hover:text-brand-green transition-colors duration-200 line-clamp-1 mb-2" title="{{ $service->title }}">
                                    {{ $service->title }}
                                </h3>

                                {{-- Expert Info --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <img src="{{ $service->expert_image ?? 'https://ui-avatars.com/api/?name=User&background=ccc&color=fff' }}"
                                        alt="{{ $service->expert_name }}"
                                        class="w-8 h-8 rounded-full object-cover ring-2 ring-brand-green/20"
                                        onerror="this.src='https://ui-avatars.com/api/?name=User&background=ccc&color=fff';">
                                    <div class="flex flex-col flex-1 min-w-0">
                                        <span class="text-xs font-bold text-slate-300 truncate">{{ $service->expert_name }}</span>
                                    </div>
                                </div>

                                {{-- Skills Tags --}}
                                @if(!empty($service->skills_array) && count($service->skills_array) > 0)
                                <div class="mb-4 flex flex-wrap gap-1">
                                    @foreach(array_slice($service->skills_array, 0, 2) as $skill)
                                    <span class="text-[9px] bg-slate-950/50 text-slate-400 px-2 py-0.5 rounded border border-white/5 font-semibold">
                                        {{ trim($skill) }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Rating and Price --}}
                                <div class="flex justify-between items-end border-t border-white/5 pt-4 mt-auto">
                                    <div class="flex items-center gap-1 text-amber-500 font-bold text-xs">
                                        <i class="fa-solid fa-star"></i>
                                        <span>{{ number_format($service->avg_rating ?? 5.0, 1) }}</span>
                                    </div>
                                    <div class="text-{{ $direction === 'rtl' ? 'left' : 'right' }} flex flex-col">
                                        <span class="text-[10px] text-slate-500 mb-0.5">
                                            {{ __('services.PRICE_LABEL', [], $currentLang) }}
                                        </span>
                                        <strong class="text-lg font-black text-brand-green">
                                            {{ number_format($service->hourly_rate, 2) }}
                                            <span class="text-[10px] font-normal text-slate-400">
                                                {{ __('services.CURRENCY_HOUR', [], $currentLang) }}
                                            </span>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    {{-- No Results --}}
                    <div class="col-span-full text-center py-16 bg-slate-900/40 border border-white/5 rounded-2xl">
                        <svg class="w-16 h-16 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-slate-400 text-lg mb-4">
                            {{ __('services.SERVICES_NO_RESULTS', [], $currentLang) }}
                        </p>
                        @if(!empty($filterSearch) || !empty($filterIndustries))
                        <a href="{{ route('services.browse') }}"
                            class="inline-block text-brand-green hover:underline font-semibold">
                            {{ $direction === 'rtl' ? 'عرض جميع الخدمات' : 'View All Services' }}
                        </a>
                        @endif
                    </div>
                    @endforelse
                </div>
                {{-- Pagination Links --}}
                <div class="mt-12 pagination-wrapper">
                    {{ $services->appends(request()->query())->links() }}
                </div>
            </main>
        </div>
    </div>
</div>
@endsection