<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة التحكم - @yield('title', __('admin.admin_panel') ?? 'Admin Panel')</title>
    
    {{-- Icons & Fonts --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Tailwind Configuration --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Cairo', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#4F46E5', // Indigo
                        'secondary': '#8B5CF6',
                        'sidebar': '#0F172A',
                        'sidebar-hover': '#1E293B',
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #F8FAFC; }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="text-slate-800 antialiased overflow-hidden" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">

    <div class="flex h-screen overflow-hidden bg-slate-50">
        
        {{-- Sidebar Overlay for Mobile --}}
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm lg:hidden" 
             @click="sidebarOpen = false" x-cloak></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : (document.dir === 'rtl' ? 'translate-x-full' : '-translate-x-full')" 
               class="fixed inset-y-0 z-50 flex flex-col w-72 h-screen px-4 py-8 overflow-y-auto bg-sidebar border-r border-slate-800 rtl:border-l rtl:border-r-0 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex-shrink-0 shadow-2xl">
            
            <div class="flex items-center justify-between pl-2 mb-8 pr-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-primary/30">R</div>
                    <span class="text-2xl font-black text-white tracking-tight">{!! __('admin.radiif_admin') !!}</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="flex flex-col justify-between flex-1 mt-6">
                <nav class="-mx-3 space-y-1">
                    <a class="flex items-center px-4 py-3 text-slate-300 bg-sidebar-hover rounded-xl hover:bg-white/10 hover:text-white transition group" href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-chart-pie w-6 text-center text-slate-400 group-hover:text-primary transition"></i>
                        <span class="mx-3 font-semibold">{!! __('admin.overview') !!}</span>
                    </a>
                    
                    <div class="mt-6 mb-2">
                        <p class="px-4 text-xs font-bold tracking-wider text-slate-500 uppercase">{!! __('admin.users_identity') !!}</p>
                    </div>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.legal.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.legal.index') }}">
                        <i class="fa-solid fa-file-contract w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.legal.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">الإدارة القانونية</span>
                    </a>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.users.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.users.index') }}">
                        <i class="fa-solid fa-users w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.users.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.all_users') !!}</span>
                    </a>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.companies.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.companies.index') }}">
                        <i class="fa-solid fa-building w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.companies.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.companies') !!}</span>
                    </a>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.experts.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.experts.index') }}">
                        <i class="fa-solid fa-user-tie w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.experts.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.experts') !!}</span>
                    </a>
                    
                    <div class="mt-6 mb-2">
                        <p class="px-4 text-xs font-bold tracking-wider text-slate-500 uppercase">{!! __('admin.ecommerce_jobs') !!}</p>
                    </div>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.services.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.services.index') }}">
                        <i class="fa-solid fa-briefcase w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.services.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.services_board') !!}</span>
                    </a>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.sentiment.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.sentiment.index') }}">
                        <i class="fa-solid fa-brain w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.sentiment.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.sentiment_tasks') !!}</span>
                    </a>
                    
                    <div class="mt-6 mb-2">
                        <p class="px-4 text-xs font-bold tracking-wider text-slate-500 uppercase">{!! __('admin.platform_governance') !!}</p>
                    </div>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.disputes.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group justify-between" href="{{ route('admin.disputes.index') }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-scale-balanced w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.disputes.*') ? 'text-white' : '' }}"></i>
                            <span class="mx-3 font-medium">{!! __('admin.disputes_center') !!}</span>
                        </div>
                    </a>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.financials.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.financials.index') }}">
                        <i class="fa-solid fa-wallet w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.financials.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.financials') !!}</span>
                    </a>
                    <a class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.settings.*') ? 'text-white bg-sidebar-hover' : 'text-slate-400 hover:bg-sidebar-hover hover:text-white' }} rounded-lg transition group" href="{{ route('admin.settings.index') }}">
                        <i class="fa-solid fa-gear w-6 text-center group-hover:text-white transition {{ request()->routeIs('admin.settings.*') ? 'text-white' : '' }}"></i>
                        <span class="mx-3 font-medium">{!! __('admin.system_settings') !!}</span>
                    </a>
                </nav>

                <div class="mt-8 border-t border-slate-700/50 pt-6">
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-2 text-slate-400 rounded-lg hover:bg-red-500/10 hover:text-red-500 transition group items-start rtl:items-start text-left rtl:text-right text-start">
                            <i class="fa-solid fa-right-from-bracket w-6 text-center transition"></i>
                            <span class="mx-3 font-medium">{!! __('admin.logout') !!}</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content Window --}}
        <div class="flex flex-col flex-1 w-full h-full overflow-hidden bg-slate-50 transition-all duration-300">
            
            {{-- Top Navbar --}}
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-slate-200 z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700 focus:outline-none transition">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    {{-- Search --}}
                    <div class="hidden md:flex relative text-slate-400 focus-within:text-primary">
                        <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 -translate-y-1/2 rtl:right-3 rtl:left-auto pt-0.5"></i>
                        <input type="text" class="py-2 pl-10 pr-4 rtl:pr-10 rtl:pl-4 bg-slate-100 border-transparent rounded-full text-sm focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary w-64 transition outline-none" placeholder="{!! __('admin.search_placeholder') !!}">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Language Switcher --}}
                    <div class="relative" x-data="{ langOpen: false }">
                        <button @click="langOpen = !langOpen" class="flex items-center gap-2 bg-slate-100 px-3 py-1.5 rounded-full text-sm font-bold text-slate-600 hover:text-primary hover:bg-indigo-50 transition border border-slate-200">
                            <i class="fa-solid fa-globe"></i>
                            <span class="uppercase">{{ app()->getLocale() }}</span>
                        </button>
                        <div x-show="langOpen" @click.outside="langOpen = false" x-cloak class="absolute rtl:left-0 ltr:right-0 top-full mt-2 w-32 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-50 overflow-hidden">
                            <a href="{{ url()->current() }}?lang=ar" class="block px-4 py-2 text-sm font-bold {{ app()->getLocale() == 'ar' ? 'bg-indigo-50 text-primary' : 'text-slate-700 hover:bg-slate-50' }} text-right" dir="rtl">العربية</a>
                            <a href="{{ url()->current() }}?lang=en" class="block px-4 py-2 text-sm font-bold {{ app()->getLocale() == 'en' ? 'bg-indigo-50 text-primary' : 'text-slate-700 hover:bg-slate-50' }} text-left" dir="ltr">English</a>
                        </div>
                    </div>

                    <a href="{{ url('/') }}" target="_blank" class="hidden sm:flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-primary transition bg-slate-100 px-4 py-2 rounded-full">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> {!! __('admin.live_site') !!}
                    </a>
                    
                    {{-- Notification Bell --}}
                    <button class="relative p-2 text-slate-500 hover:text-primary transition bg-slate-100 rounded-full w-10 h-10 flex items-center justify-center">
                        <i class="fa-regular fa-bell"></i>
                        <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 border-2 border-slate-100 rounded-full"></span>
                    </button>
                    
                    {{-- User Profile --}}
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200 rtl:border-r rtl:border-l-0 rtl:pr-4">
                        <div class="hidden md:block text-right rtl:text-left">
                            <div class="text-sm font-bold text-slate-800 leading-tight">{!! __('admin.super_admin') !!}</div>
                            <div class="text-[10px] font-bold text-primary uppercase tracking-wider">{!! __('admin.system_control') !!}</div>
                        </div>
                        <img class="object-cover w-10 h-10 rounded-full border-2 border-white shadow-sm" src="https://ui-avatars.com/api/?name=Super+Admin&background=4F46E5&color=fff" alt="Admin avatar">
                    </div>
                </div>
            </header>

            {{-- Main Dashboard Content --}}
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10 relative">
                @if(session('success'))
                    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between" role="alert">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                            <span class="font-bold">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between" role="alert">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                            <span class="font-bold">{{ session('error') }}</span>
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
