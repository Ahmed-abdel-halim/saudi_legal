{{-- resources/views/auth/superadmin-login.blade.php --}}
@php
    $currentLang   = app()->getLocale();
    $direction     = $currentLang === 'ar' ? 'rtl' : 'ltr';
    $isRtl         = $direction === 'rtl';

    // Language switcher
    $targetLang     = $currentLang === 'en' ? 'ar' : 'en';
    $targetLangText = $currentLang === 'en' ? 'العربية' : 'English';
    $switchLangUrl  = request()->url() . '?lang=' . $targetLang;
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('auth.SA_PAGE_TITLE', [], $currentLang) }}</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Cairo Font (same as site) --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Cairo', 'sans-serif'] },
                    colors: {
                        'dark-navy':       '#0F172A',
                        'brand-primary':   '#4F46E5',
                        'brand-secondary': '#8B5CF6',
                        'brand-teal':      '#0d9488',
                        'brand-dark':      '#1E293B',
                    },
                    animation: {
                        'fade-up':    'fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) both',
                        'fade-up-d':  'fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) 0.12s both',
                        'logo-pulse': 'logoPulse 4s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeUp: {
                            '0%':   { opacity: '0', transform: 'translateY(24px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        logoPulse: {
                            '0%,100%': { filter: 'drop-shadow(0 0 20px rgba(79,70,229,0.5))' },
                            '50%':     { filter: 'drop-shadow(0 0 38px rgba(139,92,246,0.75))' },
                        },
                    },
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Cairo', sans-serif; }

        /* Dark mesh background */
        .mesh-bg {
            background-color: #0F172A;
            background-image:
                radial-gradient(ellipse 80% 50% at 20% 10%,  rgba(79,70,229,.16)  0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 85%,  rgba(139,92,246,.12) 0%, transparent 60%),
                radial-gradient(ellipse 40% 30% at 60% 40%,  rgba(13,148,136,.06) 0%, transparent 55%);
        }
        /* Subtle grid lines */
        .grid-lines {
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        /* Glass card */
        .glass-card {
            background: rgba(30, 41, 59, 0.55);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            border: 1px solid rgba(79, 70, 229, 0.18);
            box-shadow:
                0 0 0 1px rgba(79,70,229,.07),
                0 32px 80px rgba(0,0,0,.55),
                inset 0 1px 0 rgba(255,255,255,.06);
        }
        /* Input */
        .sa-input {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(79, 70, 229, 0.2);
            color: #E2E8F0;
            transition: border-color .25s, box-shadow .25s;
        }
        .sa-input:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.2);
        }
        .sa-input::placeholder { color: rgba(148,163,184,.4); }
        /* Primary button */
        .btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #8B5CF6 100%);
            box-shadow: 0 4px 20px rgba(79,70,229,.4);
            transition: all .25s;
        }
        .btn-primary:hover  { box-shadow: 0 8px 30px rgba(79,70,229,.55); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: .65; transform: none; cursor: not-allowed; }
        /* Security badge */
        .badge {
            background: rgba(79,70,229,.1);
            border: 1px solid rgba(79,70,229,.25);
        }
        /* Lang switcher */
        .lang-btn {
            background: rgba(79,70,229,.12);
            border: 1px solid rgba(79,70,229,.25);
            transition: all .2s;
        }
        .lang-btn:hover {
            background: rgba(79,70,229,.22);
            border-color: rgba(79,70,229,.5);
        }
        /* Decorative blob */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }
    </style>
</head>

