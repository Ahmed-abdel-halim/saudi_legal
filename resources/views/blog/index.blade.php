@extends('layouts.app')

@section('content')
<!-- Hero -->
<div class="bg-dark-navy text-white py-20 relative overflow-hidden">
    {{-- Decorative elements --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-brand-teal/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
    
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('blog.BLOG_TITLE') }}</h1>
        <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto">
            {{ __('blog.BLOG_SUBTITLE') }}
        </p>
    </div>
</div>

<!-- Post List -->
<div class="py-20 bg-slate-50">
    <div class="container mx-auto px-6 max-w-5xl">
        @if ($posts->count() > 0)
            <div class="grid grid-cols-1 gap-10">
                @foreach($posts as $post)
                <!-- Post Card -->
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                    <!-- Thumbnail -->
                    <div class="w-full md:w-1/3 flex-shrink-0 h-64 md:h-auto overflow-hidden">
                        <img src="{{ $post->image ?? 'https://placehold.co/400x300/1E293B/5FD3D3?text=' . urlencode(mb_substr($post->title, 0, 15)) }}" 
                             onerror="this.src='https://placehold.co/400x300/1E293B/5FD3D3?text=TimeShare'"
                             alt="{{ $post->title }}" 
                             class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                    </div>
                    <!-- Content -->
                    <div class="p-6 md:p-8 flex-1 flex flex-col justify-center">
                        <div class="flex items-center text-sm text-brand-teal font-semibold mb-3">
                            <span>{{ $post->posted_at->format('Y-m-d') }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $post->author }}</span>
                        </div>
                        <h3 class="text-2xl font-bold text-dark-navy mb-3 hover:text-brand-teal transition-colors duration-300">
                            {{-- Link to detail page (placeholder or actual route) --}}
                            <a href="#">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 leading-relaxed mb-6 line-clamp-3">
                            {{ $post->summary }}
                        </p>
                        <a href="#" class="text-brand-magenta font-semibold hover:underline inline-flex items-center">
                            {{ __('blog.READ_MORE') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                </div>
                <p class="text-lg text-gray-500">{{ __('blog.NO_POSTS') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
