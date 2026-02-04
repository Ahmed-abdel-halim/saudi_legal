{{-- resources/views/auth/register-student.blade.php - Student Registration Form --}}

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
    <title>{{ __('auth.NEW_STUDENT_ACCOUNT', [], $currentLang) }} - {{ __('auth.PLATFORM_NAME', [], $currentLang) }}</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Cairo Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                    {{ __('auth.STUDENT_REGISTRATION', [], $currentLang) }}
                </h1>

                <p class="text-xl text-gray-300 max-w-lg mx-auto leading-relaxed">
                    {{ __('auth.STUDENT_REGISTER_DESC', [], $currentLang) }}
                </p>

                {{-- Feature highlights --}}
                <div class="mt-12 grid grid-cols-2 gap-6 max-w-md mx-auto">
                    <div class="text-left p-4 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-teal/20 rounded-lg flex items-center justify-center mb-3">
                            <i class="fa-solid fa-graduation-cap text-brand-teal text-lg"></i>
                        </div>
                        <h3 class="text-white font-semibold text-sm">{{ __('auth.DEVELOP_SKILLS', [], $currentLang) }}</h3>
                    </div>
                    <div class="text-left p-4 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-magenta/20 rounded-lg flex items-center justify-center mb-3">
                            <i class="fa-solid fa-certificate text-brand-magenta text-lg"></i>
                        </div>
                        <h3 class="text-white font-semibold text-sm">{{ __('auth.CERTIFIED_CERTIFICATES', [], $currentLang) }}</h3>
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

                <h2 class="text-2xl md:text-3xl font-bold text-dark-navy mb-2">
                    {{ __('auth.NEW_STUDENT_ACCOUNT', [], $currentLang) }}
                </h2>
                <p class="text-gray-600 mb-6">
                    {{ __('auth.FILL_TO_CREATE_STUDENT', [], $currentLang) }}
                </p>

                {{-- Error Messages --}}
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form class="space-y-5" action="{{ route('register.student.handle') }}" method="POST">
                    @csrf
                    
                    {{-- Full Name --}}
                    <div>
                        <label for="full-name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_FULL_NAME', [], $currentLang) }}</label>
                        <input id="full-name" name="full-name" type="text" autocomplete="name" required 
                            class="form-input-enhanced" 
                            placeholder="{{ __('auth.FULL_NAME_PLACEHOLDER', [], $currentLang) }}" value="{{ old('full-name') }}">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.UNIVERSITY_EMAIL', [], $currentLang) }}</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="form-input-enhanced" 
                            placeholder="{{ __('auth.UNIVERSITY_EMAIL_PLACEHOLDER', [], $currentLang) }}" value="{{ old('email') }}">
                    </div>

                    {{-- National ID --}}
                    <div>
                        <label for="national-id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.NATIONAL_ID', [], $currentLang) }}</label>
                        <input id="national-id" name="national-id" type="text" required 
                            class="form-input-enhanced" 
                            placeholder="{{ __('auth.NATIONAL_ID_PLACEHOLDER', [], $currentLang) }}" value="{{ old('national-id') }}">
                    </div>

                    {{-- School/University --}}
                    <div>
                        <label for="school-name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.SCHOOL_NAME', [], $currentLang) }}</label>
                        <input id="school-name" name="school-name" type="text" required 
                            class="form-input-enhanced" 
                            placeholder="{{ __('auth.SCHOOL_NAME_PLACEHOLDER', [], $currentLang) }}" value="{{ old('school-name') }}">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_PASSWORD_LABEL', [], $currentLang) }}</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                            class="form-input-enhanced" 
                            placeholder="••••••••">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_PASSWORD_CONFIRM', [], $currentLang) }}</label>
                        <input id="password-confirm" name="password_confirmation" type="password" autocomplete="new-password" required 
                            class="form-input-enhanced" 
                            placeholder="••••••••">
                    </div>

                    {{-- Terms --}}
                    <div class="checkbox-container">
                        <input id="terms" name="terms" type="checkbox" required>
                        <label for="terms" class="text-sm text-gray-700">
                            {{ __('auth.AUTH_I_AGREE', [], $currentLang) }} <a href="{{ route('legal.terms') }}" class="text-brand-teal hover:underline font-bold">{{ __('auth.AUTH_TERMS', [], $currentLang) }}</a> {{ __('auth.AND', [], $currentLang) }} <a href="{{ route('legal.privacy') }}" class="text-brand-teal hover:underline font-bold">{{ __('auth.AUTH_PRIVACY', [], $currentLang) }}</a>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary-custom w-full flex justify-center py-3 px-4 border border-transparent text-lg font-bold rounded-xl text-white bg-brand-magenta hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-magenta transition-colors shadow-lg shadow-purple-200">
                            {{ __('auth.BTN_SIGN_UP', [], $currentLang) }}
                        </button>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="flex items-center my-6">
                    <div class="flex-grow h-[2px] bg-gradient-to-r from-transparent via-gray-300 to-gray-300"></div>
                     <span class="flex-shrink mx-4 text-gray-500 text-sm font-medium">{{ __('auth.OTHER_OPTIONS', [], $currentLang) }}</span>
                    <div class="flex-grow h-[2px] bg-gradient-to-l from-transparent via-gray-300 to-gray-300"></div>
                </div>

                {{-- Other Links --}}
                <div class="space-y-3">
                    <a href="{{ route('register.company') }}" class="block w-full text-center bg-gray-50 text-gray-700 py-3 px-6 rounded-lg font-bold hover:bg-gray-100 transition border border-gray-200">
                        {{ __('auth.REGISTER_AS_COMPANY', [], $currentLang) }}
                    </a>
                    
                    <a href="{{ route('login') }}" class="block w-full text-center bg-gradient-to-r from-brand-teal to-teal-600 text-white py-3 px-6 rounded-lg font-bold hover:shadow-lg transition transform hover:scale-[1.02]">
                        {{ __('auth.BTN_LOGIN', [], $currentLang) }}
                    </a>
                </div>

                <div class="text-center text-sm text-slate-500 mt-4">
                 {{ __('auth.NOT_STUDENT', [], $currentLang) }}
            </div>            
                <p class="text-center text-sm text-gray-500 mt-6">
                    <a href="{{ route('home') }}" class="text-brand-teal hover:underline font-medium">
                        ← {{ __('auth.BACK_TO_HOME', [], $currentLang) }}
                    </a>
                </p>

            </div>
        </div>
    </div>

</body>
</html>
