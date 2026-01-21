{{-- resources/views/suppliers/profile.blade.php - Supplier Company Profile --}}
@extends('layouts.app')

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

@push('styles')
<style>
    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    /* Service Card Hover */
    .service-card {
        transition: all 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(13, 148, 136, 0.15);
    }

    /* Stats Card */
    .stat-card {
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.05) 0%, rgba(13, 148, 136, 0.02) 100%);
        border: 1px solid rgba(13, 148, 136, 0.1);
    }

    /* Rating Stars */
    .star-filled {
        color: #facc15;
    }
    
    .star-empty {
        color: #e5e7eb;
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }

    .animate-delay-100 { animation-delay: 0.1s; }
    .animate-delay-200 { animation-delay: 0.2s; }
    .animate-delay-300 { animation-delay: 0.3s; }
</style>
@endpush

@section('content')

{{-- Profile Header --}}
<div class="profile-header border-b border-gray-200">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 py-10 md:py-14">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8 animate-fade-in-up">
            
            {{-- Company Logo --}}
            <div class="flex-shrink-0">
                <img src="{{ $company->company_logo }}" 
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($company->name) }}&background=E2E8F0&color=334155&size=128'"
                    alt="{{ $company->name }}"
                    class="w-28 h-28 md:w-32 md:h-32 rounded-xl bg-gray-100 border-2 border-gray-200 p-2 object-cover shadow-sm">
            </div>
            
            {{-- Company Info --}}
            <div class="text-center {{ $direction === 'rtl' ? 'md:text-right' : 'md:text-left' }} flex-1">
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-dark-navy mb-3">
                    {{ $company->name }}
                </h1>
                
                {{-- Meta Info --}}
                <div class="flex flex-wrap justify-center {{ $direction === 'rtl' ? 'md:justify-start' : 'md:justify-start' }} gap-4 text-gray-600 text-sm mb-4">
                    {{-- Industry --}}
                    <span class="flex items-center bg-gray-100 px-3 py-1.5 rounded-full">
                        <span class="text-brand-teal {{ $direction === 'rtl' ? 'ml-1.5' : 'mr-1.5' }}">🏢</span>
                        {{ $company->industry ?? __('suppliers.SUPPLIERS_NOT_SPECIFIED', [], $currentLang) }}
                    </span>
                    
                    {{-- Size --}}
                    <span class="flex items-center bg-gray-100 px-3 py-1.5 rounded-full">
                        <span class="text-brand-teal {{ $direction === 'rtl' ? 'ml-1.5' : 'mr-1.5' }}">👥</span>
                        {{ $company->size }} {{ __('suppliers.SUPPLIERS_PROFILE_EMPLOYEES', [], $currentLang) }}
                    </span>
                    
                    {{-- Rating --}}
                    <span class="flex items-center bg-yellow-50 px-3 py-1.5 rounded-full">
                        <span class="text-yellow-500 {{ $direction === 'rtl' ? 'ml-1.5' : 'mr-1.5' }}">★</span>
                        <span class="font-semibold text-gray-700">{{ number_format($avgRating, 1) }}</span>
                        <span class="text-gray-500 {{ $direction === 'rtl' ? 'mr-1' : 'ml-1' }}">({{ $reviewCount }} {{ __('suppliers.SUPPLIERS_PROFILE_RATINGS', [], $currentLang) }})</span>
                    </span>
                </div>
                
                {{-- Description --}}
                <p class="text-gray-600 max-w-2xl leading-relaxed text-base">
                    {{ str_replace('[INDUSTRY]', $company->industry ?? '', __('suppliers.SUPPLIERS_PROFILE_DESC_TEMPLATE', [], $currentLang)) }}
                </p>
            </div>
            
            {{-- CTA Button --}}
            <div class="flex-shrink-0">
                <a href="{{ route('services.browse', ['company_id' => $company->company_id]) }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-teal to-teal-500 text-white px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all transform hover:scale-[1.02]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    {{ __('suppliers.SUPPLIERS_PROFILE_VIEW_ALL_SERVICES', [], $currentLang) }}
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 animate-fade-in-up animate-delay-100">
            <div class="stat-card rounded-xl p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-brand-teal">{{ $company->service_count ?? 0 }}</div>
                <div class="text-sm text-gray-600">{{ __('suppliers.SUPPLIERS_PROFILE_SERVICES_STAT', [], $currentLang) }}</div>
            </div>
            <div class="stat-card rounded-xl p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-brand-teal">{{ number_format($avgRating, 1) }}</div>
                <div class="text-sm text-gray-600">{{ __('suppliers.SUPPLIERS_PROFILE_RATING_STAT', [], $currentLang) }}</div>
            </div>
            <div class="stat-card rounded-xl p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-brand-teal">{{ $reviewCount }}</div>
                <div class="text-sm text-gray-600">{{ __('suppliers.SUPPLIERS_PROFILE_REVIEWS_STAT', [], $currentLang) }}</div>
            </div>
            <div class="stat-card rounded-xl p-4 text-center">
                <div class="text-2xl md:text-3xl font-bold text-brand-teal">{{ $projectCount ?? 0 }}</div>
                <div class="text-sm text-gray-600">{{ __('suppliers.SUPPLIERS_PROFILE_PROJECTS_STAT', [], $currentLang) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Services Section --}}
