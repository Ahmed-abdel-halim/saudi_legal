@extends(Auth::user()->role === 'expert' ? 'layouts.expert' : 'layouts.app')

@section('content')
@php
    $routePrefix = Auth::user()->role === 'expert' ? 'dashboard.expert.chat.' : 'dashboard.chat.';
@endphp
<div class="min-h-screen bg-slate-50 py-12" x-data="{ showDeleteModal: false, deleteUrl: '' }">
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
                                <!-- Actions (Delete) -->
                                <div class="absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 flex items-center pr-6 rtl:pr-0 rtl:pl-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <button type="button" 
                                            @click.prevent="showDeleteModal = true; deleteUrl = '{{ route($routePrefix . 'destroy', $conversation->id) }}'"
                                            class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white hover:shadow-lg transition-all transform hover:scale-110"
                                            title="{{ __('Delete Chat') }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
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
        
        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" 
             style="display: none;"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="showDeleteModal = false" 
                 class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                 
                <div class="px-6 py-8 text-center">
                    <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5">
                        <i class="fa-solid fa-triangle-exclamation text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">{{ __('Delete Chat?') }}</h3>
                    <p class="text-slate-500 leading-relaxed mb-8">
                        {{ __('Are you sure you want to delete this chat history? This action cannot be undone.') }}
                    </p>
                    
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false" 
                                type="button" 
                                class="flex-1 px-5 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        
                        <form method="POST" :action="deleteUrl" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-5 py-3 rounded-xl font-bold text-white bg-red-500 hover:bg-red-600 shadow-lg shadow-red-200 transition-all">
                                {{ __('Yes, Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
