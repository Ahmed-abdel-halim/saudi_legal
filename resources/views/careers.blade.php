@extends('layouts.app')

@section('content')
<div class="bg-white py-20 relative overflow-hidden">
    {{-- Decorative background elements --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-brand-teal/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-brand-magenta/5 rounded-full translate-y-1/3 -translate-x-1/3"></div>

    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-extrabold text-dark-navy mb-6">{{ __('careers.JOIN_OUR_TEAM') }}</h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            {{ __('careers.JOIN_OUR_TEAM_DESC') }}
        </p>
    </div>
</div>

<div class="py-20 bg-slate-50 border-t border-gray-100">
    <div class="container mx-auto px-6 max-w-4xl">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-dark-navy">
                {{ __('careers.OPEN_VACANCIES') }}
                <span class="ml-2 inline-flex items-center justify-center bg-brand-teal/10 text-brand-teal text-sm font-semibold rounded-full px-3 py-1">
                    {{ $careers->count() }}
                </span>
            </h2>
        </div>
        
        @if ($careers->count() > 0)
            <div class="space-y-6">
                @foreach ($careers as $job)
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-xl font-bold text-dark-navy mb-2">{{ $job->title }}</h3>
                            <div class="flex items-center text-sm text-gray-500 space-x-4 space-x-reverse rtl:space-x-reverse">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 rtl:ml-1 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $job->location }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 rtl:ml-1 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('careers.FULL_TIME') }}
                                </span>
                            </div>
                        </div>
                        <a href="mailto:careers@radiif.com?subject=Application for {{ urlencode($job->title) }}" 
                           class="bg-brand-magenta text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-opacity-90 transition shadow-sm hover:shadow active:scale-95 text-sm md:text-base whitespace-nowrap">
                            {{ __('careers.BTN_APPLY') }}
                        </a>
                    </div>
                    <div class="mt-5 pt-5 border-t border-gray-50">
                        <p class="text-gray-600 leading-relaxed text-sm md:text-base line-clamp-3">
                            {{ $job->description }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100 px-6">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('careers.NO_OPEN_VACANCIES') }}</h3>
                <p class="text-gray-500 max-w-md mx-auto">{{ __('careers.JOIN_OUR_TEAM_DESC') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
