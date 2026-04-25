<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('expert_dashboard.page_title') }}</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-green-200 shadow-lg">R</div>
                <div>
                    <h1 class="font-bold text-lg leading-none text-slate-800">Radiif</h1>
                    <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT DASHBOARD</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-sm font-bold text-slate-700">{{ $user->full_name ?? $user->name }}</span>
                    <span class="text-xs text-green-600 font-medium">{{ $expert_level }}</span>
                </div>
                <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm">
                    <img src="{{ $user->avatar_path ? asset('uploads/' . $user->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->full_name ?? $user->name).'&background=random&color=fff' }}" 
                         class="w-full h-full object-cover" 
                         alt="Avatar"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->full_name ?? $user->name) }}&background=random&color=fff'">
                </div>

                {{-- Notifications Dropdown --}}
                <div x-data="notificationDropdown()" class="relative" x-cloak>
                    <button @click="toggleDropdown()" 
                            class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition relative" 
                            aria-label="Notifications">
                        <i class="fa-regular fa-bell"></i>
                        <span x-show="Number(unreadCount) > 0" 
                              x-cloak
                              class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border border-white animate-pulse">
                        </span>
                    </button>

                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-[450px] bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl shadow-2xl overflow-hidden z-50 p-6 border-4 border-white/20">
                        
                            {{-- Header --}}
                            <div class="flex justify-between items-center mb-6 text-white">
                                <span class="bg-white text-indigo-600 px-3 py-1 rounded-full text-xs font-black shadow-sm flex items-center gap-1" x-show="Number(unreadCount) > 0" x-cloak>
                                    <span x-text="unreadCount"></span>
                                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                </span>
                                <h3 class="font-bold text-xl flex items-center gap-2">
                                    {{ __('header.NOTIFICATIONS') }}
                                    <span class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                                        <i class="fa-solid fa-bell text-yellow-300"></i>
                                    </span>
                                </h3>
                            </div>
    
                            {{-- List --}}
                            <div class="max-h-[400px] overflow-y-auto custom-scrollbar space-y-4">
                                <template x-if="loading">
                                    <div class="flex justify-center py-8 text-white/50">
                                        <i class="fa-solid fa-circle-notch fa-spin text-xl"></i>
                                    </div>
                                </template>
                                
                                <template x-if="!loading && notifications.length === 0">
                                    <div class="text-center py-8 text-white/50">
                                        <i class="fa-regular fa-bell-slash text-2xl mb-2 opacity-50 block"></i>
                                        <span class="text-xs">{{ __('header.NO_NOTIFICATIONS') }}</span>
                                    </div>
                                </template>
    
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div class="bg-white/10 backdrop-blur-md border border-white/10 p-4 rounded-2xl hover:bg-white/20 transition duration-300 group/item text-white">
                                        
                                        {{-- Request Notification Layout --}}
                                        <template x-if="notification.type.includes('ServiceRequest') || notification.type === 'NewServiceRequestNotification'">
                                            <div>
                                                <div class="flex justify-between items-start mb-3">
                                                    {{-- Hours Badge --}}
                                                    <div class="text-center bg-indigo-900/50 rounded-lg px-2 py-1 border border-white/10 h-fit">
                                                        <p class="text-lg font-black leading-none" x-text="notification.data.hours"></p>
                                                        <p class="text-[9px] uppercase tracking-wider opacity-75">{{ __('expert_dashboard.hours') }}</p>
                                                    </div>

                                                    {{-- Client Info --}}
                                                    <div class="flex items-center gap-3 text-end flex-row-reverse">
                                                        <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white/20 shadow-sm relative">
                                                            <template x-if="notification.data.client_avatar">
                                                                <img :src="'/uploads/' + notification.data.client_avatar" class="w-full h-full object-cover">
                                                            </template>
                                                            <template x-if="!notification.data.client_avatar">
                                                                <div class="w-full h-full bg-gradient-to-br from-white to-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                                                    <span x-text="(notification.data.client_name || 'C').charAt(0)"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <div>
                                                            <p class="font-bold text-base leading-tight" x-text="notification.data.client_name"></p>
                                                            <p class="text-xs text-indigo-100 opacity-80 mt-1 flex items-center gap-1 justify-end">
                                                                <span x-text="notification.data.service_title"></span>
                                                                <i class="fa-solid fa-layer-group text-[10px]"></i>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center justify-between gap-3 mt-4 pt-3 border-t border-white/10">
                                                    {{-- Accept Button --}}
                                                    <form :action="'/dashboard/expert/purchase/' + notification.data.request_id + '/accept'" method="POST" class="flex-1 max-w-[120px]">
                                                        <input type="hidden" name="_token" :value="document.querySelector('meta[name=&quot;csrf-token&quot;]').content">
                                                        <button type="submit" class="w-full bg-white text-indigo-700 hover:bg-indigo-50 py-2 rounded-xl text-xs font-bold transition shadow-lg shadow-indigo-900/10 flex items-center justify-center gap-2">
                                                            <span>{{ __('expert_dashboard.btn_accept') }}</span>
                                                            <i class="fa-solid fa-check circle-check rtl:rotate-0"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Time --}}
                                                    <span class="text-xs font-medium opacity-70 flex items-center gap-1">
                                                        <span x-text="notification.created_at_human || notification.created_at"></span>
                                                        <i class="fa-regular fa-clock"></i> 
                                                    </span>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Fallback for other notifications --}}
                                        <template x-if="!notification.type.includes('ServiceRequest') && notification.type !== 'NewServiceRequestNotification'">
                                            <div @click="markAsRead(notification.id, notification.data.url)" class="cursor-pointer">
                                                <div class="flex gap-3 items-center">
                                                    <div class="flex-1">
                                                        <p class="text-sm font-bold" x-text="notification.data.title"></p>
                                                        <p class="text-xs opacity-80" x-text="notification.data.message"></p>
                                                    </div>
                                                    <div x-show="!notification.read_at" class="w-2 h-2 bg-red-400 rounded-full"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                </div>

                <!-- Chat Icon -->
                <a href="{{ route('dashboard.expert.chat.index') }}" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition relative">
                    <i class="fa-regular fa-comment-dots"></i>
                    @if(\App\Models\Message::where('is_read', false)->where('sender_id', '!=', $user->id)->whereIn('conversation_id', \App\Models\Conversation::where('participant_1', $user->id)->orWhere('participant_2', $user->id)->pluck('id'))->count() > 0)
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    @endif
                </a>

                <!-- Language Toggle -->
                <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() == 'ar' ? 'en' : 'ar']) }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 transition font-bold text-sm">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_dashboard.total_balance') }}</p>
                    <h2 class="text-3xl font-bold text-slate-800">{{ number_format($total_balance, 2) }} <span class="text-sm text-slate-400 font-normal">{{ __('expert_dashboard.currency') }}</span></h2>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_dashboard.today_earnings') }}</p>
                    <h2 class="text-3xl font-bold text-slate-800">{{ number_format($today_balance, 2) }} <span class="text-sm text-slate-400 font-normal">{{ __('expert_dashboard.currency') }}</span></h2>
                </div>
                <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_dashboard.completed_tasks') }}</p>
                    <h2 class="text-3xl font-bold text-slate-800">{{ $total_tasks }} <span class="text-sm text-slate-400 font-normal">{{ __('expert_dashboard.task_unit') }}</span></h2>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="space-y-6">

                <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                    <div class="px-6 py-6">
                        <div class="flex flex-col items-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-green-600 to-green-700 rounded-full p-1 shadow-lg">
                                <img src="{{ $user->avatar_path ? asset('uploads/' . $user->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->full_name ?? $user->name).'&background=16a34a&color=fff&size=128' }}" 
                                     class="w-full h-full rounded-full object-cover bg-white" 
                                     alt="{{ $user->full_name ?? $user->name }}"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->full_name ?? $user->name) }}&background=16a34a&color=fff&size=128'">
                            </div>
                            
                            <div class="mt-4 text-center">
                                <h3 class="text-xl font-bold text-slate-800">{{ $user->full_name ?? $user->name }}</h3>
                                <p class="text-sm text-slate-500 mt-1">{{ !empty($user->job_title) ? __($user->job_title) : __('expert_dashboard.job_title_default') }}</p>
                                
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mt-4 {{ $badge_color }}">
                                    <i class="fa-solid {{ $badge_icon }}"></i>
                                    <span class="text-xs font-bold">{{ $expert_level }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3 border-t border-slate-100 pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('expert_dashboard.expert_number') }}</span>
                                <span class="font-mono font-bold text-slate-700">EXP-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('expert_dashboard.join_date') }}</span>
                                <span class="font-bold text-slate-700">{{ date('Y/m/d', strtotime($user->created_at)) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('expert_dashboard.account_status') }}</span>
                                <span class="text-green-600 font-bold flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> {{ __('expert_dashboard.active') }}</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('dashboard.expert.cv-builder') }}" class="block w-full py-2 bg-slate-50 text-slate-600 text-center rounded-lg text-sm font-bold hover:bg-slate-100 transition">{{ __('expert_dashboard.update_profile') }}</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
                    <h4 class="font-bold text-sm text-slate-700 mb-3">{{ __('expert_dashboard.quick_actions') }}</h4>
                    @if($user->role !== 'student')
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('dashboard.expert.services') }}" class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-lg hover:bg-green-50 hover:text-green-700 transition cursor-pointer">
                            <i class="fa-solid fa-box-open mb-2 text-lg"></i>
                            <span class="text-xs font-bold">{{ __('expert_dashboard.services') }}</span>
                        </a>
                        <a href="{{ route('dashboard.expert.availability') }}" class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition cursor-pointer">
                            <i class="fa-regular fa-clock mb-2 text-lg"></i>
                            <span class="text-xs font-bold">{{ __('expert_dashboard.availability') }}</span>
                        </a>
                    </div>
                    @else
                    <div class="p-4 bg-slate-50 rounded-lg text-center text-slate-500 text-sm">
                        <i class="fa-solid fa-user-graduate mb-2 text-lg block"></i>
                        {{ __('expert_dashboard.student_account') }}
                    </div>
                    @endif
                </div>

            </div>

            <div class="lg:col-span-2 space-y-6">

                
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded">{{ __('expert_dashboard.live_badge') }}</span>
                                @if($pending_count > 0)
                                    <span class="text-green-300 text-sm font-bold animate-pulse">{{ __('expert_dashboard.pending_tasks_msg', ['count' => $pending_count]) }}</span>
                                @else
                                    <span class="text-slate-400 text-sm">{{ __('expert_dashboard.no_tasks_msg') }}</span>
                                @endif
                            </div>
                            <h2 class="text-3xl font-bold mb-2">{{ __('expert_dashboard.workbench_title') }}</h2>
                            <p class="text-slate-300 text-sm max-w-md">{{ __('expert_dashboard.workbench_desc') }}</p>
                        </div>
                        
                        <a href="{{ route('dashboard.expert.workbench') }}" class="bg-green-600 hover:bg-green-500 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-green-900/50 transition transform hover:-translate-y-1 flex items-center gap-3">
                            <i class="fa-solid fa-play rtl:rotate-180"></i> {{ __('expert_dashboard.start_audit') }}
                        </a>
                    </div>
                </div>

                @if($user->expert_domain == 'law')
                {{-- SAUDI LEGAL SPECIAL CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Workbench Card --}}
                    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden group border-4 border-white/10">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 -mr-16 -mt-16 rounded-full blur-3xl transition group-hover:scale-150 duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-xl shadow-inner">
                                    <i class="fa-solid fa-gavel text-yellow-400"></i>
                                </div>
                                <span class="bg-yellow-400 text-blue-900 text-[10px] font-black px-2 py-0.5 rounded uppercase">Legal Expert</span>
                            </div>
                            <h2 class="text-xl font-black mb-1">تنقيح المساعد القانوني</h2>
                            <p class="text-blue-100/70 text-[11px] leading-relaxed mb-4">راجع إجابات الـ AI بناءً على الأنظمة السعودية.</p>
                            <a href="{{ route('dashboard.expert.legal_workbench') }}" class="inline-flex items-center gap-2 bg-white text-blue-800 px-5 py-2.5 rounded-xl font-black text-xs shadow-xl transition transform hover:-translate-y-1">
                                ابدأ التنقيح <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>
                            </a>
                        </div>
                    </div>

                    {{-- AI Assistant Card --}}
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden group border-4 border-white/10">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 -mr-16 -mt-16 rounded-full blur-3xl transition group-hover:scale-150 duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-xl shadow-inner">
                                    <i class="fa-solid fa-robot text-emerald-300"></i>
                                </div>
                                <span class="bg-emerald-400 text-teal-900 text-[10px] font-black px-2 py-0.5 rounded uppercase">NEW RAG ENGINE</span>
                            </div>
                            <h2 class="text-xl font-black mb-1">المساعد القانوني الذكي</h2>
                            <p class="text-emerald-100/70 text-[11px] leading-relaxed mb-4">اسأل الذكاء الاصطناعي وسيجيبك من 15,954 مادة قانونية.</p>
                            <a href="{{ route('dashboard.expert.legal_assistant') }}" class="inline-flex items-center gap-2 bg-white text-emerald-800 px-5 py-2.5 rounded-xl font-black text-xs shadow-xl transition transform hover:-translate-y-1">
                                اسأل المساعد <i class="fa-solid fa-comment-dots"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-clock-rotate-left"></i></span>
                             {{ __('expert_dashboard.recent_activity') }}
                        </h3>
                        <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-md">{{ __('expert_dashboard.last_5_ops') }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                            <thead class="bg-slate-50/50 text-slate-400 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="p-5 font-bold">{{ __('expert_dashboard.tbl_task_id') }}</th>
                                    <th class="p-5 font-bold">{{ __('expert_dashboard.tbl_action') }}</th>
                                    <th class="p-5 font-bold">{{ __('expert_dashboard.tbl_time') }}</th>
                                    <th class="p-5 font-bold">{{ __('expert_dashboard.tbl_value') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($history as $row)
                                <tr class="hover:bg-slate-50/80 transition duration-200 group">
                                    <td class="p-5">
                                        <span class="font-mono text-slate-500 bg-slate-100 px-2 py-1 rounded text-xs group-hover:bg-white group-hover:shadow-sm transition">#{{ $row->task_id ?? $row->id ?? 'unknown' }}</span>
                                    </td> 
                                    <td class="p-5">
                                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold border border-green-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            {{ __('expert_dashboard.status_corrected') }}
                                        </span>
                                    </td>
                                    <td class="p-5 text-slate-500 font-medium text-xs">{{ date('h:i A', strtotime($row->created_at)) }}</td>
                                    <td class="p-5 font-bold text-indigo-600">+{{ number_format($price_per_task, 2) }} <span class="text-[10px] text-slate-400">{{ __('expert_dashboard.currency') }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-300 gap-3">
                                            <i class="fa-regular fa-folder-open text-4xl"></i>
                                            <span class="text-sm font-medium">{{ __('expert_dashboard.no_activity') }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="mt-12 text-center text-slate-400 text-xs">
            &copy; {{ date('Y') }} {{ __('expert_dashboard.copyright') }}
        </div>

    </div>

    <script>
    function notificationDropdown() {
        return {
            open: false,
            loading: false,
            notifications: [],
            unreadCount: 0,
            
            init() {
                // Fetch unread count on load
                this.fetchUnreadCount();
                
                // Poll for new notifications every 30 seconds (fallback)
                setInterval(() => {
                    this.fetchUnreadCount();
                }, 30000);

                // Listen for real-time notifications via Pusher
                if (window.Echo) {
                    window.Echo.private('App.Models.User.{{ auth()->id() }}')
                        .notification((notification) => {
                            this.notifications.unshift(notification);
                            this.unreadCount++;
                            
                            // Show browser notification if permitted
                            if ('Notification' in window && Notification.permission === 'granted') {
                                new Notification(notification.data.title || 'New Notification', {
                                    body: notification.data.message || '',
                                    icon: '/images/icon.png',
                                    badge: '/images/icon.png'
                                });
                            }
                        });
                }
            },
            
            toggleDropdown() {
                this.open = !this.open;
                if (this.open && this.notifications.length === 0) {
                    this.fetchNotifications();
                }
            },
            
            async fetchUnreadCount() {
                try {
                    const response = await fetch('/notifications/unread-count', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.unreadCount = data.count;
                    }
                } catch (error) {
                    console.error('Error fetching unread count:', error);
                }
            },
            
            async fetchNotifications() {
                this.loading = true;
                try {
                    const response = await fetch('/notifications', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.notifications = data.notifications;
                        this.unreadCount = data.unread_count; // sync
                    }
                } catch (error) {
                    console.error('Error fetching notifications:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            async markAsRead(id, url) {
                try {
                    await fetch(`/notifications/${id}/read`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    });
                    
                    // Update local state
                    const notif = this.notifications.find(n => n.id === id);
                    if (notif && !notif.read_at) {
                        notif.read_at = new Date().toISOString();
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                    
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                } catch (error) {
                    console.error('Error marking as read:', error);
                }
            },
            
            async markAllAsRead() {
                try {
                    await fetch('/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    });
                    
                    this.notifications.forEach(n => n.read_at = new Date().toISOString());
                    this.unreadCount = 0;
                } catch (error) {
                    console.error('Error marking all as read:', error);
                }
            },

            getNotificationIcon(type) {
                if(type.includes('Message')) return 'fa-solid fa-envelope';
                if(type.includes('Service')) return 'fa-solid fa-briefcase';
                if(type.includes('Review')) return 'fa-solid fa-star';
                if(type.includes('Dispute')) return 'fa-solid fa-triangle-exclamation';
                return 'fa-regular fa-bell';
            },

            getNotificationIconClass(type) {
                if(type.includes('Message')) return 'bg-blue-500';
                if(type.includes('Service')) return 'bg-green-500';
                if(type.includes('Review')) return 'bg-yellow-500';
                if(type.includes('Dispute')) return 'bg-red-500';
                return 'bg-gray-400';
            }
        }
    }
    </script>
</body>
</html>
