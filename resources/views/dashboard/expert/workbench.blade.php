<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('expert_workbench.title') }} | Radiif</title>
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
                    <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT WORKBENCH</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-sm">
                    <span class="text-slate-500">{{ __('expert_workbench.current_session') }}:</span>
                    <span class="font-bold text-green-600">5 {{ __('expert_workbench.tasks_in_session') }}</span>
                </div>
                <a href="{{ route('dashboard.expert') }}" class="text-slate-600 hover:text-green-600 transition text-sm font-medium">
                    <i class="fa-solid fa-arrow-left ml-2"></i> {{ __('expert_workbench.back_to_dashboard') }}
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <!-- Statistics Bar -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-xs mb-1">{{ __('expert_workbench.tasks_completed_today') }}</p>
                <h3 class="text-2xl font-bold text-green-600">12</h3>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-xs mb-1">{{ __('expert_workbench.earnings_today') }}</p>
                <h3 class="text-2xl font-bold text-orange-500">60 ر.س</h3>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-xs mb-1">{{ __('expert_workbench.accuracy_rate') }}</p>
                <h3 class="text-2xl font-bold text-blue-600">98%</h3>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-xs mb-1">{{ __('expert_workbench.pending_tasks') }}</p>
                <h3 class="text-2xl font-bold text-slate-700">24</h3>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-xs mb-1">{{ __('expert_workbench.quality_score') }}</p>
                <h3 class="text-2xl font-bold text-purple-600">{{ __('expert_workbench.excellent') }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Task Area -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">{{ __('expert_workbench.task_queue') }}</h2>
                            <p class="text-slate-500 text-sm">24 {{ __('expert_workbench.tasks_available') }}</p>
                        </div>
                        <button class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition">
                            <i class="fa-solid fa-play ml-2"></i> {{ __('expert_workbench.start_task') }}
                        </button>
                    </div>

                    <!-- Task Card -->
                    <div class="border-2 border-green-200 bg-green-50 rounded-2xl p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">{{ __('expert_workbench.task_types.text_correction') }}</span>
                                <p class="text-sm text-slate-500 mt-2">{{ __('expert_workbench.task_id') }}: #AI-2847</p>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-slate-500">{{ __('expert_workbench.earnings_today') }}</p>
                                <p class="text-xl font-bold text-green-600">+5 ر.س</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-white rounded-xl p-4">
                                <label class="text-sm font-bold text-slate-700 mb-2 block">{{ __('expert_workbench.original_data') }}</label>
                                <p class="text-slate-600">هذا نص يحتوي على بعض الاخطاء الاملائية والنحوية</p>
                            </div>

                            <div class="bg-white rounded-xl p-4">
                                <label class="text-sm font-bold text-slate-700 mb-2 block">{{ __('expert_workbench.ai_suggestion') }}</label>
                                <p class="text-slate-600">هذا نص يحتوي على بعض الأخطاء الإملائية والنحوية</p>
                            </div>

                            <div class="bg-white rounded-xl p-4">
                                <label class="text-sm font-bold text-slate-700 mb-2 block">{{ __('expert_workbench.your_correction') }}</label>
                                <textarea rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="{{ __('expert_workbench.correction_notes_placeholder') }}">هذا نص يحتوي على بعض الأخطاء الإملائية والنحوية</textarea>
                            </div>

                            <div class="bg-white rounded-xl p-4">
                                <label class="text-sm font-bold text-slate-700 mb-2 block">{{ __('expert_workbench.confidence_level') }}</label>
                                <select class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                                    @foreach(['low', 'medium', 'high', 'certain'] as $level)
                                        <option value="{{ $level }}">{{ __('expert_workbench.confidence_levels.' . $level) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-bold transition">
                                <i class="fa-solid fa-check ml-2"></i> {{ __('expert_workbench.approve') }}
                            </button>
                            <button class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-xl font-bold transition">
                                <i class="fa-solid fa-edit ml-2"></i> {{ __('expert_workbench.correct') }}
                            </button>
                            <button class="px-6 py-3 border border-slate-300 text-slate-600 rounded-xl font-medium hover:bg-slate-50 transition">
                                <i class="fa-solid fa-forward"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Session Progress -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h4 class="font-bold text-slate-700 mb-4">{{ __('expert_workbench.session_progress') }}</h4>
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-600">5 / 10 مهام</span>
                            <span class="text-green-600 font-bold">50%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full" style="width: 50%"></div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button class="w-full py-2 bg-orange-50 text-orange-700 rounded-lg font-medium hover:bg-orange-100 transition text-sm">
                            <i class="fa-solid fa-coffee ml-2"></i> {{ __('expert_workbench.take_break') }}
                        </button>
                        <button class="w-full py-2 bg-slate-50 text-slate-700 rounded-lg font-medium hover:bg-slate-100 transition text-sm">
                            <i class="fa-solid fa-stop ml-2"></i> {{ __('expert_workbench.end_session') }}
                        </button>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white">
                    <h4 class="font-bold text-lg mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-lightbulb"></i> {{ __('expert_workbench.guidelines') }}
                    </h4>
                    <ul class="space-y-2 text-sm text-blue-100">
                        @foreach(__('expert_workbench.guidelines_list') as $guideline)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check-circle mt-0.5"></i>
                                <span>{{ $guideline }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
