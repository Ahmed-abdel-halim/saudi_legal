@extends('layouts.app')

@section('content')
<!-- Hero -->
<div class="bg-dark-navy text-white py-20 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand-green/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
    
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-black text-white mb-4 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">{{ __('blog.BLOG_TITLE') }}</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
            {{ __('blog.BLOG_SUBTITLE') }}
        </p>
    </div>
</div>

<!-- Post List -->
<div class="py-20 bg-dark-navy relative">
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3 pointer-events-none"></div>

    <div class="container mx-auto px-6 max-w-5xl relative z-10">
        @if ($posts->count() > 0)
            <div class="grid grid-cols-1 gap-10">
                @foreach($posts as $post)
                <!-- Post Card -->
                <div class="flex flex-col md:flex-row expert-card hover:shadow-lg transition-all duration-300 overflow-hidden gap-0">
                    <!-- Thumbnail -->
                    <div class="w-full md:w-1/3 flex-shrink-0 h-64 md:h-auto overflow-hidden relative border-r border-white/5 rtl:border-l rtl:border-r-0">
                        <img src="{{ $post->image ?? 'https://placehold.co/400x300/1E293B/5FD3D3?text=' . urlencode(mb_substr($post->title, 0, 15)) }}" 
                             onerror="this.src='https://placehold.co/400x300/1E293B/5FD3D3?text=TimeShare'"
                             alt="{{ $post->title }}" 
                             class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                    </div>
                    <!-- Content -->
                    <div class="p-6 md:p-8 flex-1 flex flex-col justify-center">
                        <div class="flex items-center text-sm text-brand-green font-semibold mb-3">
                            <span>{{ $post->posted_at->format('Y-m-d') }}</span>
                            <span class="mx-2 text-slate-600">•</span>
                            <span>{{ $post->author }}</span>
                        </div>
                        <h3 class="text-2xl font-black text-white mb-3 hover:text-brand-green transition-colors duration-300">
                            {{-- Link to detail page (placeholder or actual route) --}}
                            <a href="#">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-slate-400 leading-relaxed mb-6 line-clamp-3 text-sm">
                            {{ $post->summary }}
                        </p>
                        <a href="#" class="text-brand-green font-bold hover:underline inline-flex items-center gap-1 text-sm">
                            {{ __('blog.READ_MORE') }}
                            <svg class="w-4 h-4 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 expert-card px-6">
                <div class="w-16 h-16 bg-slate-900/60 border border-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                </div>
                <p class="text-lg font-bold text-white mb-2">{{ __('blog.NO_POSTS') }}</p>
                <p class="text-sm text-slate-400">{{ __('blog.BLOG_SUBTITLE') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
