{{-- resources/views/auth/register-company.blade.php - Company Registration Form --}}

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
    <title>{{ __('auth.AUTH_CREATE_COMPANY', [], $currentLang) }} - {{ __('auth.PLATFORM_NAME', [], $currentLang) }}</title>

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

        /* Background decoration */
        .bg-decoration {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(95, 211, 211, 0.2), rgba(217, 70, 239, 0.1));
            filter: blur(60px);
        }

        /* Form Input Styling */
        .form-input-enhanced {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #E5E7EB;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .form-input-enhanced:focus {
            outline: none;
            border-color: #1B7A7E;
            box-shadow: 0 0 0 3px rgba(27, 122, 126, 0.1);
            transform: translateY(-1px);
        }

        .form-input-enhanced::placeholder {
            color: #9CA3AF;
        }

        /* Progress Bar */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            font-size: 0.75rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .progress-step.active {
            font-weight: 700;
            color: #1B7A7E;
            background: rgba(27, 122, 126, 0.1);
        }

        .progress-step.inactive {
            color: #9CA3AF;
        }

        .progress-bar-container {
            height: 0.375rem;
            background: #E5E7EB;
            border-radius: 9999px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #1B7A7E 0%, #14b8a6 100%);
            border-radius: 9999px;
            transition: width 0.5s ease;
        }

        /* Step Transitions */
        .step-content {
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        [dir="rtl"] .step-content {
            animation: slideInRtl 0.4s ease-out;
        }

        @keyframes slideInRtl {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Checkbox */
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: #F9FAFB;
            border-radius: 0.75rem;
            border: 2px solid #E5E7EB;
        }

        .checkbox-container input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.125rem;
            cursor: pointer;
            accent-color: #1B7A7E;
        }

        /* Button hover effect */
        .btn-primary-custom:hover {
            box-shadow: 0 8px 25px rgba(217, 70, 239, 0.4);
        }

        .btn-secondary-custom:hover {
            background: #E5E7EB;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .progress-steps {
                flex-direction: column;
                gap: 0.5rem;
            }

            .progress-step {
                width: 100%;
                text-align: center;
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body class="bg-slate-light font-sans">
    @auth
    <script>
        window.location.href = '{{ route("dashboard") }}';
    </script>
    @endauth

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
                    {{ __('auth.REGISTER_SUBTITLE', [], $currentLang) }}
                </p>

                {{-- Feature highlights --}}
                <div class="mt-12 grid grid-cols-3 gap-4 max-w-md mx-auto">
                    <div class="text-center p-3 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-teal/20 rounded-lg flex items-center justify-center mb-2 mx-auto">
                            <svg class="w-5 h-5 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-xs">{{ __('auth.STEP_1_SHORT', [], $currentLang) }}</h3>
                    </div>
                    <div class="text-center p-3 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-magenta/20 rounded-lg flex items-center justify-center mb-2 mx-auto">
                            <svg class="w-5 h-5 text-brand-magenta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-xs">{{ __('auth.STEP_2_SHORT', [], $currentLang) }}</h3>
                    </div>
                    <div class="text-center p-3 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mb-2 mx-auto">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-xs">{{ __('auth.STEP_3_SHORT', [], $currentLang) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Section - Form (Full-width on Mobile) --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white p-6 md:p-10 overflow-y-auto">
            <div class="max-w-lg w-full animate-fade-in-up">

                {{-- Logo for Mobile --}}
                <div class="lg:hidden text-center mb-6">
                    <a href="{{ route('home') }}" class="flex items-center justify-center gap-2">
                        <img src="{{ asset('images/icon.png') }}"
                            onerror="this.src='https://placehold.co/40x40/0F172A/FFFFFF?text=R'"
                            alt="{{ __('auth.PLATFORM_NAME', [], $currentLang) }}"
                            class="h-10 w-10 rounded-full">
                        <span class="text-2xl font-bold text-dark-navy">{{ __('auth.PLATFORM_NAME', [], $currentLang) }}</span>
                    </a>
                </div>

                <h2 class="text-2xl md:text-3xl font-bold text-brand-teal mb-2">
                    {{ __('auth.AUTH_CREATE_COMPANY', [], $currentLang) }}
                </h2>
                <p class="text-gray-600 mb-6">
                    {{ __('auth.AUTH_LOGIN_TO_CONTINUE', [], $currentLang) }}
                </p>

                {{-- Error Messages --}}
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                {{-- Progress Bar --}}
                <div class="mb-6">
                    <div class="progress-steps">
                        <div class="progress-step active" id="step-title-1">
                            1. {{ __('auth.AUTH_STEP_ACCOUNT', [], $currentLang) }}
                        </div>
                        <div class="progress-step inactive" id="step-title-2">
                            2. {{ __('auth.AUTH_STEP_COMPANY', [], $currentLang) }}
                        </div>
                        <div class="progress-step inactive" id="step-title-3">
                            3. {{ __('auth.AUTH_STEP_AGREEMENT', [], $currentLang) }}
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" id="progress-bar" style="width: 33.33%"></div>
                    </div>
                </div>

                {{-- Form --}}
                <form id="multi-step-form" action="{{ route('register.company.handle') }}" method="POST" class="space-y-5">
                    @csrf

                    <input type="hidden" id="registration-type" name="registration-type" value="{{ $type ?? request('type', 'supplier') }}">

                    {{-- Step 1: Account Information --}}
                    <div id="step-1" class="step-content space-y-4">
                        <div>
                            <label for="full-name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('auth.AUTH_FULL_NAME', [], $currentLang) }}
                            </label>
                            <input type="text"
                                id="full-name"
                                name="full-name"
                                required
                                class="form-input-enhanced"
                                value="{{ old('full-name') }}"
                                placeholder="{{ __('auth.AUTH_FULL_NAME', [], $currentLang) }}">
                        </div>

                        <div>
                            <label for="work-email" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}
                            </label>
                            <input type="email"
                                id="work-email"
                                name="work-email"
                                required
                                class="form-input-enhanced"
                                value="{{ old('work-email') }}"
                                placeholder="example@company.com">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('auth.AUTH_PASSWORD_LABEL', [], $currentLang) }}
                            </label>
                            <div class="relative">
                                <input type="password"
                                    id="password"
                                    name="password"
                                    required
                                    class="form-input-enhanced {{ $direction === 'rtl' ? 'pl-10' : 'pr-10' }}"
                                    placeholder="••••••••"
                                    minlength="8">
                                <button type="button"
                                    class="absolute {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-brand-teal transition-colors"
                                    onclick="togglePassword('password', this)"
                                    aria-label="Toggle password visibility">
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

                        <div>
                            <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('auth.AUTH_PASSWORD_CONFIRM', [], $currentLang) }}
                            </label>
                            <div class="relative">
                                <input type="password"
                                    id="password-confirm"
                                    name="password_confirmation"
                                    required
                                    class="form-input-enhanced {{ $direction === 'rtl' ? 'pl-10' : 'pr-10' }}"
                                    placeholder="••••••••"
                                    minlength="8">
                                <button type="button"
                                    class="absolute {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-brand-teal transition-colors"
                                    onclick="togglePassword('password-confirm', this)"
                                    aria-label="Toggle password confirmation visibility">
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

                        <button type="button"
                            onclick="nextStep(2)"
                            class="btn-primary-custom w-full bg-brand-magenta text-white py-3 px-6 rounded-lg font-semibold text-lg shadow-lg hover:bg-opacity-90 transition-all transform hover:scale-[1.02]">
                            {{ __('auth.BTN_NEXT', [], $currentLang) }} →
                        </button>
                    </div>

                    {{-- Step 2: Company Information --}}
                    <div id="step-2" class="step-content space-y-4 hidden">
                        <div>
                            <label for="company-name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('auth.AUTH_COMPANY_NAME', [], $currentLang) }}
                            </label>
                            <input type="text"
                                id="company-name"
                                name="company-name"
                                required
                                class="form-input-enhanced"
                                value="{{ old('company-name') }}"
                                placeholder="{{ __('auth.AUTH_COMPANY_NAME', [], $currentLang) }}">
                        </div>

                        <div>
                            <label for="cr-number" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('auth.AUTH_CR_NUMBER', [], $currentLang) }}
                            </label>
                            <input type="text"
                                id="cr-number"
                                name="cr-number"
                                required
                                class="form-input-enhanced"
                                placeholder="{{ __('auth.AUTH_CR_PLACEHOLDER', [], $currentLang) }}"
                                value="{{ old('cr-number') }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.AUTH_INDUSTRY', [], $currentLang) }}
                                </label>
                                <select id="industry"
                                    name="industry"
                                    class="form-input-enhanced"
                                    required>
                                    <option value="">{{ __('auth.AUTH_INDUSTRY', [], $currentLang) }}</option>
                                    <option value="تقنية المعلومات" @selected(old('industry')==='تقنية المعلومات' )>
                                        {{ __('auth.INDUSTRY_IT', [], $currentLang) }}
                                    </option>
                                    <option value="تصميم واجهات (UI)" @selected(old('industry')==='تصميم واجهات (UI)' )>
                                        {{ __('auth.INDUSTRY_UI', [], $currentLang) }}
                                    </option>
                                    <option value="تجربة العميل (CX)" @selected(old('industry')==='تجربة العميل (CX)' )>
                                        {{ __('auth.INDUSTRY_CX', [], $currentLang) }}
                                    </option>
                                    <option value="استشارات مالية" @selected(old('industry')==='استشارات مالية' )>
                                        {{ __('auth.INDUSTRY_FINANCE', [], $currentLang) }}
                                    </option>
                                    <option value="التسويق الرقمي" @selected(old('industry')==='التسويق الرقمي' )>
                                        {{ __('auth.INDUSTRY_MARKETING', [], $currentLang) }}
                                    </option>
                                    <option value="other" @selected(old('industry')==='other' )>
                                        {{ __('auth.INDUSTRY_OTHER', [], $currentLang) }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="company-size" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.AUTH_COMPANY_SIZE', [], $currentLang) }}
                                </label>
                                <select id="company-size"
                                    name="company-size"
                                    class="form-input-enhanced"
                                    required>
                                    <option value="">{{ __('auth.AUTH_COMPANY_SIZE', [], $currentLang) }}</option>
                                    <option value="1-10" @selected(old('company-size')==='1-10' )>
                                        {{ __('auth.SIZE_1_10', [], $currentLang) }}
                                    </option>
                                    <option value="11-50" @selected(old('company-size')==='11-50' )>
                                        {{ __('auth.SIZE_11_50', [], $currentLang) }}
                                    </option>
                                    <option value="51-200" @selected(old('company-size')==='51-200' )>
                                        {{ __('auth.SIZE_51_200', [], $currentLang) }}
                                    </option>
                                    <option value="201+" @selected(old('company-size')==='201+' )>
                                        {{ __('auth.SIZE_201_PLUS', [], $currentLang) }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button"
                                onclick="prevStep(1)"
                                class="btn-secondary-custom flex-1 bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-semibold transition-all">
                                ← {{ __('auth.BTN_PREVIOUS', [], $currentLang) }}
                            </button>
                            <button type="button"
                                onclick="nextStep(3)"
                                class="btn-primary-custom flex-1 bg-brand-magenta text-white py-3 px-6 rounded-lg font-semibold shadow-lg hover:bg-opacity-90 transition-all transform hover:scale-[1.02]">
                                {{ __('auth.BTN_NEXT', [], $currentLang) }} →
                            </button>
                        </div>
                    </div>

                    {{-- Step 3: Agreement --}}
                    <div id="step-3" class="step-content space-y-5 hidden">
                        <div>
                            <h3 class="text-xl font-bold text-dark-navy mb-2">
                                {{ __('auth.AUTH_LEGAL_AGREEMENT', [], $currentLang) }}
                            </h3>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                {{ __('auth.AUTH_LEGAL_AGREEMENT_DESC', [], $currentLang) }}
                            </p>
                        </div>

                        <div class="checkbox-container">
                            <input type="checkbox"
                                id="terms"
                                name="terms"
                                required>
                            <label for="terms" class="text-sm text-gray-700 cursor-pointer">
                                {{ __('auth.AUTH_I_AGREE', [], $currentLang) }}
                                <a href="{{ route('legal.terms') ?? '#' }}"
                                    target="_blank"
                                    class="text-brand-teal hover:underline font-semibold">
                                    {{ __('auth.AUTH_TERMS', [], $currentLang) }}
                                </a>
                                {{ __('auth.AND', [], $currentLang) }}
                                <a href="{{ route('legal.privacy') ?? '#' }}"
                                    target="_blank"
                                    class="text-brand-teal hover:underline font-semibold">
                                    {{ __('auth.AUTH_PRIVACY', [], $currentLang) }}
                                </a>
                            </label>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button"
                                onclick="prevStep(2)"
                                class="btn-secondary-custom flex-1 bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-semibold transition-all">
                                ← {{ __('auth.BTN_PREVIOUS', [], $currentLang) }}
                            </button>
                            <button type="submit"
                                class="btn-primary-custom flex-1 bg-gradient-to-r from-brand-teal to-teal-500 text-white py-3 px-6 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-[1.02]">
                                {{ __('auth.BTN_SIGN_UP', [], $currentLang) }}
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="grid grid-cols-2 gap-4 mt-10 mb-6">
                    <a href="{{ route('register.student') }}" class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:border-brand-teal/50 transition-all duration-300 group transform hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center mb-3 group-hover:bg-brand-teal/10 transition-colors">
                            <svg class="w-6 h-6 text-teal-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-brand-teal transition-colors">{{ __('auth.REGISTER_AS_STUDENT', [], $currentLang) }}</span>
                    </a>
                    
                    <a href="{{ route('freelancer.register.form') }}" class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:border-brand-magenta/50 transition-all duration-300 group transform hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-fuchsia-50 flex items-center justify-center mb-3 group-hover:bg-brand-magenta/10 transition-colors">
                            <svg class="w-6 h-6 text-brand-magenta group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-brand-magenta transition-colors">{{ __('auth.REGISTER_AS_FREELANCER', [], $currentLang) }}</span>
                    </a>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        {{ __('auth.AUTH_ALREADY_HAVE_ACCOUNT', [], $currentLang) }}
                        <a href="{{ route('login') }}" class="font-bold text-brand-teal hover:underline">
                            {{ __('auth.BTN_LOGIN', [], $currentLang) }}
                        </a>
                    </p>
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
        let currentStep = 1;

        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const type = urlParams.get('type') || 'supplier';
            const registrationTypeInput = document.getElementById('registration-type');
            if (registrationTypeInput) {
                registrationTypeInput.value = type;
            }
        });

        function showStep(step) {
            // Hide all steps
            document.getElementById('step-1').classList.add('hidden');
            document.getElementById('step-2').classList.add('hidden');
            document.getElementById('step-3').classList.add('hidden');

            // Show current step
            document.getElementById('step-' + step).classList.remove('hidden');

            // Update progress
            updateProgress(step);
        }

        function nextStep(step) {
            // Validate current step before proceeding
            if (currentStep === 1) {
                const fullName = document.getElementById('full-name').value.trim();
                const email = document.getElementById('work-email').value.trim();
                const password = document.getElementById('password').value;
                const passwordConfirm = document.getElementById('password-confirm').value;

                if (!fullName || !email || !password || !passwordConfirm) {
                    alert('{{ __("auth.ERROR_EMPTY_FIELDS", [], $currentLang) }}');
                    return;
                }

                if (password.length < 8) {
                    alert('{{ __("auth.ERROR_PASSWORD_MIN", [], $currentLang) }}');
                    return;
                }

                if (password !== passwordConfirm) {
                    alert('{{ __("auth.ERROR_PASSWORD_CONFIRMATION", [], $currentLang) }}');
                    return;
                }
            }

            if (currentStep === 2) {
                const companyName = document.getElementById('company-name').value.trim();
                const crNumber = document.getElementById('cr-number').value.trim();
                const industry = document.getElementById('industry').value;
                const companySize = document.getElementById('company-size').value;

                if (!companyName || !crNumber || !industry || !companySize) {
                    alert('{{ __("auth.ERROR_EMPTY_FIELDS", [], $currentLang) }}');
                    return;
                }
            }

            currentStep = step;
            showStep(currentStep);
        }

        function prevStep(step) {
            currentStep = step;
            showStep(currentStep);
        }

        function updateProgress(step) {
            const progressBar = document.getElementById('progress-bar');
            const stepTitle1 = document.getElementById('step-title-1');
            const stepTitle2 = document.getElementById('step-title-2');
            const stepTitle3 = document.getElementById('step-title-3');

            // Reset all steps
            [stepTitle1, stepTitle2, stepTitle3].forEach(el => {
                el.classList.remove('active');
                el.classList.add('inactive');
            });

            // Update progress bar and active step
            if (step === 1) {
                progressBar.style.width = '33.33%';
                stepTitle1.classList.remove('inactive');
                stepTitle1.classList.add('active');
            } else if (step === 2) {
                progressBar.style.width = '66.66%';
                stepTitle2.classList.remove('inactive');
                stepTitle2.classList.add('active');
            } else if (step === 3) {
                progressBar.style.width = '100%';
                stepTitle3.classList.remove('inactive');
                stepTitle3.classList.add('active');
            }
        }

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