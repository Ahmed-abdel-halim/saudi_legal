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
<div class="bg-white pt-20 pb-16">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 text-center max-w-[1920px]">
        <h1 class="text-3xl md:text-4xl font-extrabold text-dark-navy mb-4">
            {{ __('requests.REQUESTS_TITLE', [], $currentLang) }}
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            {{ __('requests.REQUESTS_SUBTITLE', [], $currentLang) }}
        </p>
    </div>
</div>

{{-- Main Content --}}
<div class="py-12 md:py-20 bg-slate-light min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 max-w-[1920px]">
        <div class="flex flex-col md:flex-row gap-6 md:gap-10">
            
            {{-- Sidebar Filters --}}
            <aside class="w-full md:w-1/4">
                <form action="{{ route('requests.browse') }}" method="GET" class="bg-white p-6 rounded-xl shadow-sm sticky-filter">
                    <h3 class="font-bold text-dark-navy mb-4 border-b pb-2 text-lg">
                        {{ __('requests.FILTER_TITLE', [], $currentLang) }}
                    </h3>
                    
                    {{-- Search Filter --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2 text-gray-700">
                            {{ __('requests.FILTER_SEARCH', [], $currentLang) }}
                        </label>
                        <input type="text" 
                            name="search" 
                            value="{{ old('search', $filterSearch) }}" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-brand-primary focus:border-transparent transition"
                            placeholder="{{ __('requests.FILTER_SEARCH_PLACEHOLDER', [], $currentLang) }}">
                    </div>
                    
                    {{-- Max Rate Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold mb-2 text-gray-700">
                            {{ __('requests.FILTER_MAX_RATE', [], $currentLang) }}
                        </label>
                        <input type="number" 
                            name="max_rate" 
                            value="{{ old('max_rate', $filterMaxRate) }}" 
                            min="0"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-brand-primary focus:border-transparent transition"
                            placeholder="0.00">
                    </div>
                    
                    {{-- Apply Button --}}
                    <button type="submit" 
                        class="w-full bg-brand-magenta text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition-all duration-300 shadow-md hover:shadow-lg">
                        {{ __('requests.FILTER_APPLY', [], $currentLang) }}
                    </button>
                    
                    {{-- Clear Filters Link --}}
                    @if(!empty($filterSearch) || !empty($filterMaxRate))
                    <a href="{{ route('requests.browse') }}" 
                        class="block text-center text-sm text-gray-500 hover:text-brand-primary mt-3 transition">
                        {{ $direction === 'rtl' ? 'مسح الفلاتر' : 'Clear Filters' }}
                    </a>
                    @endif
                </form>
            </aside>

            {{-- Main Content Area --}}
            <main class="w-full md:w-3/4">
                {{-- Results Count --}}
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-dark-navy">
                        <span class="text-brand-magenta">{{ $requests->count() }}</span>
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
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-teal-glow transition-all duration-300 border border-gray-100 hover:border-brand-teal/30">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-4">
                            <div class="flex-1">
                                <span class="text-xs font-bold text-brand-magenta bg-brand-magenta/10 px-3 py-1 rounded-full inline-block mb-2">
                                    {{ $project->requester_name }}
                                </span>
                                <h3 class="text-xl font-bold text-dark-navy mt-2 mb-2">
                                    <a href="{{ route('requests.show', $project->project_id) }}" 
                                        class="hover:text-brand-teal transition-colors duration-300">
                                        {{ $project->title }}
                                    </a>
                                </h3>
                            </div>
                            <div class="text-{{ $direction === 'rtl' ? 'left' : 'right' }} md:text-{{ $direction === 'rtl' ? 'left' : 'right' }}">
                                <span class="block text-2xl font-extrabold text-dark-navy">
                                    {{ number_format($project->max_hourly_rate, 2) }}
                                    <span class="text-xs font-normal text-gray-500">
                                        {{ __('requests.CURRENCY_HOUR', [], $currentLang) }}
                                    </span>
                                </span>
                                <span class="text-xs text-gray-500">
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
                        <p class="text-gray-600 mb-4 text-sm line-clamp-2 leading-relaxed">
                            {{ $project->scope_description }}
                        </p>
                        
                        {{-- Skills Tags --}}
                        @if(!empty($project->skills_array) && count($project->skills_array) > 0)
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($project->skills_array as $skill)
                                <span class="bg-brand-cyan/10 text-brand-teal text-xs font-bold px-3 py-1 rounded-full border border-brand-cyan/20">
                                    {{ trim($skill) }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                        
                        {{-- Details Button --}}
                        <div class="flex justify-{{ $direction === 'rtl' ? 'start' : 'end' }} mt-4">
                            <a href="{{ route('requests.show', $project->project_id) }}" 
                                class="inline-block bg-brand-magenta text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-opacity-90 transition-all duration-300 shadow-md hover:shadow-lg">
                                {{ __('requests.REQUEST_DETAILS', [], $currentLang) }}
                            </a>
                        </div>
                    </div>
                    @empty
                    {{-- No Results --}}
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">
                            {{ __('requests.NO_REQUESTS', [], $currentLang) }}
                        </p>
                        @if(!empty($filterSearch) || !empty($filterMaxRate))
                        <a href="{{ route('requests.browse') }}" 
                            class="inline-block mt-4 text-brand-primary hover:underline font-semibold">
                            {{ $direction === 'rtl' ? 'عرض جميع الطلبات' : 'View All Requests' }}
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
