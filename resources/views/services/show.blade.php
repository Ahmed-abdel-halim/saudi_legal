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
    <title>{{ $service->title }} | {{ $platformName }}</title>

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
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
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

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm text-slate-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center hover:text-indigo-600 transition font-bold">
                        <svg class="w-4 h-4 mr-2 rtl:ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        {{ __('dashboard.home') ?? 'Home' }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-slate-300 mx-2 rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <a href="{{ route('services.browse') }}" class="text-sm font-bold hover:text-indigo-600 transition">{{ __('services.SERVICES_TITLE') ?? 'Services' }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-slate-300 mx-2 rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-800 truncate max-w-[200px] md:max-w-md">{{ $service->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Service Header -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
                    <!-- Decorative Background Gradient -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
                    <div class="absolute top-0 right-40 w-64 h-64 bg-purple-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>

                    <div class="relative z-10">
                        <div class="flex flex-wrap gap-3 mb-6">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-600 border border-indigo-100">
                                {{ $service->industry ?? 'General' }}
                            </span>
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-600 border border-amber-100 gap-1.5">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span>{{ number_format($service->avg_rating ?? 5.0, 1) }}</span>
                                <span class="text-amber-600/60 font-medium">({{ $service->reviews_count ?? 0 }} reviews)</span>
                            </span>
                        </div>
                        
                        <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-6 leading-tight tracking-tight">
                            {{ $service->title }}
                        </h1>
                        
                        <div class="flex items-center gap-5 mt-8 pt-8 border-t border-slate-100/50">
                            <div class="relative group">
                                <img src="{{ $service->expert_image ?? 'https://ui-avatars.com/api/?name=Expert&background=random' }}" 
                                     alt="{{ $service->expert_name }}"
                                     class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-lg group-hover:scale-105 transition duration-300"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Expert&background=ccc';">
                                <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-green-500 border-4 border-white rounded-full"></div>
                            </div>
                            <div>
                                <p class="text-[10px] text-indigo-500 font-black uppercase tracking-widest mb-1">{{ __('dashboard.expert_label') }}</p>
                                <h3 class="font-bold text-slate-900 text-xl">{{ $service->expert_name }}</h3>
                                <p class="text-sm text-slate-500 font-medium">{{ $service->expert_title ?? 'Service Expert' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        {{ __('dashboard.about_service') }}
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed text-lg">
                        <p class="whitespace-pre-line">{{ $service->description }}</p>
                        
                        @if(!empty($service->expert_bio))
                            <div class="mt-8 pt-8 border-t border-slate-100">
                                <h3 class="text-lg font-bold text-slate-900 mb-3">{{ __('dashboard.about_expert') }}</h3>
                                <p class="whitespace-pre-line">{{ $service->expert_bio }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Skills Card -->
                @if(isset($service->skills_array) && count($service->skills_array) > 0)
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        {{ __('dashboard.skills_expertise') }}
                    </h2>
                    <div class="flex flex-wrap gap-3">
                        @foreach($service->skills_array as $skill)
                            <span class="px-5 py-2.5 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-xl font-bold text-sm border border-slate-200 hover:border-indigo-100 transition duration-300 cursor-default shadow-sm">
                                {{ trim($skill) }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Pricing & Action Card -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-indigo-100 border border-slate-100 sticky top-28">
                    <div class="mb-8 text-center">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-2">{{ __('dashboard.rate_label') }}</p>
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="text-5xl font-black text-slate-900">${{ number_format($service->hourly_rate, 2) }}</span>
                            <span class="text-lg text-slate-400 font-bold">{{ __('dashboard.per_hour') }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <a href="{{ route('services.request', $service->service_id) }}" 
                           class="flex justify-center items-center w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-xl font-bold text-lg shadow-lg shadow-indigo-200 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-indigo-300 group">
                            {{ __('dashboard.btn_request_expert') }}
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition rtl:mr-2 rtl:ml-0 rtl:group-hover:-translate-x-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                        
                        <a href="{{ route('services.contact', $service->service_id) }}"
                           class="flex justify-center items-center w-full bg-white hover:bg-slate-50 text-slate-700 border-2 border-slate-200 hover:border-slate-300 px-6 py-3.5 rounded-xl font-bold text-base transition-all duration-300">
                            <svg class="w-5 h-5 mr-2 rtl:ml-2 rtl:mr-0 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            {{ __('dashboard.btn_contact_company') }}
                        </a>
                    </div>
                
                    <!-- Company Info Mini-Section -->
                    @if($service->company_name)
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-4 group cursor-pointer p-2 hover:bg-slate-50 rounded-xl transition">
                            <div class="bg-white p-2 rounded-xl border border-slate-100 shadow-sm group-hover:border-indigo-100 transition">
                                <img src="{{ $service->company_logo ?? 'https://ui-avatars.com/api/?name=Co&background=random' }}" 
                                     class="w-10 h-10 object-contain"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Co&background=efefef';">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">{{ __('dashboard.provided_by') }}</p>
                                <p class="font-bold text-slate-900 text-sm truncate group-hover:text-indigo-600 transition">{{ $service->company_name }}</p>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-400 transition rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Trust Badges -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-sm text-slate-600">
                            <div class="w-8 h-8 flex items-center justify-center bg-green-100 text-green-600 rounded-full">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-bold">Verified Professional</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-600">
                            <div class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-bold">Secure Payment</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-600">
                            <div class="w-8 h-8 flex items-center justify-center bg-purple-100 text-purple-600 rounded-full">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                </svg>
                            </div>
                            <span class="font-bold">Full Satisfaction Guarantee</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</body>
</html>
