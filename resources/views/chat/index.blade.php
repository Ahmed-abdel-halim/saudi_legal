@extends(Auth::user()->role === 'expert' ? 'layouts.expert' : 'layouts.app')

@section('content')
@php
    $routePrefix = Auth::user()->role === 'expert' ? 'dashboard.expert.chat.' : 'dashboard.chat.';
    $currentLang = app()->getLocale();
    $isRtl = $currentLang === 'ar';
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-indigo-50/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ── Page Header ── --}}
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $isRtl ? 'الرسائل' : 'Messages' }}</h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">{{ $isRtl ? 'جميع محادثاتك في مكان واحد' : 'All your conversations in one place' }}</p>
                </div>
            </div>
            <span class="bg-indigo-100 text-indigo-700 font-bold text-sm px-3 py-1.5 rounded-full">
                {{ $conversations->count() }} {{ $isRtl ? 'محادثة' : 'chats' }}
            </span>
        </div>

        {{-- ── Two‑Panel Chat Shell ── --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden flex h-[75vh] min-h-[480px]">

            {{-- LEFT: Conversation List --}}
            <div class="w-full md:w-[340px] lg:w-[380px] flex-shrink-0 border-{{ $isRtl ? 'l' : 'r' }} border-slate-100 flex flex-col">

                {{-- Search bar --}}
                <div class="p-4 border-b border-slate-100">
                    <div class="relative">
                        <svg class="absolute {{ $isRtl ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="searchInput" type="text" placeholder="{{ $isRtl ? 'ابحث في المحادثات...' : 'Search conversations...' }}"
                            class="{{ $isRtl ? 'pr-9 pl-4 text-right' : 'pl-9 pr-4' }} w-full py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300 outline-none transition">
                    </div>
                </div>

                {{-- List --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-slate-50" id="conversationList"
                     x-data="{ showDeleteModal: false, deleteUrl: '' }">

                    @forelse($conversations as $conversation)
                        @php
                            $otherUser   = $conversation->participant_1 == Auth::id() ? $conversation->participant2 : $conversation->participant1;
                            $lastMessage = $conversation->lastMessage;
                            $unreadCount = $conversation->messages()->where('sender_id', '!=', Auth::id())->where('is_read', false)->count();
                            $isUnread    = $unreadCount > 0;

                            $avatarUrl = $otherUser->avatar_path
                                ? asset('uploads/' . $otherUser->avatar_path)
                                : 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&background=6366f1&color=fff&size=128&bold=true';
                        @endphp

                        <div class="conversation-item group relative hover:bg-indigo-50/60 transition-colors duration-200 cursor-pointer"
                             data-name="{{ strtolower($otherUser->name) }}"
                             data-href="{{ route($routePrefix . 'show', $conversation->id) }}">

                            <a href="{{ route($routePrefix . 'show', $conversation->id) }}" class="flex items-center gap-3.5 px-4 py-4">
                                {{-- Avatar --}}
                                <div class="relative flex-shrink-0">
                                    <img src="{{ $avatarUrl }}"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($otherUser->name) }}&background=6366f1&color=fff&size=128'"
                                         class="w-12 h-12 rounded-2xl object-cover border-2 {{ $isUnread ? 'border-indigo-400' : 'border-slate-100' }} group-hover:border-indigo-300 transition shadow-sm">
                                    @if($isUnread)
                                        <span class="absolute -top-1 -{{ $isRtl ? 'left' : 'right' }}-1 min-w-[20px] h-5 px-1 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full shadow border-2 border-white">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Text --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-0.5">
                                        <span class="text-sm font-bold text-slate-800 truncate group-hover:text-indigo-700 transition">{{ $otherUser->name }}</span>
                                        <span class="text-[11px] text-slate-400 whitespace-nowrap flex-shrink-0">
                                            {{ $lastMessage ? $lastMessage->created_at->diffForHumans(null, true) : '' }}
                                        </span>
                                    </div>
                                    <p class="text-xs truncate {{ $isUnread ? 'text-slate-800 font-semibold' : 'text-slate-400' }}">
                                        @if($lastMessage && $lastMessage->sender_id == Auth::id())
                                            <span class="text-indigo-400">{{ $isRtl ? 'أنت: ' : 'You: ' }}</span>
                                        @endif
                                        {{ $lastMessage ? $lastMessage->content : ($isRtl ? 'لا توجد رسائل بعد' : 'No messages yet') }}
                                    </p>
                                </div>

                                @if($isUnread)
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 flex-shrink-0 shadow-sm shadow-indigo-300"></span>
                                @endif
                            </a>

                            {{-- Delete (hover) --}}
                            <button type="button"
                                    @click.prevent="showDeleteModal = true; deleteUrl = '{{ route($routePrefix . 'destroy', $conversation->id) }}'"
                                    class="absolute top-1/2 -translate-y-1/2 {{ $isRtl ? 'left-3' : 'right-3' }} w-8 h-8 rounded-full bg-white border border-red-100 text-red-400 flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white hover:border-red-500 hover:shadow-lg shadow-red-200 transition-all duration-200 z-10">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>

                            {{-- Delete Modal (AlpineJS) --}}
                            <div x-show="showDeleteModal"
                                 style="display:none"
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                                 x-transition:enter="ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                <div @click.away="showDeleteModal = false"
                                     class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center"
                                     x-transition:enter="ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100">
                                    <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $isRtl ? 'حذف المحادثة؟' : 'Delete Chat?' }}</h3>
                                    <p class="text-slate-500 text-sm mb-7 leading-relaxed">{{ $isRtl ? 'سيتم حذف جميع الرسائل بشكل نهائي ولا يمكن التراجع.' : 'All messages will be permanently deleted. This cannot be undone.' }}</p>
                                    <div class="flex gap-3">
                                        <button @click="showDeleteModal = false" type="button"
                                                class="flex-1 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-sm">
                                            {{ $isRtl ? 'إلغاء' : 'Cancel' }}
                                        </button>
                                        <form method="POST" :action="deleteUrl" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-full py-2.5 rounded-xl font-bold text-white bg-red-500 hover:bg-red-600 shadow-lg shadow-red-100 transition text-sm">
                                                {{ $isRtl ? 'نعم، احذف' : 'Delete' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <div class="flex flex-col items-center justify-center h-full py-16 px-6 text-center">
                            <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-5">
                                <svg class="w-9 h-9 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-700 mb-1">{{ $isRtl ? 'لا توجد محادثات' : 'No conversations yet' }}</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">{{ $isRtl ? 'ستظهر محادثاتك هنا عند قبول طلب خدمة.' : 'Your conversations will appear here once you accept a service request.' }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: Welcome / Select a chat --}}
            <div class="hidden md:flex flex-1 items-center justify-center bg-gradient-to-br from-slate-50 to-indigo-50/40 relative overflow-hidden">
                {{-- Decorative circles --}}
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-indigo-100/40 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-purple-100/40 rounded-full blur-3xl pointer-events-none"></div>

                <div class="text-center relative z-10 px-8">
                    <div class="w-24 h-24 bg-white rounded-3xl shadow-xl shadow-indigo-100 flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-800 mb-2">
                        {{ $isRtl ? 'اختر محادثة' : 'Select a conversation' }}
                    </h2>
                    <p class="text-sm text-slate-400 max-w-xs mx-auto leading-relaxed">
                        {{ $isRtl ? 'اختر إحدى محادثاتك من القائمة لعرض الرسائل.' : 'Choose a conversation from the list to start reading and replying.' }}
                    </p>

                    @if($conversations->count() > 0)
                    <div class="mt-8 flex flex-wrap gap-2 justify-center">
                        @foreach($conversations->take(3) as $conv)
                            @php $ou = $conv->participant_1 == Auth::id() ? $conv->participant2 : $conv->participant1; @endphp
                            <a href="{{ route($routePrefix . 'show', $conv->id) }}"
                               class="flex items-center gap-2 px-4 py-2 bg-white rounded-full border border-indigo-100 hover:border-indigo-400 hover:shadow-md transition text-sm font-semibold text-slate-700 hover:text-indigo-700">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($ou->name) }}&background=6366f1&color=fff&size=64&bold=true"
                                     class="w-6 h-6 rounded-full object-cover">
                                {{ $ou->name }}
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- end flex shell --}}
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #c7d2fe; }
</style>

<script>
    // Live search filter
    const searchInput = document.getElementById('searchInput');
    const items       = document.querySelectorAll('.conversation-item');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            items.forEach(item => {
                const name = item.dataset.name || '';
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }
</script>

@endsection
