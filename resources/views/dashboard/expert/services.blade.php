<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('expert_services.title') }} | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-green-200 shadow-lg">R</div>
                <div>
                    <h1 class="font-bold text-lg leading-none text-slate-800">Radiif</h1>
                    <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT DASHBOARD</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard.expert') }}" class="text-slate-600 hover:text-green-600 transition text-sm font-medium">
                    <i class="fa-solid fa-arrow-left ml-2"></i> {{ __('expert_services.back_to_dashboard') }}
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('expert_services.title') }}</h1>
                <p class="text-slate-600">{{ __('expert_services.subtitle') }}</p>
            </div>
            <button class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition">
                <i class="fa-solid fa-plus ml-2"></i> {{ __('expert_services.add_service') }}
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fa-solid fa-circle-check ml-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_services.total_services') }}</p>
                <h2 class="text-2xl font-bold text-slate-800">3</h2>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_services.active_services') }}</p>
                <h2 class="text-2xl font-bold text-green-600">2</h2>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_services.total_requests') }}</p>
                <h2 class="text-2xl font-bold text-slate-800">15</h2>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_services.avg_rating') }}</p>
                <h2 class="text-2xl font-bold text-orange-500">4.8 <i class="fa-solid fa-star text-sm"></i></h2>
            </div>
        </div>

        <!-- Services List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Service Card Example -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">{{ __('expert_services.active') }}</span>
                                <span class="text-slate-400 text-xs">استشارات</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800 mb-2">استشارات تحليل البيانات</h3>
                            <p class="text-slate-600 text-sm line-clamp-2">تقديم استشارات متخصصة في تحليل البيانات وبناء نماذج التعلم الآلي</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div>
                            <span class="text-2xl font-bold text-green-600">250</span>
                            <span class="text-slate-500 text-sm">{{ __('expert_services.currency') }}{{ __('expert_services.per_hour') }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-4 py-2 text-slate-600 hover:bg-slate-50 rounded-lg transition">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add New Service Card -->
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center p-12 hover:border-green-400 hover:bg-green-50 transition cursor-pointer">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fa-solid fa-plus text-2xl text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 mb-2">{{ __('expert_services.add_service') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('expert_services.no_services_desc') }}</p>
                </div>
            </div>

        </div>

        <!-- Empty State (if no services) -->
        <div class="hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-box-open text-3xl text-slate-400"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-700 mb-2">{{ __('expert_services.no_services') }}</h3>
            <p class="text-slate-500 mb-6">{{ __('expert_services.no_services_desc') }}</p>
            <button class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition">
                <i class="fa-solid fa-plus ml-2"></i> {{ __('expert_services.add_service') }}
            </button>
        </div>

    </div>

</body>
</html>
