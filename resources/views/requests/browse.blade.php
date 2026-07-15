@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
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
{{-- Page Header --}}
<div class="bg-dark-navy text-white pt-24 pb-16 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-green/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="container mx-auto px-4 text-center max-w-[1400px] relative z-10">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black mb-6 leading-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
            {{ __('requests.REQUESTS_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
            {{ __('requests.REQUESTS_SUBTITLE', [], $currentLang) }}
        </p>
    </div>
</div>

{{-- Main Content --}}
<div class="py-12 md:py-20 bg-dark-navy min-h-screen">
    <div class="container mx-auto px-4 max-w-[1400px]">
        <div class="flex flex-col md:flex-row gap-8 lg:gap-12">
            
            {{-- Sidebar Filters --}}
            <aside class="w-full md:w-1/4">
                <form action="{{ route('requests.browse') }}" method="GET" class="bg-slate-900/40 p-6 rounded-2xl border border-white/5 backdrop-blur-md sticky-filter">
                    <h3 class="font-bold text-white mb-4 border-b border-white/5 pb-2 text-lg">
                        {{ __('requests.FILTER_TITLE', [], $currentLang) }}
                    </h3>
                    
                    {{-- Search Filter --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2 text-slate-300">
                            {{ __('requests.FILTER_SEARCH', [], $currentLang) }}
                        </label>
                        <input type="text" 
                            name="search" 
                            value="{{ old('search', $filterSearch) }}" 
                            class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-green/40 focus:border-brand-green/40 focus:outline-none transition"
                            placeholder="{{ __('requests.FILTER_SEARCH_PLACEHOLDER', [], $currentLang) }}">
                    </div>
                    
                    {{-- Max Rate Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold mb-2 text-slate-300">
                            {{ __('requests.FILTER_MAX_RATE', [], $currentLang) }}
                        </label>
                        <input type="number" 
                            name="max_rate" 
                            value="{{ old('max_rate', $filterMaxRate) }}" 
                            min="0"
                            step="0.01"
                            class="w-full bg-slate-950 border border-white/10 rounded-xl p-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-green/40 focus:border-brand-green/40 focus:outline-none transition"
                            placeholder="0.00">
                    </div>

                    {{-- Category Filter --}}
                    @php
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
                                        {{ in_array($dbValue, $filterIndustries ?? []) ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    {{-- Apply Button --}}
                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy py-3.5 rounded-xl font-black hover:opacity-90 transition-all duration-300 shadow-green-glow mb-4">
                        {{ __('requests.FILTER_APPLY', [], $currentLang) }}
                    </button>
                    
                    {{-- Clear Filters Link --}}
                    @if(!empty($filterSearch) || !empty($filterMaxRate) || !empty($filterIndustries))
                    <a href="{{ route('requests.browse') }}" 
                        class="block text-center text-sm text-slate-400 hover:text-white transition duration-200">
                        {{ $direction === 'rtl' ? 'مسح الفلاتر' : 'Clear Filters' }}
                    </a>
                    @endif
                </form>
            </aside>

            {{-- Main Content Area --}}
            <main class="w-full md:w-3/4">
                {{-- Results Count --}}
                <div class="mb-6">
                    <h2 class="text-xl md:text-2xl font-black text-white">
                        <span class="text-brand-green">{{ $requests->count() }}</span>
                        @if($requests->count() === 1)
                            {{ __('requests.RESULTS_COUNT', [], $currentLang) }}
                        @else
                            {{ __('requests.RESULTS_COUNT_PLURAL', [], $currentLang) }}
                        @endif
                    </h2>
                </div>
                
                {{-- Requests List --}}
                <div class="space-y-6">
                    @forelse($requests as $project)
                    <div class="expert-card p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-4">
                            <div class="flex-1">
                                <span class="text-xs font-bold text-brand-green bg-brand-green/10 px-3.5 py-1 rounded-full border border-brand-green/20 inline-block mb-2">
                                    {{ $project->requester_name }}
                                </span>
                                <h3 class="text-lg font-black text-white mt-2 mb-2">
                                    <a href="{{ route('requests.show', $project->project_id) }}" 
                                        class="hover:text-brand-green transition-colors duration-300">
                                        {{ $project->title }}
                                    </a>
                                </h3>
                            </div>
                            <div class="text-{{ $direction === 'rtl' ? 'left' : 'right' }} flex flex-col items-start md:items-end">
                                <span class="block text-2xl font-black text-brand-green leading-none">
                                    {{ number_format($project->max_hourly_rate, 2) }}
                                    <span class="text-xs font-normal text-slate-400">
                                        {{ __('requests.CURRENCY_HOUR', [], $currentLang) }}
                                    </span>
                                </span>
                                <span class="text-xs text-slate-400 mt-1">
                                    {{ $project->requested_duration_hours }}
                                    @if($project->requested_duration_hours == 1)
                                        {{ __('requests.REQUEST_HOURS', [], $currentLang) }}
                                    @else
                                        {{ __('requests.REQUEST_HOURS_PLURAL', [], $currentLang) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        {{-- Description --}}
                        <p class="text-slate-400 mb-4 text-sm line-clamp-2 leading-relaxed">
                            {{ $project->scope_description }}
                        </p>
                        
                        {{-- Skills Tags --}}
                        @if(!empty($project->skills_array) && count($project->skills_array) > 0)
                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach($project->skills_array as $skill)
                                <span class="bg-slate-950/60 text-brand-green text-xs font-bold px-3 py-1 rounded-full border border-brand-green/10">
                                    {{ trim($skill) }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                        
                        {{-- Details Button --}}
                        <div class="flex justify-{{ $direction === 'rtl' ? 'start' : 'end' }} mt-4 border-t border-white/5 pt-4">
                            <a href="{{ route('requests.show', $project->project_id) }}" 
                                class="ec-btn">
                                <span>{{ __('requests.REQUEST_DETAILS', [], $currentLang) }}</span>
                                @if($direction === 'rtl')
                                    <i class="fa-solid fa-arrow-left-long text-xs"></i>
                                @else
                                    <i class="fa-solid fa-arrow-right-long text-xs"></i>
                                @endif
                            </a>
                        </div>
                    </div>
                    @empty
                    {{-- No Results --}}
                    <div class="bg-slate-900/40 border border-white/5 rounded-2xl p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-slate-400 text-lg">
                            {{ __('requests.NO_REQUESTS', [], $currentLang) }}
                        </p>
                        @if(!empty($filterSearch) || !empty($filterMaxRate))
                        <a href="{{ route('requests.browse') }}" 
                            class="inline-block mt-4 text-brand-green hover:underline font-semibold">
                            {{ $direction === 'rtl' ? 'عرض جميع الطلبات' : 'View All Requests' }}
                        </a>
                        @endif
                    </div>
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                <div class="mt-12 pagination-wrapper">
                    @if(method_exists($requests, 'links'))
                        {{ $requests->appends(request()->query())->links() }}
                    @endif
                </div>
            </main>
        </div>
    </div>
</div>
@endsection
