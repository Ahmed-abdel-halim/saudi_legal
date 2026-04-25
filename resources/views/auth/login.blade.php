{{-- resources/views/auth/login.blade.php - Login Page --}}

@php
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';
@endphp

<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $direction }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.AUTH_WELCOME', [], $currentLang) }} - {{ __('auth.PLATFORM_NAME', [], $currentLang) }}</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Cairo Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Cairo', 'sans-serif']
                    },
                    colors: {
                        'dark-navy': '#0F172A',
                        'slate-light': '#F1F5F9',
                        'brand-teal': '#1B7A7E',
                        'brand-magenta': '#D946EF',
                        'slate-card': '#1E293B',
                    },
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .logo-glow-auth {
            filter: drop-shadow(0 0 20px rgba(95, 211, 211, 0.8));
        }

        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                filter: drop-shadow(0 0 20px rgba(95, 211, 211, 0.8));
            }

            50% {
                filter: drop-shadow(0 0 35px rgba(95, 211, 211, 1));
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .logo-pulse {
            animation: pulse-glow 3s ease-in-out infinite;
        }

        /* Form Input Styling */
        .form-input-enhanced {
            transition: all 0.3s ease;
        }

        .form-input-enhanced:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(27, 122, 126, 0.15);
        }

        /* Button hover effect */
        .btn-login:hover {
            box-shadow: 0 8px 25px rgba(217, 70, 239, 0.4);
        }

        /* Background decoration */
        .bg-decoration {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(95, 211, 211, 0.2), rgba(217, 70, 239, 0.1));
            filter: blur(60px);
        }
    </style>
</head>

