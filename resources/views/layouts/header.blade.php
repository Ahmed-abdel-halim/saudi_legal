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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $platformName }}@if(isset($title)) - {{ $title }}@endif</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    {{-- External CSS/JS Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    {{-- Custom Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v=2.1">

    {{-- Tailwind Configuration --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Cairo', 'sans-serif'],
                    },
                    colors: {
                        'dark-navy': '#0F172A',
                        'slate-light': '#F8FAFC',
                        'brand-primary': '#4F46E5',
                        'brand-secondary': '#8B5CF6',
                        'brand-dark': '#1E293B',
                        'brand-magenta': '#d946ef',
                        'brand-teal': '#0d9488',
                        'brand-cyan': '#06b6d4',
                    },
                    backgroundImage: {
                        'gradient-primary': 'linear-gradient(135deg, #4F46E5 0%, #8B5CF6 100%)',
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(79, 70, 229, 0.4)',
                        'teal-glow': '0 0 15px rgba(13, 148, 136, 0.3)',
                    }
                }
            }
        }
    </script>

    {{-- Custom Styles --}}
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-light text-gray-800 flex flex-col min-h-screen" dir="{{ $direction }}">

    <header class="fixed w-full top-0 z-50 transition-all duration-300 glass shadow-sm" x-data="{ mobileMenuOpen: false }">
        <nav class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/icon.png') }}"
                        onerror="this.src='https://placehold.co/40x40/4F46E5/FFFFFF?text=R'"
                        alt="Logo"
                        class="h-10 w-10 rounded-full shadow-sm object-cover">
                    <span class="text-2xl font-bold text-dark-navy group-hover:text-brand-primary transition-colors">
                        {{ $platformName }}
                    </span>
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('how-it-works') }}"
                        class="text-gray-600 hover:text-brand-primary font-bold transition whitespace-nowrap">
                        {{ __('header.NAV_HOW_IT_WORKS', [], $currentLang) }}
                    </a>
                    <a href="{{ route('services.browse') }}"
                        class="text-gray-600 hover:text-brand-primary font-bold transition whitespace-nowrap">
                        {{ __('header.NAV_SERVICES', [], $currentLang) }}
                    </a>
                    <a href="{{ route('requests.browse') }}"
                        class="text-gray-600 hover:text-brand-primary font-bold transition whitespace-nowrap">
                        {{ __('header.NAV_REQUESTS', [], $currentLang) }}
                    </a>
                    <a href="{{ route('suppliers.browse') }}"
                        class="text-gray-600 hover:text-brand-primary font-bold transition whitespace-nowrap">
                        {{ __('header.NAV_SUPPLIERS', [], $currentLang) }}
                    </a>
                </div>

                {{-- Desktop Actions --}}
                <div class="hidden md:flex items-center gap-4">
                    {{-- Language Switcher --}}
                    <a href="{{ $switchLangUrl }}"
                        class="text-sm font-bold text-gray-500 hover:text-brand-primary border border-gray-200 px-3 py-1 rounded-full transition">
                        {{ $targetLangText }}
                    </a>

                    {{-- Authentication Links --}}
                    @guest
                    {{-- Guest: Show Login and Register buttons --}}
                    <a href="{{ route('login') }}"
                        class="text-gray-600 hover:text-brand-primary font-bold transition">
                        {{ __('header.BTN_LOGIN', [], $currentLang) }}
                    </a>
                    <a href="{{ route('register.company', ['type' => 'supplier']) }}"
                        class="bg-brand-primary text-white px-6 py-2.5 rounded-full font-bold shadow-lg hover:bg-opacity-90 transition-all">
                        {{ __('header.BTN_START_NOW', [], $currentLang) }}
                    </a>
                    @else
                    {{-- Authenticated: Show Dashboard and Logout buttons --}}
                    <a href="{{ route('dashboard') }}"
                        class="bg-brand-primary text-white px-6 py-2.5 rounded-full font-bold shadow-lg hover:bg-opacity-90 transition-all">
                        {{ __('header.BTN_DASHBOARD', [], $currentLang) }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-gray-600 hover:text-red-600 font-bold transition px-4 py-2.5">
                            {{ __('header.BTN_LOGOUT', [], $currentLang) }}
                        </button>
                    </form>
                    @endguest
                </div>

                {{-- Mobile Menu Toggle --}}
                <div class="md:hidden flex items-center gap-3">
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
                class="md:hidden mt-4 pb-4 border-t pt-4">
                <div class="flex flex-col gap-4">
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
                    {{-- Guest: Show Login and Register buttons --}}
                    <a href="{{ route('login') }}"
                        class="text-gray-600 font-bold">
                        {{ __('header.BTN_LOGIN', [], $currentLang) }}
                    </a>
                    <a href="{{ route('register.company', ['type' => 'supplier']) }}"
                        class="bg-brand-primary text-white px-6 py-2.5 rounded-full font-bold text-center">
                        {{ __('header.BTN_START_NOW', [], $currentLang) }}
                    </a>
                    @else
                    {{-- Authenticated: Show Dashboard and Logout buttons --}}
                    <a href="{{ route('dashboard') }}"
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