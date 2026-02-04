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
    <title>{{ __('services.REQUEST_EXPERT_TITLE') }} | {{ $platformName }}</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    {{-- External CSS/JS Libraries --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
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

    <div class="min-h-screen py-12 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-50/50 rounded-full mix-blend-multiply filter blur-3xl opacity-60 animate-blob pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-purple-50/50 rounded-full mix-blend-multiply filter blur-3xl opacity-60 animate-blob animation-delay-4000 pointer-events-none"></div>

        <div class="container mx-auto px-4 max-w-3xl relative z-10">
            
            <!-- Breadcrumb -->
            <nav class="flex mb-8 text-sm text-slate-500 justify-center" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="{{ route('services.show', $service->service_id) }}" class="inline-flex items-center hover:text-indigo-600 transition font-bold">
                            <svg class="w-4 h-4 mr-2 rtl:ml-2 rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('services.BTN_CANCEL') }}
                        </a>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/50 p-8 md:p-12 border border-slate-100">
                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-6 shadow-sm">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-900 mb-3">{{ __('services.REQUEST_EXPERT_TITLE') }}</h1>
                    <p class="text-slate-500 text-lg max-w-lg mx-auto leading-relaxed">
                        {{ __('services.REQUEST_EXPERT_DESC') }}
                    </p>
                </div>

                <!-- Service Info Card -->
                <div class="bg-slate-50 rounded-2xl p-6 mb-10 border border-slate-200 flex items-start gap-5 relative overflow-hidden group hover:border-indigo-200 transition-colors">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">{{ __('dashboard.service') }}</p>
                                <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $service->title }}</h3>
                                <p class="text-sm text-slate-500 font-medium">{{ __('services.PROVIDED_BY') }}: <span class="text-slate-700">{{ $service->company_name }}</span></p>
                            </div>
                            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-100">
                                <span class="text-2xl font-black text-slate-900">${{ $service->hourly_rate }}</span>
                                <span class="text-xs font-bold text-slate-400 uppercase">{{ __('dashboard.per_hour') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-8 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-bold">{{ session('success') }}</p>
                </div>
                @endif

                <!-- Request Form -->
                <form action="{{ route('services.request.send', $service->service_id) }}" method="POST" class="space-y-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_NAME') }} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <input type="text" name="name" required class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white rtl:pr-10 rtl:pl-4">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_EMAIL') }} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                    </svg>
                                </span>
                                <input type="email" name="email" required class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white rtl:pr-10 rtl:pl-4">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_COMPANY') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <input type="text" name="company" class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white rtl:pr-10 rtl:pl-4">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_PHONE') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                    </svg>
                                </span>
                                <input type="tel" name="phone" class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white rtl:pr-10 rtl:pl-4">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_PROJECT_TYPE') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                                </svg>
                            </span>
                            <select name="project_type" required class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white appearance-none rtl:pr-10 rtl:pl-4">
                                <option value="">{{ __('services.SELECT_PROJECT_TYPE') }}</option>
                                <option value="full-time">{{ __('services.PROJECT_FULL_TIME') }}</option>
                                <option value="part-time">{{ __('services.PROJECT_PART_TIME') }}</option>
                                <option value="contract">{{ __('services.PROJECT_CONTRACT') }}</option>
                                <option value="one-time">{{ __('services.PROJECT_ONE_TIME') }}</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none rtl:left-0 rtl:right-auto">
                                <svg class="w-3 h-3 text-slate-400 mr-2 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_DURATION') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <input type="text" name="duration" placeholder="{{ __('services.DURATION_PLACEHOLDER') }}" class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white rtl:pr-10 rtl:pl-4">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_BUDGET') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 rtl:right-0 rtl:left-auto rtl:pr-3 rtl:pl-0">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <input type="text" name="budget" placeholder="{{ __('services.BUDGET_PLACEHOLDER') }}" class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white rtl:pr-10 rtl:pl-4">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2.5">{{ __('services.FORM_PROJECT_DETAILS') }} <span class="text-red-500">*</span></label>
                        <textarea name="project_details" rows="5" required placeholder="{{ __('services.PROJECT_DETAILS_PLACEHOLDER') }}" class="w-full px-4 py-3.5 rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition bg-slate-50/50 focus:bg-white resize-none"></textarea>
                    </div>

                    <div class="pt-4 flex flex-col items-center gap-4">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-indigo-200 transform hover:-translate-y-1 flex items-center justify-center gap-2">
                            <span>{{ __('services.BTN_SUBMIT_REQUEST') }}</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