<body class="mesh-bg min-h-screen font-sans">

    {{-- Grid overlay --}}
    <div class="fixed inset-0 grid-lines pointer-events-none"></div>

    {{-- ── LAYOUT ──────────────────────────────────────────────────────── --}}
    <div class="relative z-10 min-h-screen flex {{ $isRtl ? 'flex-row-reverse' : 'flex-row' }}">

        {{-- ══════════ LEFT / BRANDING PANEL (hidden on mobile) ══════════ --}}
        <div class="hidden lg:flex w-[46%] flex-col items-center justify-center p-14 relative overflow-hidden">
            {{-- Blobs --}}
            <div class="blob w-[480px] h-[480px] bg-brand-primary opacity-[0.09] -top-24 -left-28"></div>
            <div class="blob w-80 h-80 bg-brand-secondary opacity-[0.08] bottom-4 -right-16"></div>

            <div class="relative z-10 text-center max-w-md animate-fade-up">
                {{-- Logo --}}
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/icon.png') }}"
                         onerror="this.src='https://placehold.co/96x96/4F46E5/FFFFFF?text=R'"
                         alt="Logo"
                         class="h-24 w-24 rounded-2xl object-cover animate-logo-pulse"
                         style="filter:drop-shadow(0 0 24px rgba(79,70,229,0.6));animation:logoPulse 4s ease-in-out infinite;">
                </div>

                {{-- Name --}}
                <h1 class="text-4xl font-extrabold text-white mb-2 tracking-tight">
                    {{ __('auth.PLATFORM_NAME', [], $currentLang) }}
                </h1>
                <p class="text-slate-400 text-lg mb-10">
                    {{ __('auth.SA_PLATFORM_SUBTITLE', [], $currentLang) }}
                </p>

                {{-- Feature list --}}
                <div class="space-y-3 text-{{ $isRtl ? 'right' : 'left' }}">
                    {{-- Role access --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl {{ $isRtl ? 'flex-row-reverse' : '' }}"
                         style="background:rgba(79,70,229,0.1);border:1px solid rgba(79,70,229,0.2);">
                        <div class="w-9 h-9 rounded-lg bg-brand-primary/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ __('auth.SA_FEATURE_ROLE', [], $currentLang) }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">{{ __('auth.SA_FEATURE_ROLE_DESC', [], $currentLang) }}</p>
                        </div>
                    </div>
                    {{-- Rate limit --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl {{ $isRtl ? 'flex-row-reverse' : '' }}"
                         style="background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.2);">
                        <div class="w-9 h-9 rounded-lg bg-brand-secondary/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ __('auth.SA_FEATURE_RATE', [], $currentLang) }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">{{ __('auth.SA_FEATURE_RATE_DESC', [], $currentLang) }}</p>
                        </div>
                    </div>
                    {{-- Audit --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl {{ $isRtl ? 'flex-row-reverse' : '' }}"
                         style="background:rgba(13,148,136,0.1);border:1px solid rgba(13,148,136,0.2);">
                        <div class="w-9 h-9 rounded-lg bg-brand-teal/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ __('auth.SA_FEATURE_AUDIT', [], $currentLang) }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">{{ __('auth.SA_FEATURE_AUDIT_DESC', [], $currentLang) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ RIGHT / FORM PANEL ══════════ --}}
        <div class="flex-1 flex flex-col">

            {{-- ── Top bar: logo (mobile) + lang switcher ── --}}
            <div class="flex items-center justify-between px-6 py-4" dir="ltr">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-2">
                    <img src="{{ asset('images/icon.png') }}"
                         onerror="this.src='https://placehold.co/36x36/4F46E5/FFFFFF?text=R'"
                         alt="Logo" class="h-9 w-9 rounded-xl object-cover">
                    <span class="text-white font-bold text-lg">{{ __('auth.PLATFORM_NAME', [], $currentLang) }}</span>
                </div>
                <div class="hidden lg:block"></div>{{-- spacer --}}

                {{-- Language switcher --}}
                <a href="{{ $switchLangUrl }}"
                   class="lang-btn inline-flex items-center gap-2 text-slate-300 hover:text-white text-sm font-semibold px-4 py-2 rounded-full">
                    {{-- Globe icon --}}
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    {{ $targetLangText }}
                </a>
            </div>

            {{-- ── Centered form ── --}}
            <div class="flex-1 flex items-center justify-center px-6 pb-10">
                <div class="w-full max-w-md animate-fade-up-d">

                    {{-- CARD --}}
                    <div class="glass-card rounded-2xl overflow-hidden">

                        {{-- Gradient top bar --}}
                        <div class="h-1 bg-gradient-to-r from-brand-primary via-brand-secondary to-brand-teal"></div>

                        <div class="p-8 md:p-10">

                            {{-- Header --}}
                            <div class="mb-7">
                                <div class="inline-flex items-center gap-2 badge rounded-full px-3 py-1 mb-4">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-pulse"></span>
                                    <span class="text-xs font-semibold text-slate-300 tracking-widest uppercase">
                                        {{ __('auth.SA_RESTRICTED_BADGE', [], $currentLang) }}
                                    </span>
                                </div>
                                <h2 class="text-2xl font-bold text-white">
                                    {{ __('auth.SA_WELCOME', [], $currentLang) }}
                                </h2>
                                <p class="text-slate-400 mt-1 text-sm">
                                    {{ __('auth.SA_SUBTITLE', [], $currentLang) }}
                                </p>
                            </div>

                            {{-- ── ERROR ALERTS ── --}}
                            @if($errors->any())
                            <div class="mb-6 flex items-start gap-3 p-4 rounded-xl {{ $isRtl ? 'flex-row-reverse text-right' : '' }}"
                                 style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.22);">
                                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="space-y-0.5">
                                    @foreach($errors->all() as $err)
                                    <p class="text-red-300 text-sm leading-relaxed">{{ $err }}</p>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if(session('error'))
                            <div class="mb-6 flex items-start gap-3 p-4 rounded-xl {{ $isRtl ? 'flex-row-reverse text-right' : '' }}"
                                 style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.22);">
                                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-red-300 text-sm leading-relaxed">{{ session('error') }}</p>
                            </div>
                            @endif

                            {{-- ── FORM ── --}}
                            <form id="sa-form" action="{{ route('superadmin.login.handle') }}" method="POST" class="space-y-5">
                                @csrf

                                {{-- Email --}}
                                <div>
                                    <label for="email"
                                           class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                                        {{ __('auth.SA_EMAIL_LABEL', [], $currentLang) }}
                                    </label>
                                    <div class="relative">
                                        <input type="email" id="email" name="email" required
                                               autocomplete="username"
                                               value="{{ old('email') }}"
                                               dir="ltr"
                                               class="sa-input w-full rounded-xl px-4 py-3 {{ $isRtl ? 'pr-11 text-right' : 'pl-11' }} text-sm"
                                               placeholder="{{ __('auth.SA_EMAIL_PLACEHOLDER', [], $currentLang) }}">
                                        <span class="pointer-events-none absolute {{ $isRtl ? 'right-3.5' : 'left-3.5' }} top-1/2 -translate-y-1/2">
                                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                {{-- Password --}}
                                <div>
                                    <label for="password"
                                           class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                                        {{ __('auth.SA_PASSWORD_LABEL', [], $currentLang) }}
                                    </label>
                                    <div class="relative">
                                        <input type="password" id="password" name="password" required
                                               autocomplete="current-password"
                                               dir="ltr"
                                               class="sa-input w-full rounded-xl px-4 py-3 {{ $isRtl ? 'pr-11 pl-11' : 'pl-11 pr-11' }} text-sm"
                                               placeholder="{{ __('auth.SA_PASSWORD_PLACEHOLDER', [], $currentLang) }}">
                                        {{-- Lock icon --}}
                                        <span class="pointer-events-none absolute {{ $isRtl ? 'right-3.5' : 'left-3.5' }} top-1/2 -translate-y-1/2">
                                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </span>
                                        {{-- Eye toggle --}}
                                        <button type="button" onclick="togglePwd()"
                                                aria-label="{{ __('auth.SA_TOGGLE_PASSWORD', [], $currentLang) }}"
                                                class="absolute {{ $isRtl ? 'left-3.5' : 'right-3.5' }} top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors focus:outline-none">
                                            <svg id="eye-show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg id="eye-hide" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Audit notice --}}
                                <div class="flex items-start gap-3 p-3.5 rounded-xl {{ $isRtl ? 'flex-row-reverse text-right' : '' }}"
                                     style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.18);">
                                    <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-amber-300/80 text-xs leading-relaxed">
                                        {!! __('auth.SA_AUDIT_NOTICE', [], $currentLang) !!}
                                    </p>
                                </div>

                                {{-- Submit --}}
                                <button type="submit" id="sa-btn"
                                        class="btn-primary w-full rounded-xl py-3.5 px-6 font-bold text-white text-sm tracking-wide flex items-center justify-center gap-2 mt-1">
                                    <svg id="btn-icon" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span id="btn-text">{{ __('auth.SA_BTN_SIGNIN', [], $currentLang) }}</span>
                                </button>
                            </form>

                            {{-- Footer row --}}
                            <div class="mt-8 pt-6 border-t border-white/5 flex items-center justify-between {{ $isRtl ? 'flex-row-reverse' : '' }}">
                                <a href="{{ route('home') }}"
                                   class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-brand-primary transition-colors group {{ $isRtl ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-3.5 h-3.5 transition-transform {{ $isRtl ? 'rotate-180 group-hover:translate-x-0.5' : 'group-hover:-translate-x-0.5' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7M3 12h18"/>
                                    </svg>
                                    {{ __('auth.SA_BACK_TO_SITE', [], $currentLang) }}
                                </a>
                                <span class="text-xs text-slate-600 font-mono select-none">
                                    {{ __('auth.SA_FOOTER_VERSION', [], $currentLang) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- IP / timestamp --}}
                    <p class="text-center text-slate-700 text-xs mt-4 font-mono select-none" dir="ltr">
                        {{ request()->ip() }} · {{ now()->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ── Password toggle ──
        function togglePwd() {
            const i    = document.getElementById('password');
            const show = document.getElementById('eye-show');
            const hide = document.getElementById('eye-hide');
            if (i.type === 'password') { i.type = 'text';     show.classList.add('hidden');    hide.classList.remove('hidden'); }
            else                       { i.type = 'password'; show.classList.remove('hidden'); hide.classList.add('hidden'); }
        }

        // ── Loading state on submit ──
        const saBtn   = document.getElementById('sa-btn');
        const btnText = document.getElementById('btn-text');
        const btnIcon = document.getElementById('btn-icon');
        const signingIn = @json(__('auth.SA_BTN_SIGNING_IN', [], $currentLang));

        document.getElementById('sa-form').addEventListener('submit', function () {
            saBtn.disabled = true;
            btnText.textContent = signingIn;
            btnIcon.innerHTML = `
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" class="opacity-25" fill="none"/>
                <path fill="currentColor" class="opacity-75"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>`;
            btnIcon.classList.add('animate-spin');
        });
    </script>
</body>
</html>
