<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('expert_dashboard.page_title') }}</title>
    
    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        /* Custom Scrollbar for Chat */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
    @stack('styles')
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.expert') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-green-200 shadow-lg">R</div>
                    <div>
                        <h1 class="font-bold text-lg leading-none text-slate-800">Radiif</h1>
                        <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT DASHBOARD</span>
                    </div>
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-sm font-bold text-slate-700">{{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                    <!-- Expert Level Badge (Simplified) -->
                    <span class="text-xs text-green-600 font-medium">Expert</span>
                </div>
                <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm">
                    <img src="{{ Auth::user()->avatar_path ? asset('uploads/' . Auth::user()->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->full_name ?? Auth::user()->name).'&background=random&color=fff' }}" 
                         class="w-full h-full object-cover" 
                         alt="Avatar"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name ?? Auth::user()->name) }}&background=random&color=fff'">
                </div>
                <!-- Chat Icon -->
                <a href="{{ route('dashboard.expert.chat.index') }}" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition relative">
                    <i class="fa-regular fa-comment-dots"></i>
                    @if(\App\Models\Message::where('is_read', false)->where('sender_id', '!=', Auth::id())->whereIn('conversation_id', \App\Models\Conversation::where('participant_1', Auth::id())->orWhere('participant_2', Auth::id())->pluck('id'))->count() > 0)
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    @endif
                </a>

                <!-- Language Toggle -->
                <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() == 'ar' ? 'en' : 'ar']) }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 transition font-bold text-sm">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
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
