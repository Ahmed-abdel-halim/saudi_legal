<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="dark">
<head>
    @include('partials.google-analytics')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('expert_dashboard.page_title') }}</title>
    
    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Tailwind Configuration matching Radiif Brand Identity --}}
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

    <style> 
        body, input, button, select, textarea { font-family: 'Tajawal', 'Cairo', sans-serif; }
        body {
            background-color: #0b1120;
            color: #f8fafc;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(79, 70, 229, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(13, 148, 136, 0.12) 0%, transparent 55%);
            background-attachment: fixed;
        }

        /* Ambient dot overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .glass-card {
            background: rgba(17, 24, 39, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .glass-nav {
            background: rgba(11, 17, 32, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* ══ Global Dark Theme Overrides for All Child Views ══ */
        .bg-white,
        .bg-slate-50,
        .bg-slate-50\/50,
        .bg-slate-50\/30,
        .bg-slate-100,
        .bg-gray-50,
        .bg-gray-100 {
            background-color: rgba(17, 24, 39, 0.85) !important;
            border-color: rgba(31, 45, 64, 0.8) !important;
            color: #f8fafc !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .text-slate-800,
        .text-slate-900,
        .text-slate-700,
        .text-slate-600,
        .text-gray-800,
        .text-gray-700,
        .text-gray-600 {
            color: #ffffff !important;
        }

        .text-slate-500,
        .text-slate-400,
        .text-gray-500,
        .text-gray-400 {
            color: #94a3b8 !important;
        }

        .border-slate-100,
        .border-slate-200,
        .border-slate-300,
        .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: rgba(31, 45, 64, 0.8) !important;
        }

        input:not([type="checkbox"]):not([type="radio"]),
        select,
        textarea {
            background-color: #0b1120 !important;
            border-color: #1f2d40 !important;
            color: #ffffff !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: #64748b !important;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1f2d40; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0d9488; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="text-slate-100 antialiased min-h-screen relative z-10">

    <nav class="glass-nav sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.expert') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-600 to-emerald-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-green-glow">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-none text-white">رديف</h1>
                        <span class="text-[10px] text-emerald-400 font-extrabold tracking-wider">لوحة الخبراء المحترفين</span>
                    </div>
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-sm font-bold text-white">{{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                    <span class="text-xs text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-circle text-[8px] text-emerald-400 animate-pulse"></i> خبير معتمد
                    </span>
                </div>

                <div class="h-10 w-10 rounded-full bg-dark-card overflow-hidden border-2 border-emerald-500/40 shadow-glow">
                    <img src="{{ Auth::user()->avatar_path ? asset('uploads/' . Auth::user()->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->full_name ?? Auth::user()->name).'&background=0d9488&color=fff' }}" 
                         class="w-full h-full object-cover" 
                         alt="Avatar"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name ?? Auth::user()->name) }}&background=0d9488&color=fff'">
                </div>

                <!-- Chat Icon -->
                <a href="{{ route('dashboard.expert.chat.index') }}" class="w-10 h-10 rounded-full bg-dark-card border border-dark-border flex items-center justify-center text-slate-300 hover:text-emerald-400 hover:border-emerald-500/50 transition relative shadow-sm">
                    <i class="fa-regular fa-comment-dots text-base"></i>
                    @if(\App\Models\Message::where('is_read', false)->where('sender_id', '!=', Auth::id())->whereIn('conversation_id', \App\Models\Conversation::where('participant_1', Auth::id())->orWhere('participant_2', Auth::id())->pluck('id'))->count() > 0)
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full shadow-green-glow"></span>
                    @endif
                </a>

                <!-- Language Toggle -->
                <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() == 'ar' ? 'en' : 'ar']) }}" 
                   class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-dark-card border border-dark-border text-slate-300 hover:text-white hover:border-emerald-500/50 transition font-bold text-xs shadow-sm">
                    <i class="fa-solid fa-globe text-emerald-400"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="w-10 h-10 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 transition flex items-center justify-center">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    @if(View::hasSection('full_page'))
        @yield('content')
    @else
        <div class="container mx-auto px-4 py-8">
            @yield('content')
        </div>
    @endif

</body>
</html>
