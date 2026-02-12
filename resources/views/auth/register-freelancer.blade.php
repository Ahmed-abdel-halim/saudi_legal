{{-- resources/views/auth/register-freelancer.blade.php - Freelancer Registration Form --}}

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
    <title>تسجيل حساب مستقل - {{ __('auth.PLATFORM_NAME', [], $currentLang) }}</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Cairo Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> --}}

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
                        'brand-primary': '#4F46E5', // Added for consistency with previous form if needed, or stick to teal/magenta
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

        /* Button hover effect */
        .btn-primary-custom:hover {
            box-shadow: 0 8px 25px rgba(27, 122, 126, 0.4);
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
                    انضم كخبير مستقل
                </h1>

                <p class="text-xl text-gray-300 max-w-lg mx-auto leading-relaxed">
                    شارك خبراتك، اعمل على مشاريع مميزة، وحقق دخلاً إضافياً في بيئة عمل مرنة واحترافية.
                </p>

                {{-- Feature highlights --}}
                <div class="mt-12 grid grid-cols-2 gap-6 max-w-md mx-auto">
                    <div class="text-left p-4 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-teal/20 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-sm">مشاريع متنوعة</h3>
                    </div>
                    <div class="text-left p-4 bg-white/5 rounded-xl backdrop-blur-sm">
                        <div class="w-10 h-10 bg-brand-magenta/20 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-brand-magenta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-sm">دخل مضمون</h3>
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
                    إنشاء حساب مستقل جديد
                </h2>
                <p class="text-gray-600 mb-6">
                    أكمل البيانات التالية للبدء
                </p>

                {{-- Error Messages --}}
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6">
                    @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form class="space-y-5" action="{{ route('freelancer.register') }}" method="POST">
                    @csrf
                    
                    {{-- Full Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_FULL_NAME', [], $currentLang) }}</label>
                        <input id="name" name="name" type="text" autocomplete="name" required 
                            class="form-input-enhanced" 
                            placeholder="{{ __('auth.FULL_NAME_PLACEHOLDER', [], $currentLang) }}" value="{{ old('name') }}">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_EMAIL_LABEL', [], $currentLang) }}</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="form-input-enhanced" 
                            placeholder="example@gmail.com" value="{{ old('email') }}">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_PASSWORD_LABEL', [], $currentLang) }}</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" autocomplete="new-password" required 
                                class="form-input-enhanced {{ $direction === 'rtl' ? 'pl-10' : 'pr-10' }}" 
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

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.AUTH_PASSWORD_CONFIRM', [], $currentLang) }}</label>
                        <div class="relative">
                            <input id="password-confirm" name="password_confirmation" type="password" autocomplete="new-password" required 
                                class="form-input-enhanced {{ $direction === 'rtl' ? 'pl-10' : 'pr-10' }}" 
                                placeholder="••••••••">
                            <button type="button"
                                class="absolute {{ $direction === 'rtl' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-brand-teal transition-colors"
                                onclick="togglePassword('password-confirm', this)"
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

                    <div>
                        <button type="submit" class="btn-primary-custom w-full flex justify-center py-3 px-4 border border-transparent text-lg font-bold rounded-xl text-white bg-brand-teal hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-teal transition-colors shadow-lg shadow-teal-200">
                            تسجيل حساب
                        </button>
                    </div>
                </form>

                {{-- Other Options Grid --}}
                <div class="grid grid-cols-2 gap-4 mb-6 mt-10 mb-6">
                    <a href="{{ route('register.company') }}" class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:border-brand-teal/50 transition-all duration-300 group transform hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center mb-3 group-hover:bg-brand-teal/10 transition-colors">
                            <svg class="w-6 h-6 text-teal-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-brand-teal transition-colors">{{ __('auth.REGISTER_AS_COMPANY', [], $currentLang) }}</span>
                    </a>
                    
                    <a href="{{ route('register.student') }}" class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:border-brand-primary/50 transition-all duration-300 group transform hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center mb-3 group-hover:bg-brand-primary/10 transition-colors">
                            <svg class="w-6 h-6 text-brand-primary group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-brand-primary transition-colors">{{ __('auth.REGISTER_AS_STUDENT', [], $currentLang) }}</span>
                    </a>
                </div>

                {{-- Login Link (Text style like Company page) --}}
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        {{ __('auth.AUTH_ALREADY_HAVE_ACCOUNT', [], $currentLang) }}
                        <a href="{{ route('login') }}" class="font-bold text-brand-teal hover:underline">
                            {{ __('auth.BTN_LOGIN', [], $currentLang) }}
                        </a>
                    </p>
                </div>
                
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
