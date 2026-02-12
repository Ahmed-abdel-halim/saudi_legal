@extends(Auth::user()->role === 'expert' ? 'layouts.expert' : 'layouts.app')

@section('content')
@php
    $routePrefix = Auth::user()->role === 'expert' ? 'dashboard.expert.chat.' : 'dashboard.chat.';
@endphp
<div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <i class="fa-regular fa-comments"></i>
                    </span>
                    {{ __('dashboard.messages') }}
                </h1>
                <p class="text-slate-500 mt-1 rtl:mr-14 ltr:ml-14">{{ __('dashboard.recent_activity') ?? 'Stay connected with your clients and experts' }}</p>
            </div>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
                <i class="fa-solid fa-message text-9xl"></i>
            </div>

            @if($conversations->count() > 0)
                <div class="divide-y divide-slate-50">
                    @foreach($conversations as $conversation)
                        @php
                            $otherUser = $conversation->participant_1 == Auth::id() ? $conversation->participant2 : $conversation->participant1;
                            $lastMessage = $conversation->lastMessage;
                            $unreadCount = $conversation->messages()->where('sender_id', '!=', Auth::id())->where('is_read', false)->count();
                            $isActive = $unreadCount > 0;
                        @endphp
                        <a href="{{ route($routePrefix . 'show', $conversation->id) }}" class="block group hover:bg-slate-50/80 transition duration-300 relative overflow-hidden">
                            <div class="p-6 relative z-10 flex items-center gap-5">
                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden shadow-sm border-2 {{ $isActive ? 'border-indigo-500' : 'border-slate-100' }} group-hover:border-indigo-300 transition-colors">
                                        <img src="{{ $otherUser->avatar_path ? asset('uploads/' . $otherUser->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&background=random&color=fff&size=128' }}" 
                                             class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500"
                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($otherUser->name) }}&background=random&color=fff&size=128'">
                                    </div>
                                    @if($isActive)
                                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full shadow-md border-2 border-white animate-bounce-short">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <h3 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate">
                                            {{ $otherUser->name }}
                                        </h3>
                                        <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded-full whitespace-nowrap group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors">
                                            {{ $lastMessage ? $lastMessage->created_at->diffForHumans() : '' }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm truncate pr-4 rtl:pl-4 rtl:pr-auto {{ $isActive ? 'text-slate-900 font-bold' : 'text-slate-500' }}">
                                            @if($lastMessage && $lastMessage->sender_id == Auth::id())
                                                <span class="text-indigo-500 mr-1 rtl:ml-1 rtl:mr-0">{{ __('You:') }}</span>
                                            @endif
                                            {{ $lastMessage ? $lastMessage->content : 'No messages yet' }}
                                        </p>
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1">
                                            <i class="fa-solid fa-chevron-right rtl:rotate-180"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($isActive)
                                <div class="absolute inset-y-0 left-0 w-1 bg-indigo-500 rtl:left-auto rtl:right-0"></div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <div class="p-20 text-center flex flex-col items-center justify-center relative z-10">
                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6 animate-pulse-slow">
                        <i class="fa-regular fa-comments text-4xl text-indigo-300"></i>
                    </div>
                    <h3 class="font-bold text-2xl text-slate-800 mb-3">{{ __('dashboard.no_conversations_title') ?? 'No conversations yet' }}</h3>
                    <p class="text-slate-500 max-w-sm mx-auto leading-relaxed">
                        {{ __('dashboard.no_conversations_desc') ?? 'Messages will appear here once you interact with clients or accept tasks.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