<div class="py-12 md:py-16 bg-slate-light">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        
        {{-- Section Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 animate-fade-in-up animate-delay-200">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-dark-navy">
                    {{ __('suppliers.SUPPLIERS_PROFILE_SERVICES_TITLE', [], $currentLang) }}
                </h2>
                <p class="text-gray-600 mt-1">
                    {{ str_replace('[COUNT]', $services->count(), __('suppliers.SUPPLIERS_PROFILE_SERVICES_SUBTITLE', [], $currentLang)) }}
                </p>
            </div>
            
            @if($services->count() > 6)
            <a href="{{ route('services.browse', ['company_id' => $company->company_id]) }}" 
               class="text-brand-teal font-semibold hover:underline flex items-center gap-1">
                {{ __('suppliers.SUPPLIERS_PROFILE_VIEW_ALL', [], $currentLang) }}
                <span>{{ $direction === 'rtl' ? '←' : '→' }}</span>
            </a>
            @endif
        </div>

        {{-- Services Grid --}}
        @if($services->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up animate-delay-300">
            @foreach($services as $service)
            <div class="service-card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                {{-- Service Header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-dark-navy text-lg mb-1 truncate" title="{{ $service->title }}">
                            {{ $service->title }}
                        </h3>
                        @if($service->category)
                        <span class="inline-block text-xs bg-brand-teal/10 text-brand-teal px-2 py-1 rounded-full font-medium">
                            {{ $service->category }}
                        </span>
                        @endif
                    </div>
                    @if($service->is_featured)
                    <span class="flex-shrink-0 bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-semibold">
                        ⭐ {{ __('suppliers.SUPPLIERS_PROFILE_FEATURED', [], $currentLang) }}
                    </span>
                    @endif
                </div>
                
                {{-- Description --}}
                <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                    {{ $service->description }}
                </p>
                
                {{-- Footer --}}
                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <div>
                        <span class="text-brand-teal font-bold text-lg">{{ number_format($service->hourly_rate) }}</span>
                        <span class="text-gray-500 text-sm">{{ __('suppliers.SAR_PER_HOUR', [], $currentLang) }}</span>
                    </div>
                    <a href="{{ route('services.show', $service->service_id) }}" 
                       class="text-sm font-semibold text-brand-magenta hover:text-brand-magenta/80 flex items-center gap-1 transition-colors">
                        {{ __('suppliers.BTN_DETAILS', [], $currentLang) }}
                        <span>{{ $direction === 'rtl' ? '←' : '→' }}</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- No Services --}}
        <div class="bg-white rounded-xl p-10 text-center shadow-sm border border-gray-100">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">{{ __('suppliers.SUPPLIERS_PROFILE_NO_SERVICES', [], $currentLang) }}</h3>
            <p class="text-gray-500">{{ __('suppliers.SUPPLIERS_PROFILE_NO_SERVICES_DESC', [], $currentLang) }}</p>
        </div>
        @endif

        {{-- View All Services Button (Mobile) --}}
        @if($services->count() > 0)
        <div class="mt-8 text-center md:hidden">
            <a href="{{ route('services.browse', ['company_id' => $company->company_id]) }}" 
               class="inline-flex items-center gap-2 bg-brand-teal text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-brand-teal/90 transition-all">
                {{ __('suppliers.SUPPLIERS_PROFILE_VIEW_ALL_SERVICES', [], $currentLang) }}
                <span>{{ $direction === 'rtl' ? '←' : '→' }}</span>
            </a>
        </div>
        @endif
    </div>
</div>

{{-- Back to Suppliers --}}
<div class="py-6 bg-white border-t border-gray-100">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <a href="{{ route('suppliers.browse') }}" 
           class="inline-flex items-center gap-2 text-gray-600 hover:text-brand-teal transition-colors font-medium">
            <span>{{ $direction === 'rtl' ? '→' : '←' }}</span>
            {{ __('suppliers.SUPPLIERS_PROFILE_BACK_TO_LIST', [], $currentLang) }}
        </a>
    </div>
</div>

@endsection
