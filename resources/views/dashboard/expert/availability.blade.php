<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('expert_availability.title') }} | Radiif</title>
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
                    <i class="fa-solid fa-arrow-left ml-2"></i> {{ __('expert_availability.back_to_dashboard') }}
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('expert_availability.title') }}</h1>
            <p class="text-slate-600">{{ __('expert_availability.subtitle') }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fa-solid fa-circle-check ml-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Statistics Cards -->
            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_availability.availability_status') }}</p>
                            <h2 class="text-2xl font-bold text-green-600">{{ __('expert_availability.currently_available') }}</h2>
                        </div>
                        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_availability.total_hours_week') }}</p>
                            <h2 class="text-2xl font-bold text-slate-800">40 <span class="text-sm text-slate-400 font-normal">{{ __('expert_availability.hours') }}</span></h2>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_availability.available_days') }}</p>
                            <h2 class="text-2xl font-bold text-slate-800">5 <span class="text-sm text-slate-400 font-normal">{{ __('expert_availability.days_count') }}</span></h2>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Schedule -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">{{ __('expert_availability.weekly_schedule') }}</h3>
                    
                    <form method="POST" action="{{ route('dashboard.expert.availability') }}">
                        @csrf
                        
                        <div class="space-y-4">
                            @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            <div class="border border-slate-200 rounded-xl p-4 hover:border-green-300 transition">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="{{ $day }}" name="days[]" value="{{ $day }}" class="w-5 h-5 text-green-600 rounded" checked>
                                        <label for="{{ $day }}" class="font-bold text-slate-700 cursor-pointer">{{ __('expert_availability.days.' . $day) }}</label>
                                    </div>
                                    <button type="button" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                        <i class="fa-solid fa-plus ml-1"></i> {{ __('expert_availability.add_time_slot') }}
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-slate-500 mb-1 block">{{ __('expert_availability.from') }}</label>
                                        <input type="time" name="{{ $day }}_from[]" value="09:00" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-slate-500 mb-1 block">{{ __('expert_availability.to') }}</label>
                                        <input type="time" name="{{ $day }}_to[]" value="17:00" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-bold transition">
                                <i class="fa-solid fa-save ml-2"></i> {{ __('expert_availability.save_schedule') }}
                            </button>
                            <button type="button" class="px-6 py-3 border border-slate-300 text-slate-600 rounded-xl font-medium hover:bg-slate-50 transition">
                                {{ __('expert_availability.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h4 class="font-bold text-slate-700 mb-4">{{ __('expert_availability.quick_actions') }}</h4>
                    <div class="space-y-3">
                        <button class="w-full py-3 bg-green-50 text-green-700 rounded-lg font-medium hover:bg-green-100 transition">
                            <i class="fa-solid fa-check-circle ml-2"></i> {{ __('expert_availability.mark_available_today') }}
                        </button>
                        <button class="w-full py-3 bg-red-50 text-red-700 rounded-lg font-medium hover:bg-red-100 transition">
                            <i class="fa-solid fa-times-circle ml-2"></i> {{ __('expert_availability.mark_unavailable_today') }}
                        </button>
                        <button class="w-full py-3 bg-blue-50 text-blue-700 rounded-lg font-medium hover:bg-blue-100 transition">
                            <i class="fa-solid fa-copy ml-2"></i> {{ __('expert_availability.copy_to_all_days') }}
                        </button>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl shadow-lg p-6 text-white">
                    <div class="text-center">
                        <i class="fa-solid fa-lightbulb text-4xl mb-3 opacity-80"></i>
                        <h4 class="font-bold text-lg mb-2">نصيحة</h4>
                        <p class="text-sm text-green-100">حافظ على جدول منتظم لزيادة فرص الحصول على مهام جديدة</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