<body class="bg-slate-light font-sans">

    <div class="min-h-screen flex">

        {{-- Left Section - Branding (Hidden on Mobile) --}}
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-dark-navy to-slate-card items-center justify-center p-12 text-center relative overflow-hidden">
            {{-- Background Decorations --}}
            <div class="bg-decoration w-96 h-96 -top-20 -left-20"></div>
            <div class="bg-decoration w-80 h-80 -bottom-10 -right-10"></div>

            <div class="z-10 animate-fade-in-up">
                <img src="{{ asset('images/icon.png') }}"
                    onerror="this.src='https://placehold.co/120x120/0F172A/FFFFFF?text=R'"
                    alt="{{ __('auth.PLATFORM_NAME', [], $currentLang) }}"
                    class="h-32 w-32 rounded-full mx-auto mb-8 logo-glow-auth logo-pulse">

                <h1 class="text-5xl font-extrabold text-white mb-6">
                    {{ __('auth.PLATFORM_NAME', [], $currentLang) }}
                </h1>

                <p class="text-xl text-gray-300 max-w-lg mx-auto leading-relaxed">
                    {{ __('auth.AUTH_LOGIN_TO_CONTINUE', [], $currentLang) }}
                </p>

                {{-- Feature highlights --}}
                <div class="mt-12 grid grid-cols-2 gap-6 max-w-md mx-auto">
                    <div class="text-left p-4 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-teal/20 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-sm">{{ __('auth.FEATURE_SECURE', [], $currentLang) }}</h3>
                    </div>
                    <div class="text-left p-4 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-magenta/20 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-brand-magenta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-sm">{{ __('auth.FEATURE_FAST', [], $currentLang) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Section - Form (Full-width on Mobile) --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white p-8 md:p-12">
            <div class="max-w-md w-full animate-fade-in-up">

                {{-- Logo for Mobile --}}
                <div class="lg:hidden text-center mb-8">
                    <a href="{{ route('home') }}" class="flex items-center justify-center gap-2">
                        <img src="{{ asset('images/icon.png') }}"
                            onerror="this.src='https://placehold.co/40x40/0F172A/FFFFFF?text=R'"
                            alt="{{ __('auth.PLATFORM_NAME', [], $currentLang) }}"
                            class="h-10 w-10 rounded-full">
                        <span class="text-2xl font-bold text-dark-navy">{{ __('auth.PLATFORM_NAME', [], $currentLang) }}</span>
                    </a>
                </div>

                <h2 class="text-3xl font-bold text-dark-navy mb-4">
                    {{ __('auth.AUTH_WELCOME', [], $currentLang) }}
                </h2>
                <p class="text-gray-600 mb-8">
                    {{ __('auth.AUTH_LOGIN_TO_CONTINUE', [], $currentLang) }}
                </p>

                {{-- Messages Area --}}
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
                @endif

                @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-6">
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
                @endif

                @if(request('error') == 'invalid_credentials')
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    <p class="text-sm">{{ __('auth.ERROR_INVALID_CREDENTIALS', [], $currentLang) }}</p>
                </div>
                @endif

                @if(request('error') == 'auth_required')
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    <p class="text-sm">{{ __('auth.ERROR_AUTH_REQUIRED', [], $currentLang) }}</p>
                </div>
                @endif

                @if(request('success') == 'register_company')
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-6">
                    <p class="text-sm">{{ __('auth.SUCCESS_REGISTER_COMPANY', [], $currentLang) }}</p>
                </div>
                @endif

                @if(request('success') == 'activated')
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-6">
                    <p class="text-sm">{{ __('auth.SUCCESS_ACTIVATED', [], $currentLang) }}</p>
                </div>
                @endif

                {{-- Login Form --}}
                <form id="login-form" action="{{ route('login.handle') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}
                        </label>
                        <input type="email"
                            id="email"
                            name="email"
                            required
                            value="{{ old('email') }}"
                            class="form-input-enhanced w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-teal focus:border-transparent"
                            placeholder="{{ __('auth.AUTH_EMAIL_PLACEHOLDER', [], $currentLang) }}">
                    </div>

                    {{-- Password --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                {{ __('auth.AUTH_PASSWORD_LABEL', [], $currentLang) }}
                            </label>
                            <a href="{{ route('password.request') }}"
                                class="text-sm font-medium text-brand-teal hover:text-brand-teal/80 transition-colors">
                                {{ __('auth.AUTH_FORGOT_PASSWORD', [], $currentLang) }}
                            </a>
                        </div>
                        <div class="relative">
                            <input type="password"
                                id="password"
                                name="password"
                                required
                                class="form-input-enhanced w-full px-4 py-3 {{ $direction === 'rtl' ? 'pl-10' : 'pr-10' }} border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-teal focus:border-transparent"
                                placeholder="••••••••">
                            <button type="button"
                                class="absolute {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-brand-teal transition-colors"
                                onclick="togglePassword('password', this)"
                                aria-label="{{ __('auth.TOGGLE_PASSWORD_VISIBILITY', [], $currentLang) }}">
                                <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-5 h-5 eye-slash-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input type="checkbox"
                            id="remember"
                            name="remember"
                            class="h-4 w-4 text-brand-teal focus:ring-brand-teal border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="mr-2 ml-2 block text-sm text-gray-700 cursor-pointer">
                            {{ __('auth.AUTH_REMEMBER_ME', [], $currentLang) }}
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <div>
                        <button type="submit"
                            class="btn-login w-full bg-brand-magenta text-white py-3 px-6 rounded-lg font-semibold text-lg shadow-lg hover:bg-opacity-90 transition-all transform hover:scale-[1.02]">
                            {{ __('auth.BTN_LOGIN', [], $currentLang) }}
                        </button>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="flex items-center my-8">
                    <div class="flex-grow h-[2px] bg-gradient-to-r from-transparent via-gray-300 to-gray-300"></div>
                    <span class="flex-shrink mx-4 text-gray-500 text-sm font-medium">
                        {{ __('auth.AUTH_NEW_USER', [], $currentLang) }}
                    </span>
                    <div class="flex-grow h-[2px] bg-gradient-to-l from-transparent via-gray-300 to-gray-300"></div>
                </div>

                {{-- Register Links --}}
                <div class="space-y-4">
                    <a href="{{ route('register.company') }}"
                        class="btn-register block w-full text-center bg-gradient-to-r from-brand-teal to-teal-500 text-white py-3 px-6 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
                        {{ __('auth.AUTH_CREATE_COMPANY', [], $currentLang) }}
                    </a>

                    <a href="{{ route('freelancer.register.form', ['type' => 'expert']) }}"
                        class="block w-full text-center border-2 border-brand-teal text-brand-teal py-3 px-6 rounded-lg font-semibold text-lg hover:bg-brand-teal hover:text-white transition-all duration-300 transform hover:scale-[1.02]">
                        {{ __('auth.REGISTER_AS_EXPERT', [], $currentLang) }}
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4 text-center">
                    <a href="{{ route('register.student') }}" class="text-gray-500 hover:text-brand-teal font-medium text-xs border-r border-gray-200">
                        {{ __('auth.ARE_YOU_STUDENT', [], $currentLang) }}
                    </a>
                    <a href="{{ route('register.company') }}" class="text-gray-500 hover:text-brand-teal font-medium text-xs">
                        {{ __('auth.AUTH_NEW_USER', [], $currentLang) }}
                    </a>
                </div>

                {{-- Back to Home --}}
                <p class="text-center text-sm text-gray-500 mt-6">
                    <a href="{{ route('home') }}" class="text-brand-teal hover:underline font-medium">
                        ← {{ __('auth.BACK_TO_HOME', [], $currentLang) }}
                    </a>
                </p>

            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeSlashIcon = button.querySelector('.eye-slash-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>

</body>

</html>
