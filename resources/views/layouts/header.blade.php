@php
// Get current locale and direction
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';

// Platform name - Use translation key
$platformName = __('header.PLATFORM_NAME', [], $currentLang);

// Language switch text and code
$targetLangCode = $currentLang === 'en' ? 'ar' : 'en';
$targetLangText = $currentLang === 'en'
? __('header.LANG_ARABIC', [], $currentLang)
: __('header.LANG_ENGLISH', [], $currentLang);

// Language switch URL - preserve current route and query parameters, replace existing lang
$currentUrl = request()->url();
$currentQuery = request()->query();
$currentQuery['lang'] = $targetLangCode;
$switchLangUrl = $currentUrl . '?' . http_build_query($currentQuery);
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $direction }}">

<head>
    @include('partials.google-analytics')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $platformName }}@if(isset($title)) - {{ $title }}@endif</title>

    {{-- SEO Meta: hreflang + schema (injected from child views via @push) --}}
    @stack('seo_head')

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    {{-- External CSS/JS Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">

    {{-- Custom Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v=2.1">

    {{-- Tailwind Configuration --}}
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
    <script>
        // Force dark mode layout
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme', 'dark');
    </script>

    {{-- Custom Styles --}}
    <style>
        /* ─── Typography ─── */
        body, input, button, select, textarea { font-family: 'Tajawal', sans-serif; }
        body { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        /* ─── Global Background Grid ─── */
        body {
            background-color: var(--bg);
            color: var(--text-primary);
            background-image:
                radial-gradient(ellipse at 20% 20%, var(--radial-1) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, var(--radial-2) 0%, transparent 55%);
        }

        /* Fix input text color inside white/light containers */
        input, select, textarea {
            color: #0f172a;
        }
        .dark input:not([class*="text-white"]):not([class*="text-gray"]):not([class*="text-slate"]),
        .dark select:not([class*="text-white"]):not([class*="text-gray"]):not([class*="text-slate"]),
        .dark textarea:not([class*="text-white"]):not([class*="text-gray"]):not([class*="text-slate"]) {
            color: #0f172a;
        }
        .bg-white input, .bg-white select, .bg-white textarea,
        input.bg-white, select.bg-white, textarea.bg-white,
        input[class*="bg-white"], select[class*="bg-white"], textarea[class*="bg-white"],
        .bg-slate-50 input, .bg-slate-100 input, .bg-slate-200 input {
            color: #0f172a !important;
        }

        input::placeholder, textarea::placeholder {
            color: #94a3b8;
        }

        /* Subtle dot-grid overlay on the entire page */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, var(--grid-dot) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        /* ─── Glassmorphism Navbar ─── */
        .glass {
            background: rgba(255, 255, 255, 0.82);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .dark .glass {
            background: rgba(11, 17, 32, 0.82);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        /* ─── Scrollbar ─── */
        * { scrollbar-width: thin; scrollbar-color: rgba(13,148,136,0.3) transparent; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(13,148,136,0.35); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(13,148,136,0.6); }

        /* ─── Ensure content above body::before ─── */
        header, main, footer { position: relative; z-index: 1; }
    </style>

    @stack('styles')
</head>

<body class="text-slate-100 flex flex-col min-h-screen" dir="{{ $direction }}">

    @if(request()->hasCookie('impersonation_token') && \Illuminate\Support\Facades\Auth::check())
        <div class="bg-red-600 text-white text-center py-2 px-4 flex justify-between items-center fixed top-0 w-full z-[100] shadow-md">
            <span>
                <strong>⚠️ Impersonation Mode:</strong> You are currently viewing the platform as <strong>{{ auth()->user()->name }}</strong>. Actions you take will be logged.
            </span>
            <form action="{{ route('impersonate.stop') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-white text-red-600 px-3 py-1 rounded shadow text-sm font-bold hover:bg-red-50">
                    Return to Admin
                </button>
            </form>
        </div>
        <!-- Add margin to body to push down content below the fixed banner -->
        <style>body { padding-top: 48px; }</style>
    @endif

    <header class="fixed w-full top-0 z-50 transition-all duration-500 glass @if(request()->hasCookie('impersonation_token')) mt-[48px] @endif" 
            x-data="{ 
                mobileMenuOpen: false, 
                scrolled: false 
            }" 
            x-init="
                window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 });
                document.documentElement.classList.add('dark');
            " 
            :class="scrolled ? 'shadow-lg shadow-black/30' : ''">
        <nav class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/icon.png') }}"
                        onerror="this.src='https://placehold.co/40x40/0d9488/0b1120?text=R'"
                        alt="Logo"
                        class="h-10 w-10 rounded-full shadow-md object-cover ring-2 ring-brand-green/30">
                    <span class="text-xl font-black text-slate-900 dark:text-white group-hover:text-brand-green transition-colors duration-300">
                        {{ $platformName }}
                    </span>
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden lg:flex items-center gap-6 text-sm">
                    <a href="{{ route('legal_assistant.public') }}"
                        class="text-brand-green font-bold transition whitespace-nowrap bg-brand-green/10 px-3 py-1.5 rounded-full flex items-center gap-1.5 border border-brand-green/25 hover:bg-brand-green hover:text-dark-navy group text-xs">
                        <i class="fa-solid fa-robot group-hover:animate-bounce"></i>
                        {{ __('header.NAV_AI_ASSISTANT', [], $currentLang) }}
                    </a>
                    <a href="{{ route('ai.packages') }}"
                        class="text-emerald-400 font-bold transition-colors duration-200 whitespace-nowrap flex items-center gap-1 hover:text-emerald-300">
                        <i class="fa-solid fa-gem text-xs"></i>
                        {{ __('header.NAV_PACKAGES', [], $currentLang) }}
                    </a>
                    <a href="{{ route('how-it-works') }}"
                        class="text-slate-600 dark:text-slate-300 hover:text-brand-green font-semibold transition-colors duration-200 whitespace-nowrap">
                        {{ __('header.NAV_HOW_IT_WORKS', [], $currentLang) }}
                    </a>
                    <a href="{{ route('services.browse') }}"
                        class="text-slate-600 dark:text-slate-300 hover:text-brand-green font-semibold transition-colors duration-200 whitespace-nowrap">
                        {{ __('header.NAV_SERVICES', [], $currentLang) }}
                    </a>
                    <a href="{{ route('requests.browse') }}"
                        class="text-slate-600 dark:text-slate-300 hover:text-brand-green font-semibold transition-colors duration-200 whitespace-nowrap">
                        {{ __('header.NAV_REQUESTS', [], $currentLang) }}
                    </a>
                    <a href="{{ route('suppliers.browse') }}"
                        class="text-slate-600 dark:text-slate-300 hover:text-brand-green font-semibold transition-colors duration-200 whitespace-nowrap">
                        {{ __('header.NAV_SUPPLIERS', [], $currentLang) }}
                    </a>
                </div>

                {{-- Desktop Actions --}}
                <div class="hidden lg:flex items-center gap-3 text-sm">
                    {{-- Language Switcher --}}
                    <a href="{{ $switchLangUrl }}"
                        class="text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-brand-green border border-slate-200 dark:border-white/10 hover:border-brand-green/30 px-2.5 py-1.5 rounded-full transition-all duration-200">
                        {{ $targetLangText }}
                    </a>

                    @guest
                    {{-- Guest: Show Start Now button directed to Login --}}
                    <a href="{{ route('login') }}"
                        class="bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy px-5 py-2 rounded-full font-black shadow-green-glow hover:opacity-90 transition-all duration-200 hover:-translate-y-0.5">
                        {{ __('header.BTN_START_NOW', [], $currentLang) }}
                    </a>
                    @else
                    {{-- Authenticated: Show Notifications, Messages, Dashboard and Logout buttons --}}
                    
                    {{-- Notifications Dropdown --}}
                    <div x-data="notificationDropdown()" class="relative">
                        <button @click="toggleDropdown()" 
                                class="text-gray-600 hover:text-brand-primary p-2 rounded-full transition relative group" 
                                aria-label="Notifications">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span x-show="Number(unreadCount) > 0" 
                                  x-cloak
                                  class="absolute -top-1 {{ $direction === 'rtl' ? '-left-1' : '-right-1' }} min-w-[20px] h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1 border-2 border-white">
                            </span>
                        </button>

                        {{-- Dropdown Panel --}}
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute {{ $direction === 'rtl' ? 'left-0' : 'right-0' }} mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50"
                             style="display: none;">
                            
                            {{-- Header --}}
                            <div class="bg-gradient-to-r from-brand-primary to-brand-secondary p-4 text-white">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold">{{ __('header.NOTIFICATIONS', [], $currentLang) }}</h3>
                                    <button @click="markAllAsRead()" 
                                            x-show="unreadCount > 0"
                                            class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition">
                                        {{ __('header.MARK_ALL_READ', [], $currentLang) }}
                                    </button>
                                </div>
                            </div>

                            {{-- Notifications List --}}
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="loading">
                                    <div class="flex items-center justify-center py-12">
                                        <svg class="animate-spin h-8 w-8 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </template>

                                <template x-if="!loading && notifications.length === 0">
                                    <div class="text-center py-12 text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="font-semibold">{{ __('header.NO_NOTIFICATIONS', [], $currentLang) }}</p>
                                    </div>
                                </template>

                                <template x-if="!loading && notifications.length > 0">
                                    <div>
                                        <template x-for="notification in notifications" :key="notification.id">
                                            <div @click="markAsRead(notification.id, notification.data.url)" 
                                                 :class="notification.read_at ? 'bg-white' : 'bg-blue-50'"
                                                 class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition group">
                                                <div class="flex gap-3">
                                                    {{-- Icon based on type --}}
                                                    <div class="flex-shrink-0">
                                                        <div :class="getNotificationIconClass(notification.type)" 
                                                             class="w-10 h-10 rounded-full flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path x-show="notification.type.includes('Message')" d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                                                                <path x-show="notification.type.includes('Service')" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"></path>
                                                                <path x-show="notification.type.includes('Review')" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                                <path x-show="notification.type.includes('Dispute')" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    {{-- Content --}}
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-gray-900 mb-1" x-text="notification.data.title || 'Notification'"></p>
                                                        <p class="text-sm text-gray-600 line-clamp-2" x-text="notification.data.message"></p>
                                                        <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                                                    </div>

                                                    {{-- Unread indicator --}}
                                                    <div x-show="!notification.read_at" class="flex-shrink-0">
                                                        <div class="w-2 h-2 bg-brand-primary rounded-full"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- Footer --}}
                            <div class="bg-gray-50 p-3 text-center border-t">
                                <a href="{{ route('dashboard') }}" 
                                   class="text-sm font-semibold text-brand-primary hover:text-brand-secondary transition">
                                    {{ __('header.VIEW_ALL_NOTIFICATIONS', [], $currentLang) }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Authenticated: Show Messages, Dashboard and a Logout buttons --}}
                    @php
                        $chatRoute = Auth::user() && Auth::user()->role === 'expert' ? 'dashboard.expert.chat.index' : 'dashboard.chat.index';
                    @endphp
                    <a href="{{ route($chatRoute) }}"
                       class="text-gray-600 hover:text-brand-primary p-2 rounded-full transition relative group" aria-label="{{ __('header.NAV_MESSAGES', [], $currentLang) }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white hidden"></span>
                    </a>
                    <a href="{{ Auth::user() && in_array(Auth::user()->role, ['expert', 'freelancer']) ? route('dashboard.expert') : route('dashboard') }}"
                        class="bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy px-5 py-2 rounded-full font-black shadow-green-glow hover:opacity-90 transition-all duration-200 hover:-translate-y-0.5">
                        {{ __('header.BTN_DASHBOARD', [], $currentLang) }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-gray-600 hover:text-red-600 font-bold transition px-3 py-2">
                            {{ __('header.BTN_LOGOUT', [], $currentLang) }}
                        </button>
                    </form>
                    @endguest
                </div>

                {{-- Mobile Menu Toggle --}}
                <div class="lg:hidden flex items-center gap-3">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-gray-600 p-1"
                        aria-label="{{ __('header.ARIA_TOGGLE_MENU', [], $currentLang) }}">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="lg:hidden mt-4 pb-4 border-t pt-4">
                <div class="flex flex-col gap-4">
                    <a href="{{ route('legal_assistant.public') }}"
                        class="text-brand-primary font-bold flex items-center gap-2 bg-brand-primary/10 p-2 rounded-lg border border-brand-primary/20">
                        <i class="fa-solid fa-robot"></i> {{ __('header.NAV_AI_ASSISTANT', [], $currentLang) }}
                    </a>
                    <a href="{{ route('ai.packages') }}"
                        class="text-emerald-400 font-bold flex items-center gap-2 bg-emerald-500/10 p-2 rounded-lg border border-emerald-500/20">
                        <i class="fa-solid fa-gem"></i> {{ __('header.NAV_PACKAGES', [], $currentLang) }}
                    </a>
                    <a href="{{ route('how-it-works') }}"
                        class="text-gray-600 font-bold">
                        {{ __('header.NAV_HOW_IT_WORKS', [], $currentLang) }}
                    </a>
                    <a href="{{ route('services.browse') }}"
                        class="text-gray-600 font-bold">
                        {{ __('header.NAV_SERVICES', [], $currentLang) }}
                    </a>
                    <a href="{{ route('requests.browse') }}"
                        class="text-gray-600 font-bold">
                        {{ __('header.NAV_REQUESTS', [], $currentLang) }}
                    </a>
                    <a href="{{ route('suppliers.browse') }}"
                        class="text-gray-600 font-bold">
                        {{ __('header.NAV_SUPPLIERS', [], $currentLang) }}
                    </a>
                    <hr>
                    <a href="{{ $switchLangUrl }}"
                        class="text-gray-600 font-bold">
                        {{ $targetLangText }}
                    </a>
                    @guest
                    {{-- Guest: Show Start Now button directed to Login --}}
                    <a href="{{ route('login') }}"
                        class="bg-brand-primary text-white px-6 py-2.5 rounded-full font-bold text-center">
                        {{ __('header.BTN_START_NOW', [], $currentLang) }}
                    </a>
                    @else
                    {{-- Authenticated: Show Dashboard and Logout buttons --}}
                    @php
                        $chatRouteMobile = Auth::user() && Auth::user()->role === 'expert' ? 'dashboard.expert.chat.index' : 'dashboard.chat.index';
                    @endphp
                    <a href="{{ route($chatRouteMobile) }}"
                        class="text-gray-600 font-bold">
                        {{ __('header.NAV_MESSAGES', [], $currentLang) }}
                    </a>
                    <a href="{{ Auth::user() && in_array(Auth::user()->role, ['expert', 'freelancer']) ? route('dashboard.expert') : route('dashboard') }}"
                        class="bg-brand-primary text-white px-6 py-2.5 rounded-full font-bold text-center">
                        {{ __('header.BTN_DASHBOARD', [], $currentLang) }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full text-gray-600 hover:text-red-600 font-bold text-center py-2.5">
                            {{ __('header.BTN_LOGOUT', [], $currentLang) }}
                        </button>
                    </form>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-grow pt-24">