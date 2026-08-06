<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة التحكم - @yield('title', __('admin.admin_panel') ?? 'Admin Panel')</title>
    
    {{-- Icons & Fonts --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Tailwind Configuration matching Radiif Frontend Identity --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Tajawal', 'Cairo', 'sans-serif'],
                    },
                    colors: {
                        'dark-navy':       '#0b1120',
                        'dark-card':       '#111827',
                        'dark-border':     '#1f2d40',
                        'slate-light':     '#F8FAFC',
                        'brand-primary':   '#4F46E5',
                        'brand-secondary': '#8B5CF6',
                        'brand-dark':      '#1E293B',
                        'brand-green':     '#0d9488',
                        'brand-green-dim': '#0f766e',
                        'brand-teal':      '#0d9488',
                        'brand-cyan':      '#06b6d4',
                    },
                    backgroundImage: {
                        'gradient-primary': 'linear-gradient(135deg, #4F46E5 0%, #8B5CF6 100%)',
                        'gradient-green':   'linear-gradient(135deg, #0f766e 0%, #0d9488 100%)',
                    },
                    boxShadow: {
                        'glow':       '0 0 20px rgba(79, 70, 229, 0.4)',
                        'green-glow': '0 0 20px rgba(13, 148, 136, 0.35)',
                        'teal-glow':  '0 0 15px rgba(13, 148, 136, 0.3)',
                    }
                }
            }
        }
    </script>
    
    <style>
        body, input, button, select, textarea { font-family: 'Tajawal', 'Cairo', sans-serif; }
        body {
            background-color: #0b1120;
            color: #f8fafc;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(79, 70, 229, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(13, 148, 136, 0.12) 0%, transparent 55%);
            background-attachment: fixed;
        }

        /* Ambient dot overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        /* Glassmorphism Classes */
        .glass-panel {
            background: rgba(17, 24, 39, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .glass-header {
            background: rgba(11, 17, 32, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* ══ Global Dark Theme Overrides for All Child Admin Views ══ */
        main .bg-white,
        main .bg-slate-50,
        main .bg-slate-50\/50,
        main .bg-slate-50\/30,
        main .bg-slate-100 {
            background-color: rgba(17, 24, 39, 0.85) !important;
            border-color: rgba(31, 45, 64, 0.8) !important;
            color: #f8fafc !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        main .text-slate-800,
        main .text-slate-900,
        main .text-slate-700,
        main .text-slate-600 {
            color: #ffffff !important;
        }

        main .text-slate-500,
        main .text-slate-400 {
            color: #94a3b8 !important;
        }

        main .border-slate-100,
        main .border-slate-200,
        main .border-slate-300,
        main .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        main .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: rgba(31, 45, 64, 0.8) !important;
        }

        main input:not([type="checkbox"]):not([type="radio"]),
        main select,
        main textarea {
            background-color: #0b1120 !important;
            border-color: #1f2d40 !important;
            color: #ffffff !important;
        }

        main input::placeholder,
        main textarea::placeholder {
            color: #64748b !important;
        }

        main table tr:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        main tr, main th, main td {
            border-color: rgba(31, 45, 64, 0.8) !important;
        }

        main thead tr,
        main thead th {
            background-color: rgba(11, 17, 32, 0.9) !important;
            color: #94a3b8 !important;
        }

        main .bg-gray-50,
        main .bg-gray-100 {
            background-color: rgba(17, 24, 39, 0.85) !important;
            border-color: rgba(31, 45, 64, 0.8) !important;
            color: #f8fafc !important;
        }

        main .text-gray-800,
        main .text-gray-700,
        main .text-gray-600 {
            color: #ffffff !important;
        }

        main .text-gray-500,
        main .text-gray-400 {
            color: #94a3b8 !important;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1f2d40; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #4F46E5; }
        
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="text-slate-100 antialiased overflow-hidden" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">

    <div class="flex h-screen overflow-hidden bg-dark-navy/90 relative z-10">
        
        {{-- Sidebar Overlay for Mobile --}}
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-40 bg-dark-navy/90 backdrop-blur-md lg:hidden" 
             @click="sidebarOpen = false" x-cloak></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : (document.dir === 'rtl' ? 'translate-x-full' : '-translate-x-full')" 
               class="fixed inset-y-0 z-50 flex flex-col w-72 h-screen px-4 py-6 overflow-y-auto glass-panel border-r border-dark-border rtl:border-l rtl:border-r-0 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex-shrink-0 shadow-2xl">
            
            {{-- Logo Header --}}
            <div class="flex items-center justify-between px-2 mb-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-bold text-xl shadow-glow">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-black text-white tracking-tight leading-none block">رديف</span>
                        <span class="text-[10px] text-brand-primary font-bold uppercase tracking-widest">لوحة التحكم العليا</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            {{-- Navigation --}}
            <div class="flex flex-col justify-between flex-1 space-y-6">
                <nav class="space-y-1">
                    {{-- Overview --}}
                    <a class="flex items-center px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'text-white bg-gradient-to-r from-brand-primary/30 to-brand-secondary/20 border border-brand-primary/40 shadow-glow' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} rounded-xl transition group font-bold text-sm" href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-chart-pie w-6 text-center text-brand-primary group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">{!! __('admin.overview') !!}</span>
                    </a>
                    
                    {{-- Category 1 --}}
                    <div class="pt-4 pb-1">
                        <p class="px-4 text-[11px] font-extrabold tracking-wider text-slate-500 uppercase">{!! __('admin.users_identity') !!}</p>
                    </div>
                    
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.legal.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.legal.index') }}">
                        <i class="fa-solid fa-file-contract w-5 text-center group-hover:text-brand-primary transition"></i>
                        <span class="mx-3">الإدارة القانونية</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.ai_chats.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.ai_chats.index') }}">
                        <i class="fa-solid fa-comments w-5 text-center text-sky-400 group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">محادثات البوت (AI Logs)</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.ai_feedback.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.ai_feedback.index') }}">
                        <i class="fa-solid fa-thumbs-up w-5 text-center text-emerald-400 group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">تقييمات الإجابات (Feedback)</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.ai_packages.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.ai_packages.index') }}">
                        <i class="fa-solid fa-box-open w-5 text-center text-amber-400 group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">باقات المساعد الذكي</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.azure.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.azure.index') }}">
                        <i class="fa-solid fa-cloud w-5 text-center text-blue-400 group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">تكامل Azure</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.users.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.users.index') }}">
                        <i class="fa-solid fa-users w-5 text-center group-hover:text-brand-primary transition"></i>
                        <span class="mx-3">{!! __('admin.all_users') !!}</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.companies.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.companies.index') }}">
                        <i class="fa-solid fa-building w-5 text-center group-hover:text-brand-primary transition"></i>
                        <span class="mx-3">{!! __('admin.companies') !!}</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.experts.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.experts.index') }}">
                        <i class="fa-solid fa-user-tie w-5 text-center group-hover:text-brand-primary transition"></i>
                        <span class="mx-3">{!! __('admin.experts') !!}</span>
                    </a>
                    
                    {{-- Category 2 --}}
                    <div class="pt-4 pb-1">
                        <p class="px-4 text-[11px] font-extrabold tracking-wider text-slate-500 uppercase">{!! __('admin.ecommerce_jobs') !!}</p>
                    </div>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.services.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.services.index') }}">
                        <i class="fa-solid fa-briefcase w-5 text-center group-hover:text-brand-primary transition"></i>
                        <span class="mx-3">{!! __('admin.services_board') !!}</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.sentiment.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.sentiment.index') }}">
                        <i class="fa-solid fa-brain w-5 text-center text-purple-400 group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">{!! __('admin.sentiment_tasks') !!}</span>
                    </a>
                    
                    {{-- Category 3 --}}
                    <div class="pt-4 pb-1">
                        <p class="px-4 text-[11px] font-extrabold tracking-wider text-slate-500 uppercase">{!! __('admin.platform_governance') !!}</p>
                    </div>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.disputes.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group justify-between" href="{{ route('admin.disputes.index') }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-scale-balanced w-5 text-center text-emerald-400 group-hover:scale-110 transition-transform"></i>
                            <span class="mx-3">{!! __('admin.disputes_center') !!}</span>
                        </div>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.financials.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.financials.index') }}">
                        <i class="fa-solid fa-wallet w-5 text-center text-amber-400 group-hover:scale-110 transition-transform"></i>
                        <span class="mx-3">{!! __('admin.financials') !!}</span>
                    </a>

                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.settings.*') ? 'text-white bg-brand-primary/20 border border-brand-primary/30' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} rounded-xl transition text-xs font-semibold group" href="{{ route('admin.settings.index') }}">
                        <i class="fa-solid fa-gear w-5 text-center text-slate-400 group-hover:rotate-45 transition-transform duration-300"></i>
                        <span class="mx-3">{!! __('admin.system_settings') !!}</span>
                    </a>
                </nav>

                {{-- Logout Button --}}
                <div class="pt-4 border-t border-dark-border">
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-2.5 text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-xl transition text-xs font-bold gap-2">
                            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                            <span>{!! __('admin.logout') !!}</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content Window --}}
        <div class="flex flex-col flex-1 w-full h-full overflow-hidden bg-dark-navy/80 transition-all duration-300">
            
            {{-- Top Navbar --}}
            <header class="flex items-center justify-between px-6 py-4 glass-header z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white focus:outline-none transition">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    
                    {{-- Global Search Bar --}}
                    <div class="hidden md:flex relative text-slate-400 focus-within:text-brand-primary">
                        <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3.5 -translate-y-1/2 rtl:right-3.5 rtl:left-auto text-xs"></i>
                        <input type="text" 
                               class="py-2 pl-9 pr-4 rtl:pr-9 rtl:pl-4 bg-dark-card border border-dark-border rounded-full text-xs text-white placeholder-gray-500 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary w-64 transition shadow-inner" 
                               placeholder="{!! __('admin.search_placeholder') !!}">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Language Switcher --}}
                    <div class="relative" x-data="{ langOpen: false }">
                        <button @click="langOpen = !langOpen" class="flex items-center gap-2 bg-dark-card border border-dark-border px-3.5 py-1.5 rounded-full text-xs font-bold text-slate-300 hover:text-white hover:border-brand-primary/50 transition shadow-sm">
                            <i class="fa-solid fa-globe text-brand-primary"></i>
                            <span class="uppercase">{{ app()->getLocale() }}</span>
                        </button>
                        <div x-show="langOpen" @click.outside="langOpen = false" x-cloak class="absolute rtl:left-0 ltr:right-0 top-full mt-2 w-32 bg-dark-card border border-dark-border rounded-xl shadow-2xl py-2 z-50 overflow-hidden">
                            <a href="{{ url()->current() }}?lang=ar" class="block px-4 py-2 text-xs font-bold {{ app()->getLocale() == 'ar' ? 'bg-brand-primary/20 text-brand-primary' : 'text-slate-300 hover:bg-white/5' }} text-right" dir="rtl">🇸🇦 العربية</a>
                            <a href="{{ url()->current() }}?lang=en" class="block px-4 py-2 text-xs font-bold {{ app()->getLocale() == 'en' ? 'bg-brand-primary/20 text-brand-primary' : 'text-slate-300 hover:bg-white/5' }} text-left" dir="ltr">🇺🇸 English</a>
                        </div>
                    </div>

                    {{-- Live Website Link --}}
                    <a href="{{ url('/') }}" target="_blank" class="hidden sm:flex items-center gap-2 text-xs font-bold text-slate-300 hover:text-white transition bg-dark-card border border-dark-border px-4 py-2 rounded-full hover:border-emerald-500/50 shadow-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square text-emerald-400"></i>
                        <span>{!! __('admin.live_site') !!}</span>
                    </a>
                    
                    {{-- Notification Bell --}}
                    <button class="relative p-2 text-slate-400 hover:text-white transition bg-dark-card border border-dark-border rounded-full w-9 h-9 flex items-center justify-center">
                        <i class="fa-regular fa-bell text-sm"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-emerald-500 rounded-full shadow-green-glow"></span>
                    </button>
                    
                    {{-- User Profile Badge --}}
                    <div class="flex items-center gap-3 pl-3 border-l border-dark-border rtl:border-r rtl:border-l-0 rtl:pr-3">
                        <div class="hidden md:block text-right rtl:text-left">
                            <div class="text-xs font-bold text-white leading-tight">{!! __('admin.super_admin') !!}</div>
                            <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">{!! __('admin.system_control') !!}</div>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white text-xs font-bold shadow-glow ring-2 ring-brand-primary/30">
                            SA
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Dashboard Content --}}
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-8 relative">
                @if(session('success'))
                    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between text-xs font-bold" role="alert">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-base text-emerald-400"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl shadow-lg flex items-center justify-between text-xs font-bold" role="alert">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-base text-red-400"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
