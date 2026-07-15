@extends('layouts.app')

@section('content')
<div class="bg-dark-navy py-20 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    {{-- Decorative background elements --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-black text-white mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">{{ __('careers.JOIN_OUR_TEAM') }}</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
            {{ __('careers.JOIN_OUR_TEAM_DESC') }}
        </p>
    </div>
</div>

<div class="py-20 bg-dark-navy relative">
    <div class="container mx-auto px-6 max-w-4xl relative z-10">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-black text-white">
                {{ __('careers.OPEN_VACANCIES') }}
                <span class="ml-2 inline-flex items-center justify-center bg-brand-green/10 text-brand-green text-sm font-semibold rounded-full px-3 py-1 border border-brand-green/20">
                    {{ $careers->count() }}
                </span>
            </h2>
        </div>
        
        @if ($careers->count() > 0)
            <div class="space-y-6">
                @foreach ($careers as $job)
                <div class="expert-card p-8 hover:shadow-lg transition-all duration-300">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="mb-4 md:mb-0">
                            <h3 class="text-xl font-bold text-white mb-2">{{ $job->title }}</h3>
                            <div class="flex items-center text-sm text-slate-500 space-x-4 space-x-reverse rtl:space-x-reverse">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-brand-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $job->location }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-brand-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('careers.FULL_TIME') }}
                                </span>
                            </div>
                        </div>
                        <a href="mailto:careers@radiif.com?subject=Application for {{ urlencode($job->title) }}" 
                           class="inline-block bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy font-black px-6 py-2.5 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 text-sm whitespace-nowrap shadow-md shadow-brand-green/10">
                            {{ __('careers.BTN_APPLY') }}
                        </a>
                    </div>
                    <div class="mt-5 pt-5 border-t border-white/5">
                        <p class="text-slate-400 leading-relaxed text-sm md:text-base line-clamp-3">
                            {{ $job->description }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 expert-card px-6">
                <div class="w-20 h-20 bg-slate-900/60 rounded-full flex items-center justify-center mx-auto mb-6 border border-white/5">
                    <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">{{ __('careers.NO_OPEN_VACANCIES') }}</h3>
                <p class="text-slate-400 max-w-md mx-auto text-sm leading-relaxed">{{ __('careers.JOIN_OUR_TEAM_DESC') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
