@extends(Auth::user()->role === 'expert' ? 'layouts.expert' : 'layouts.app')

@section('content')
<div class="h-[calc(100vh-80px)] bg-slate-50 flex items-center justify-center py-4 px-4 overflow-hidden">
    <div class="w-full max-w-5xl h-full bg-white rounded-[2rem] shadow-2xl shadow-indigo-100/50 border border-slate-200 overflow-hidden flex flex-col relative">
        
        @php
            $otherUser = $conversation->participant_1 == Auth::id() ? $conversation->participant2 : $conversation->participant1;
            $routePrefix = Auth::user()->role === 'expert' ? 'dashboard.expert.chat.' : 'dashboard.chat.';
        @endphp

        <!-- Header -->
        <div class="bg-white/90 backdrop-blur-xl border-b border-slate-100 p-4 flex items-center justify-between z-20 absolute top-0 left-0 right-0 h-20">
            <div class="flex items-center gap-4">
                <a href="{{ route($routePrefix . 'index') }}" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition hover:text-indigo-600">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>
                </a>
                
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="{{ $otherUser->avatar_path ? asset('uploads/' . $otherUser->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&background=random&color=fff&size=128' }}" 
                             class="w-11 h-11 rounded-full object-cover border-2 border-white shadow-sm"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($otherUser->name) }}&background=random&color=fff&size=128'">
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                    </div>
                    <div>
                        <h1 class="font-bold text-slate-900 leading-tight text-lg">{{ $otherUser->name }}</h1>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                            <span>
                                @if($conversation->type == 'project_offer')
                                    {{ __('Project Discussion') }}
                                @elseif($conversation->type == 'service_purchase')
                                    {{ __('Service Order') }} #{{ $conversation->reference_id }}
                                @else
                                    {{ __('Chat') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="w-10 h-10 rounded-full hover:bg-slate-50 text-slate-400 hover:text-indigo-600 transition flex items-center justify-center" title="Refresh">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <div class="h-6 w-px bg-slate-200 mx-1"></div>
                <button class="w-10 h-10 rounded-full hover:bg-red-50 text-slate-400 hover:text-red-500 transition flex items-center justify-center" title="Report">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </button>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 pt-24 pb-24 bg-[#F8FAFC] custom-scrollbar" id="messages-container">
            <!-- Decorative BG Pattern -->
            <div class="fixed inset-0 pointer-events-none opacity-[0.015] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>

            <div class="text-center my-6">
                <span class="bg-slate-100 text-slate-400 text-xs font-bold px-3 py-1 rounded-full">{{ $conversation->created_at->format('M d, Y') }}</span>
            </div>

            @foreach($conversation->messages as $message)
                @php
                    $isMe = $message->sender_id == Auth::id();
                    $user = $isMe ? Auth::user() : $otherUser;
                @endphp
                <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} group animate-fade-in-up">
                    <div class="flex max-w-[85%] sm:max-w-[70%] gap-3 {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                        
                        <!-- Avatar -->
                        <div class="flex-shrink-0 self-end mb-1">
                             <img src="{{ $user->avatar_path ? asset('uploads/' . $user->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background='.($isMe ? 'indigo' : 'random').'&color=fff&size=64' }}" 
                                 class="w-8 h-8 rounded-full object-cover shadow-sm border border-white"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff&size=64'">
                        </div>

                        <!-- Message Bubble -->
                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                            <div class="px-5 py-3.5 shadow-md text-sm leading-relaxed relative
                                {{ $isMe 
                                    ? 'bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-[1.2rem] rounded-br-sm rtl:rounded-br-[1.2rem] rtl:rounded-bl-sm' 
                                    : 'bg-white text-slate-700 border border-slate-100 rounded-[1.2rem] rounded-bl-sm rtl:rounded-bl-[1.2rem] rtl:rounded-br-sm' 
                                }}">
                                <p class="whitespace-pre-wrap break-words">{{ $message->content }}</p>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 mt-1.5 px-1 flex items-center gap-1">
                                {{ $message->created_at->format('h:i A') }}
                                @if($isMe)
                                    <i class="fa-solid fa-check-double {{ $message->is_read ? 'text-indigo-500' : 'text-slate-300' }}"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Input Area -->
        <div class="absolute bottom-0 left-0 right-0 bg-white p-4 sm:p-5 z-20 border-t border-slate-100">
            <form id="chat-form" action="{{ route($routePrefix . 'send', $conversation->id) }}" method="POST" class="flex items-end gap-3 max-w-5xl mx-auto">
                @csrf
                <button type="button" class="flex-shrink-0 w-12 h-12 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-indigo-600 transition flex items-center justify-center">
                    <i class="fa-solid fa-paperclip text-lg"></i>
                </button>
                
                <div class="flex-1 bg-slate-50 hover:bg-slate-100 focus-within:bg-white focus-within:ring-2 focus-within:ring-indigo-100 transition rounded-2xl border border-slate-200">
                    <textarea name="content" required rows="1" placeholder="{{ __('Type your message...') }}" 
                           class="w-full bg-transparent border-none px-4 py-3.5 focus:ring-0 text-slate-700 placeholder:text-slate-400 resize-none max-h-32 custom-scrollbar rtl:text-right"
                           autocomplete="off" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                </div>

                <button type="submit" class="flex-shrink-0 h-12 px-6 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition transform hover:-translate-y-1 flex items-center justify-center gap-2 font-bold">
                    <span>{{ __('Send') }}</span>
                    <i class="fa-solid fa-paper-plane text-lg rtl:rotate-180"></i>
                </button>
            </form>
        </div>

    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.3s ease-out forwards; }
</style>

<script>
    const container = document.getElementById('messages-container');
    const chatForm = document.getElementById('chat-form');
    const assetUrl = "{{ asset('uploads/') }}";
    const currentUserId = {{ Auth::id() }};
    
    function scrollToBottom() {
        container.scrollTop = container.scrollHeight;
    }

    // Scroll immediately
    scrollToBottom();
    window.onload = scrollToBottom;

    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const textarea = chatForm.querySelector('textarea[name="content"]');
            const content = textarea.value.trim();
            if (!content) return;
            
            const btn = chatForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-lg"></i>';
            
            axios.post(chatForm.action, {
                content: content
            }, {
                headers: {
                    'X-Socket-ID': typeof window.Echo !== 'undefined' ? window.Echo.socketId() : ''
                }
            }).then(response => {
                textarea.value = '';
                textarea.style.height = '';
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                
                if (response.data && response.data.message) {
                    appendOutgoingMessage(response.data.message);
                } else {
                    window.location.reload();
                }
            }).catch(error => {
                console.error(error);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        });
    }

    function appendOutgoingMessage(msg) {
        let time = '';
        if (msg.created_at) {
            const dateObj = new Date(msg.created_at);
            time = dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
        
        const avatarUrl = msg.sender_avatar ? assetUrl + '/' + msg.sender_avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(msg.sender_name) + '&background=indigo&color=fff&size=64';

        const bubbleHtml = `
            <div class="flex w-full justify-end group animate-fade-in-up">
                <div class="flex max-w-[85%] sm:max-w-[70%] gap-3 flex-row-reverse">
                    <div class="flex-shrink-0 self-end mb-1">
                         <img src="${avatarUrl}" class="w-8 h-8 rounded-full object-cover shadow-sm border border-white">
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="px-5 py-3.5 shadow-md text-sm leading-relaxed relative bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-[1.2rem] rounded-br-sm rtl:rounded-br-[1.2rem] rtl:rounded-bl-sm">
                            <p class="whitespace-pre-wrap break-words">${msg.content}</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 mt-1.5 px-1 flex items-center gap-1">
                            ${time} <i class="fa-solid fa-check-double text-slate-300"></i>
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', bubbleHtml);
        scrollToBottom();
    }
    // Laravel Echo Listener
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.Echo !== 'undefined') {
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ Auth::id() }};
            const assetUrl = "{{ asset('uploads/') }}";
            
            window.Echo.private('chat.' + conversationId)
                .listen('.message.sent', (e) => {
                    if (e.sender_id !== currentUserId) {
                        appendIncomingMessage(e);
                    }
                });
        }
    });

    function appendIncomingMessage(msg) {
        let time = '';
        if (msg.created_at) {
            const dateObj = new Date(msg.created_at);
            time = dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
        
        const avatarUrl = msg.sender_avatar ? assetUrl + '/' + msg.sender_avatar : 'https://ui-avatars.com/api/?name=User&background=random&color=fff&size=64';

        const bubbleHtml = `
            <div class="flex w-full justify-start group animate-fade-in-up">
                <div class="flex max-w-[85%] sm:max-w-[70%] gap-3 flex-row">
                    <div class="flex-shrink-0 self-end mb-1">
                         <img src="${avatarUrl}" class="w-8 h-8 rounded-full object-cover shadow-sm border border-white">
                    </div>
                    <div class="flex flex-col items-start">
                        <div class="px-5 py-3.5 shadow-md text-sm leading-relaxed relative bg-white text-slate-700 border border-slate-100 rounded-[1.2rem] rounded-bl-sm rtl:rounded-bl-[1.2rem] rtl:rounded-br-sm">
                            <p class="whitespace-pre-wrap break-words">${msg.content}</p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 mt-1.5 px-1 flex items-center gap-1">
                            ${time}
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', bubbleHtml);
        scrollToBottom();
    }
</script>
@endsection
