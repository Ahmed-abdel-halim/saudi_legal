<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    @include('partials.google-analytics')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saudi Legal Workbench | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f9fafb;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 12px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 10px;
        }

        .scrollbar-blue::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 10px;
            border: 3px solid #f8fafc;
        }

        .scrollbar-blue::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }

        .scrollbar-emerald::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 10px;
            border: 3px solid #f8fafc;
        }

        .scrollbar-emerald::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="bg-gray-50 overflow-y-auto custom-scrollbar flex flex-col min-h-screen" x-data="{ mobileMenu: false }">

    @php 
        // Temporary Migration Fix
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('legal_qa_pairs', 'time_spent')) {
                \Illuminate\Support\Facades\Schema::table('legal_qa_pairs', function ($table) {
                    $table->integer('time_spent')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('ai_responses_v2', 'time_spent')) {
                \Illuminate\Support\Facades\Schema::table('ai_responses_v2', function ($table) {
                    $table->integer('time_spent')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('linguistic_tasks', 'time_spent')) {
                \Illuminate\Support\Facades\Schema::table('linguistic_tasks', function ($table) {
                    $table->integer('time_spent')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('legal_tasks', 'time_spent')) {
                \Illuminate\Support\Facades\Schema::table('legal_tasks', function ($table) {
                    $table->integer('time_spent')->nullable();
                });
            }
        } catch (\Exception $e) {}

        $price_per_task = 0.25; 
        $earnings_today = $stats['earnings_today'] ?? 0;
    @endphp

    <!-- Stats Header -->
    <header class="flex items-center justify-between px-4 md:px-6 py-3 bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <!-- Desktop Logout -->
        <a href="{{ route('dashboard.expert') }}" class="hidden md:flex text-gray-400 hover:text-emerald-600 transition text-xl p-2" title="خروج">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>

        <!-- Mobile Hamburger Menu (3 Bars) -->
        <button @click="mobileMenu = true" class="flex md:hidden text-gray-600 hover:text-emerald-600 transition text-2xl p-2">
            <i class="fa-solid fa-bars"></i>
        </button>
        
        <!-- Mobile Menu Overlay -->
        <div x-show="mobileMenu" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full"
             class="fixed inset-0 z-[100] md:hidden">
            
            <!-- Backdrop -->
            <div @click="mobileMenu = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            
            <!-- Menu Content -->
            <div class="absolute right-0 top-0 bottom-0 w-80 bg-white shadow-2xl flex flex-col">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-emerald-700 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold border border-white/30">
                            {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold">{{ Auth::user()->name }}</span>
                            <span class="text-[10px] opacity-80 text-white font-bold uppercase tracking-wider">خبير معتمد</span>
                        </div>
                    </div>
                    <button @click="mobileMenu = false" class="text-2xl opacity-70 hover:opacity-100 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-2 mb-4">قائمة التنقل</p>
                    
                    <a href="{{ route('dashboard.expert.legal_history') }}" class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-gray-700 font-bold hover:bg-emerald-50 hover:border-emerald-200 transition group">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-600 group-hover:scale-110 transition"></i>
                        <span>سجل المراجعات القانونية</span>
                    </a>

                    <div class="grid grid-cols-2 gap-2 pt-4">
                        <div class="p-4 bg-blue-50 border border-blue-100 rounded-2xl">
                            <span class="block text-[10px] font-black text-blue-400 uppercase mb-1">إنجاز اليوم</span>
                            <span class="text-xl font-black text-blue-700">{{ $stats['completed_today'] }}</span>
                        </div>
                        <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl">
                            <span class="block text-[10px] font-black text-amber-400 uppercase mb-1">الرصيد اليومي</span>
                            <span class="text-xl font-black text-amber-700">{{ number_format($earnings_today, 2) }} <small class="text-[10px]">ر.س</small></span>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50">
                    <a href="{{ route('dashboard.expert') }}" class="flex items-center justify-center gap-3 w-full p-4 bg-rose-50 text-rose-600 border border-rose-100 rounded-2xl font-black hover:bg-rose-600 hover:text-white transition shadow-sm">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>تسجيل الخروج من المنصة</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 md:gap-4">
            <!-- User Info (Hidden on Mobile) -->
            <div class="hidden md:flex items-center gap-3 pl-4 border-l border-gray-200">
                <div class="flex flex-col items-end">
                    <span class="text-sm font-bold text-gray-800">{{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                    <span class="text-[11px] text-emerald-600 font-bold">خبير معتمد</span>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold border-2 border-white shadow-sm overflow-hidden">
                    @if(Auth::user()->avatar_path)
                        <img src="{{ asset('uploads/' . Auth::user()->avatar_path) }}" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
                    @endif
                </div>
            </div>

            <!-- Completed Tasks (Linked to History) -->
            @php $total_tasks = \App\Models\LegalQaPair::where('reviewer_id', Auth::id())->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])->count(); @endphp
            <a href="{{ route('dashboard.expert.legal_history') }}" class="hidden md:flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-[12px] font-bold text-gray-700 hover:bg-white hover:border-emerald-500 transition shadow-sm group">
                <i class="fa-solid fa-circle-check text-emerald-500 group-hover:scale-110 transition"></i>
                <span class="opacity-70">المنجز كلياً:</span>
                <span>{{ $total_tasks }}</span>
            </a>

            <!-- Today's Tasks -->
            <div class="hidden md:flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-[12px] font-bold text-gray-700">
                <i class="fa-solid fa-calendar-day text-blue-500"></i>
                <span class="opacity-70">إنجاز اليوم:</span>
                <span>{{ $stats['completed_today'] }}</span>
            </div>

            <!-- Remaining Tasks -->
            <div class="flex items-center gap-1.5 md:gap-2 bg-rose-50 border border-rose-100 px-2 py-1 md:px-3 md:py-1.5 rounded-lg md:rounded-xl text-[10px] md:text-[12px] font-bold text-rose-700">
                <i class="fa-solid fa-hourglass-half text-rose-500 animate-pulse"></i>
                <span class="opacity-80">المتبقي:</span>
                <span class="text-xs md:text-sm">{{ $stats['pending_tasks'] }}</span>
            </div>

            <!-- Timer -->
            <div class="hidden lg:flex items-center gap-1.5 text-slate-400 font-bold text-[12px] mx-1">
                <span id="timer">00:00</span>
                <i class="fa-regular fa-clock"></i>
            </div>

            <!-- Earnings -->
            <div class="flex items-center gap-1.5 md:gap-2 bg-amber-50 border border-amber-200 px-2 py-1 md:px-3 md:py-1.5 rounded-lg md:rounded-xl text-[10px] md:text-[12px] font-bold text-amber-700">
                <i class="fa-solid fa-coins text-amber-600"></i>
                <span class="opacity-80">الرصيد:</span>
                <span class="text-xs md:text-sm">{{ number_format($earnings_today, 2) }}</span>
            </div>
        </div>
    </header>

    @if($task)
        <!-- Main Content Area -->
        <main class="flex-1 w-full max-w-4xl mx-auto py-10 px-4">

            <!-- The Main Card -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 relative">

                <div class="p-8 md:p-12 space-y-8">

                    <!-- Card Header -->
                    <div
                        class="flex items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md">TASK #{{ $task->id }}</span>
                        <span>تحقق من صحة العبارة</span>
                    </div>

                    <div class="text-center">
                        <h2 class="text-2xl md:text-3xl font-bold text-slate-600 leading-tight">
                            {{ $task->question }}
                        </h2>
                    </div>

                    <!-- Proposed Answer Section -->
                    <div class="space-y-4 pt-4">
                        <div class="flex justify-between items-center px-2">
                            <span class="text-xs text-gray-400 font-bold flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-gray-300"></i> {{ $task->law_system_name }}: انقر على أي كلمة لتحديدها كخطأ
                            </span>
                            <span class="text-sm font-black text-emerald-600 flex items-center gap-2">
                                الإجابة المقترحة <i class="fa-regular fa-circle-check"></i>
                            </span>
                        </div>

                        <div class="bg-gray-50 p-6 md:p-8 rounded-2xl border border-gray-100">
                            <p class="text-gray-700 text-lg leading-relaxed text-center font-medium" id="ai-answer-text">
                                {{ trim(preg_replace('/\(المصدر:[^\)]+نسبة تأكيد الربط:[^\)]+\)/u', '', $task->proposed_answer)) }}
                            </p>
                        </div>

                        {{-- الإجابة الغلط المقصودة — للـ admin فقط، مش للمحامي --}}
                    </div>

                    <!-- Interactive Source Accordions (FAQ Style) -->
                    <div class="space-y-4" dir="rtl">
                        <!-- Legal Articles (Primary - Shown First) -->
                        @if($mentioned_articles && $mentioned_articles->count() > 0)
                            @foreach($mentioned_articles as $article)
                                <div class="border border-gray-100 rounded-[1.5rem] bg-white overflow-hidden shadow-sm transition-all duration-300 hover:border-blue-200">
                                    <div onclick="toggleAccordion('article-{{ $article->id }}', this)" 
                                        class="w-full relative px-6 py-5 text-right cursor-pointer bg-white select-none">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                                                <i class="fa-solid fa-scale-balanced text-sm"></i>
                                            </div>
                                            <span class="font-black text-blue-700 text-sm">
                                                المصدر القانوني : {{ $article->legislation_title }} {{ $article->article_title }}
                                            </span>
                                        </div>
                                        <i class="fa-solid fa-chevron-down absolute left-6 top-1/2 -translate-y-1/2 text-blue-600 text-lg font-bold transition-transform duration-300"></i>
                                        @if(str_starts_with($article->id, 'temp-'))
                                            <button onclick="deleteCitation('{{ $article->id }}', event)" 
                                                class="absolute left-16 top-1/2 -translate-y-1/2 z-20 flex items-center justify-center w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition" 
                                                title="حذف هذا المرجع">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <div id="article-{{ $article->id }}" class="hidden px-6 pb-6 bg-blue-50/30 border-t border-blue-50">
                                        <div dir="rtl" style="text-align: right !important;" class="text-gray-800 text-lg leading-loose font-bold w-full pt-6 whitespace-pre-wrap">{{ trim(strip_tags($article->content)) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Judgment Accordion (Secondary - Shown Second) -->
                        <div class="border border-gray-100 rounded-[1.5rem] bg-white overflow-hidden shadow-sm transition-all duration-300 hover:border-emerald-200">
                            <button onclick="toggleAccordion('judgment-accordion', this)" 
                                class="w-full relative px-6 py-5 text-right focus:outline-none bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                                        <i class="fa-solid fa-gavel text-sm"></i>
                                    </div>
                                    <span class="font-black text-emerald-700 text-sm">
                                        مصدر الحكم : {{ $task->case_reference ?? 'حكم قضائي مرتبط' }}
                                    </span>
                                </div>
                                <i class="fa-solid fa-chevron-down absolute left-6 top-1/2 -translate-y-1/2 text-emerald-600 text-lg font-bold transition-transform duration-300"></i>
                            </button>
                            <div id="judgment-accordion" class="hidden px-6 pb-6 bg-emerald-50/30 border-t border-emerald-50">
                                <div dir="rtl" style="text-align: right !important;" class="text-gray-800 text-lg leading-loose font-bold w-full pt-6 whitespace-pre-wrap max-h-[500px] overflow-y-auto custom-scrollbar scrollbar-emerald px-4">{{ trim($task->case_text) ?: 'لا يتوفر نص سابقة قضائية لهذه المهمة. يمكنك إضافة مرجع جديد عبر التصحيح.' }}</div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Tags / Labels (Dynamic) -->
                    <div class="border border-gray-100 rounded-2xl p-6 bg-white shadow-sm">
                        <div class="flex items-center justify-between gap-2 mb-6">
                            <div class="flex items-center gap-3">
                                <button onclick="addNewTag()" class="px-2 py-1.5 md:px-4 md:py-2 border-2 border-rose-500 text-rose-500 rounded-xl font-black text-[10px] md:text-sm hover:bg-rose-50 transition">
                                    اضف تاق جديد <i class="fa-solid fa-plus mr-1"></i>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-black text-gray-400 uppercase tracking-widest">
                                الوسوم المقترحة للربط <i class="fa-solid fa-tags text-blue-500"></i>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-3" id="tags-container">
                            @php
                                // 1. استخراج الأوسمة المحفوظة مسبقاً (من قاعدة البيانات)
                                $savedTags = [];
                                if (isset($task->tags) && is_array($task->tags)) {
                                    $savedTags = $task->tags;
                                } elseif (!empty($task->expert_comment) && str_contains($task->expert_comment, '[Tags:')) {
                                    // استخراج من النظام الاحتياطي في الملاحظات
                                    preg_match('/\[Tags: (.*?)\]/', $task->expert_comment, $matches);
                                    if (isset($matches[1])) {
                                        $savedTags = array_map('trim', explode(',', $matches[1]));
                                    }
                                }

                                // 2. توليد الأوسمة المقترحة ذكياً
                                $suggestedKeywords = [$task->law_system_name];
                                $words = explode(' ', $task->question);
                                foreach (array_slice($words, 0, 10) as $word) {
                                    $cleanWord = trim(str_replace(['؟', '،', '.', 'هل', 'كيف', 'ما'], '', $word));
                                    if (mb_strlen($cleanWord) > 3) {
                                        $suggestedKeywords[] = $cleanWord;
                                    }
                                }
                                
                                // دمج المجموعتين وحذف التكرار
                                $allTags = array_unique(array_merge($savedTags, $suggestedKeywords));
                                // إخفاء الـ tags الداخلية التي تكشف طبيعة السؤال الاختباري
                                $hiddenTags = ['gold_standard', 'test_question', 'gold standard', 'test question'];
                                $allTags = array_filter($allTags, fn($t) => !in_array(strtolower(trim($t)), $hiddenTags));
                                $allTags = array_slice(array_filter($allTags), 0, 10); // تحديد العدد الأقصى
                            @endphp
                            
                            @foreach($allTags as $keyword)
                                @php 
                                    // إذا لم تكن هناك أوسمة محفوظة بعد (مهمة جديدة)، نجعل المقترحات مختارة تلقائياً
                                    // أما إذا كانت هناك أوسمة محفوظة، نختار المحفوظ منها فقط
                                    $shouldBeChecked = empty($savedTags) ? true : in_array($keyword, $savedTags); 
                                @endphp
                                <label
                                    class="flex items-center gap-1.5 px-2 py-1 md:px-4 md:py-2 {{ $shouldBeChecked ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200' }} border rounded-lg md:rounded-xl cursor-pointer hover:bg-gray-50 transition">
                                    <span class="text-[10px] md:text-sm font-bold {{ $shouldBeChecked ? 'text-blue-700' : 'text-gray-600' }}">{{ $keyword }}</span>
                                    <input type="checkbox" {{ $shouldBeChecked ? 'checked' : '' }} name="tags[]" value="{{ $keyword }}"
                                        class="w-3 h-3 md:w-4 md:h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons (3-Button System) -->
                    <div id="action-buttons" class="grid grid-cols-3 md:grid-cols-3 gap-2 md:gap-6 pt-4">
                        <button onclick="toggleCorrection(true, 'edit')"
                            class="group flex flex-col items-center justify-center p-2 md:p-8 bg-rose-50 border border-rose-100 rounded-2xl md:rounded-[2.5rem] hover:bg-rose-100 transition duration-300 shadow-sm">
                            <div
                                class="w-8 h-8 md:w-16 md:h-16 bg-rose-500 text-white rounded-full flex items-center justify-center text-sm md:text-2xl mb-1 md:mb-4 shadow-lg shadow-rose-200 group-hover:-translate-y-1 transition transform">
                                <i class="fa-solid fa-pen-nib"></i>
                            </div>
                            <span class="font-black text-rose-800 text-[10px] md:text-xl">تعديل</span>
                            <span class="hidden md:block text-sm text-rose-600 font-bold mt-2">خاطئة تحتاج تعديل</span>
                        </button>

                        <button onclick="toggleCorrection(true, 'correct')"
                            class="group flex flex-col items-center justify-center p-2 md:p-8 bg-amber-50 border border-amber-100 rounded-2xl md:rounded-[2.5rem] hover:bg-amber-100 transition duration-300 shadow-sm">
                            <div
                                class="w-8 h-8 md:w-16 md:h-16 bg-amber-500 text-white rounded-full flex items-center justify-center text-sm md:text-2xl mb-1 md:mb-4 shadow-lg shadow-amber-200 group-hover:-translate-y-1 transition transform">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <span class="font-black text-amber-800 text-[10px] md:text-xl">تصحيح</span>
                            <span class="hidden md:block text-sm text-amber-600 font-bold mt-2">غير دقيقة تحتاج تصحيح</span>
                        </button>

                        <button onclick="submitTask(true)"
                            class="group flex flex-col items-center justify-center p-2 md:p-8 bg-emerald-50 border border-emerald-100 rounded-2xl md:rounded-[2.5rem] hover:bg-emerald-100 transition duration-300 shadow-sm">
                            <div
                                class="w-8 h-8 md:w-16 md:h-16 bg-emerald-500 text-white rounded-full flex items-center justify-center text-sm md:text-2xl mb-1 md:mb-4 shadow-lg shadow-emerald-200 group-hover:-translate-y-1 transition transform">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <span class="font-black text-emerald-800 text-[10px] md:text-xl">صحيحة</span>
                            <span class="hidden md:block text-sm text-emerald-600 font-bold mt-2">اعتماد ومتابعة</span>
                        </button>
                    </div>

                    <!-- Hidden Correction Form -->
                    <div id="correction-area"
                        class="hidden space-y-4 pt-4 animate-in fade-in slide-in-from-top-4 duration-300">
                        
                        <!-- NEW SECTION: Add Legal Reference -->
                        <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100 shadow-sm relative z-30">
                            <div class="absolute inset-0 rounded-2xl overflow-hidden pointer-events-none">
                                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100/50 -mr-8 -mt-8 rounded-full blur-xl"></div>
                            </div>
                            <div class="relative z-10">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="block text-sm font-black text-blue-800 flex items-center gap-2">
                                        <i class="fa-solid fa-book-section text-blue-500"></i> إضافة مراجع قانونية للتصحيح (اختياري)
                                    </label>
                                    <button type="button" onclick="addReferenceRow()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-black rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                                        <i class="fa-solid fa-plus text-[10px]"></i> أضف مرجعاً آخر
                                    </button>
                                </div>
                                <div id="references-list" class="space-y-3">
                                    <!-- First Row -->
                                    <div class="bg-white p-4 rounded-xl border border-blue-100 reference-row space-y-3 relative">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 relative">
                                                <input type="text" class="ref-system w-full px-4 py-3 bg-white border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm font-bold text-gray-700 transition" placeholder="النظام (مثال: نظام الإثبات)" autocomplete="off">
                                                <div class="ref-system-dropdown hidden absolute right-0 left-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg z-50 text-right"></div>
                                            </div>
                                            <div class="flex-1 relative">
                                                <input type="text" class="ref-article w-full px-4 py-3 bg-white border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm font-bold text-gray-700 transition" placeholder="المادة (مثال: المادة الأولى)" autocomplete="off" disabled>
                                                <div class="ref-article-dropdown hidden absolute right-0 left-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg z-50 text-right"></div>
                                                <input type="hidden" class="ref-article-id">
                                            </div>
                                            <button type="button" onclick="removeReferenceRow(this)" class="p-3 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition opacity-40 hover:opacity-100 flex-shrink-0">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </div>
                                        <div class="ref-article-preview hidden bg-blue-50/40 p-4 rounded-xl border border-blue-100/50 text-sm font-bold text-gray-700 leading-relaxed text-right transition-all duration-300"></div>
                                    </div>
                                </div>
                                <p class="text-xs text-blue-600/80 mt-3 font-bold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info"></i> سيتم ربط هذه الأنظمة والمواد بالإجابة الصحيحة لتطوير الذكاء الاصطناعي.
                                </p>
                            </div>
                        </div>

                        <div class="p-1 bg-rose-50 rounded-2xl border border-rose-100">
                            <textarea id="correct_answer"
                                class="w-full h-40 p-5 bg-transparent border-none focus:ring-0 outline-none text-lg font-bold text-gray-800 resize-none"
                                placeholder="اكتب التعديل الصحيح هنا..."></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="toggleCorrection(false)"
                                class="px-6 py-4 bg-gray-100 text-gray-600 font-black rounded-xl hover:bg-gray-200 transition">إلغاء</button>
                            <button onclick="submitTask(false)" id="btn-submit-edit"
                                class="flex-1 flex justify-center items-center gap-2 px-6 py-4 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                حفظ التعديل <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>

                        </div>
                    </div>


                </div>

                <!-- Card Footer Navigation -->
                <div class="bg-gray-50/50 px-8 py-5 border-t border-gray-100 flex justify-between items-center rounded-b-[2rem]">
                    <button onclick="skipTask()"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-600 font-bold text-sm rounded-xl hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition shadow-sm">
                        تخطي <i class="fa-solid fa-forward-step"></i>
                    </button>
                    <button onclick="previousTask()"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-600 font-bold text-sm rounded-xl hover:bg-gray-100 hover:border-gray-300 hover:text-gray-900 transition shadow-sm">
                        <i class="fa-solid fa-angle-right"></i> السابقة
                    </button>
                </div>

            </div>

        </main>

        <!-- Custom Tag Modal -->
        <div id="tag-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden transform animate-in zoom-in-95 duration-300 border border-slate-100">
                <div class="bg-slate-50 px-8 py-8 border-b border-slate-100 text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-100 rotate-3">
                        <i class="fa-solid fa-plus text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">إضافة وسم جديد</h3>
                    <p class="text-slate-400 text-xs font-bold mt-2">أدخل اسماً معبراً للتصنيف الجديد</p>
                </div>
                <div class="p-8 bg-white">
                    <div class="space-y-4">
                        <input type="text" id="new-tag-input" 
                            class="w-full px-6 py-5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-bold text-slate-700 text-center text-lg"
                            placeholder="مثلاً: تجاري، إثبات..."
                            onkeydown="if(event.key === 'Enter') confirmAddTag()">
                    </div>
                    <div class="flex gap-4 mt-8">
                        <button onclick="confirmAddTag()" class="flex-[2] px-6 py-5 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 active:scale-95">إضافة الآن</button>
                        <button onclick="closeTagModal()" class="flex-1 px-6 py-5 bg-slate-100 text-slate-500 font-black rounded-2xl hover:bg-slate-200 transition active:scale-95">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>

    @else
        <main class="flex-1 flex items-center justify-center">
            <div class="text-center p-12 bg-white rounded-[3rem] shadow-sm border border-gray-100 max-w-md w-full">
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <i class="fa-solid fa-party-horn text-5xl text-yellow-500 absolute top-0 left-0 animate-bounce"></i>
                    <i class="fa-solid fa-star text-2xl text-blue-400 absolute bottom-0 right-0 animate-pulse"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-800 mb-3">عظيم! لا توجد مهام جديدة.</h2>
                <p class="text-gray-500 text-sm font-medium mb-8 leading-relaxed">لقد قمت بتنقيح كل البيانات المتاحة في
                    القائمة. يمكنك العودة لاحقاً للمزيد.</p>
                <a href="{{ route('dashboard.expert') }}"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-200">
                    <i class="fa-solid fa-rotate"></i> التحقق من مهام جديدة
                </a>
            </div>
        </main>
    @endif

    <script>
        // Timer Logic
        let seconds = 0;
        const timerEl = document.getElementById('timer');
        if (timerEl) {
            setInterval(() => {
                seconds++;
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                timerEl.textContent = `${m}:${s}`;
            }, 1000);
        }

        function toggleCorrection(show, mode = 'edit') {
            const area = document.getElementById('correction-area');
            const buttons = document.getElementById('action-buttons');
            const textarea = document.getElementById('correct_answer');

            if (show) {
                area.classList.remove('hidden');
                buttons.classList.add('hidden');
                textarea.value = document.getElementById('ai-answer-text').innerText.trim();
                
                // Change placeholder based on mode
                if (mode === 'correct') {
                    textarea.placeholder = "اكتب التصحيح اللازم هنا...";
                    document.getElementById('btn-submit-edit').innerHTML = 'حفظ التصحيح <i class="fa-solid fa-paper-plane"></i>';
                } else {
                    textarea.placeholder = "اكتب التعديل الصحيح هنا...";
                    document.getElementById('btn-submit-edit').innerHTML = 'حفظ التعديل <i class="fa-solid fa-paper-plane"></i>';
                }
                
                textarea.focus();
            } else {
                area.classList.add('hidden');
                buttons.classList.remove('hidden');
            }
        }

        function addNewTag() {
            const modal = document.getElementById('tag-modal');
            const input = document.getElementById('new-tag-input');
            modal.classList.remove('hidden');
            input.value = '';
            setTimeout(() => input.focus(), 100);
        }

        function closeTagModal() {
            document.getElementById('tag-modal').classList.add('hidden');
        }

        function confirmAddTag() {
            const input = document.getElementById('new-tag-input');
            const tagName = input.value.trim();
            
            if (tagName) {
                const container = document.getElementById('tags-container');
                const newLabel = document.createElement('label');
                newLabel.className = "flex items-center gap-1.5 px-2 py-1 md:px-4 md:py-2 bg-blue-50 border-blue-200 border rounded-lg md:rounded-xl cursor-pointer hover:bg-gray-50 transition animate-in zoom-in duration-300 shadow-sm";
                newLabel.innerHTML = `
                    <span class="text-[10px] md:text-sm font-bold text-blue-700">${tagName}</span>
                    <input type="checkbox" checked name="tags[]" value="${tagName}" class="w-3 h-3 md:w-4 md:h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                `;
                container.prepend(newLabel);
                closeTagModal();
            }
        }

        let isSubmitting = false;

        function disableAllButtons(disable) {
            const buttons = document.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.disabled = disable;
                if (disable) {
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        function initAutocomplete(row) {
            const systemInput = row.querySelector('.ref-system');
            const systemDropdown = row.querySelector('.ref-system-dropdown');
            const articleInput = row.querySelector('.ref-article');
            const articleDropdown = row.querySelector('.ref-article-dropdown');
            const articleIdInput = row.querySelector('.ref-article-id');
            const previewContainer = row.querySelector('.ref-article-preview');

            let systemTimeout = null;
            let articleTimeout = null;

            // --- SYSTEM SEARCH ---
            const fetchSystems = async (query) => {
                try {
                    const res = await fetch(`/dashboard/expert/legal-workbench/search-systems?q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    
                    systemDropdown.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(systemName => {
                            const item = document.createElement('div');
                            item.className = "px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm font-bold text-gray-700 transition border-b border-gray-100 last:border-0";
                            item.textContent = systemName;
                            item.onclick = () => {
                                systemInput.value = systemName;
                                systemDropdown.classList.add('hidden');
                                
                                // Reset and enable article input
                                articleInput.disabled = false;
                                articleInput.value = '';
                                articleIdInput.value = '';
                                previewContainer.innerHTML = '';
                                previewContainer.classList.add('hidden');
                                articleInput.focus();
                                fetchArticles(systemName, ''); // trigger empty search to show list
                            };
                            systemDropdown.appendChild(item);
                        });
                        systemDropdown.classList.remove('hidden');
                    } else {
                        systemDropdown.classList.add('hidden');
                    }
                } catch (e) {
                    console.error("Error fetching systems:", e);
                }
            };

            systemInput.addEventListener('focus', () => {
                fetchSystems(systemInput.value.trim());
            });

            systemInput.addEventListener('input', () => {
                clearTimeout(systemTimeout);
                systemTimeout = setTimeout(() => {
                    fetchSystems(systemInput.value.trim());
                }, 300);
            });

            // --- ARTICLE SEARCH ---
            const fetchArticles = async (systemName, query) => {
                try {
                    const res = await fetch(`/dashboard/expert/legal-workbench/search-articles?system=${encodeURIComponent(systemName)}&q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    
                    articleDropdown.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(art => {
                            const item = document.createElement('div');
                            item.className = "px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm font-bold text-gray-700 transition border-b border-gray-100 last:border-0 text-right";
                            
                            const title = art.article_title || 'مادة';
                            const snippet = art.content ? art.content.substring(0, 60) + '...' : '';
                            item.innerHTML = `
                                <div class="text-blue-700 font-bold">${title}</div>
                                <div class="text-xs text-gray-400 font-normal truncate mt-0.5">${snippet}</div>
                            `;
                            
                            item.onclick = () => {
                                articleInput.value = title;
                                articleIdInput.value = art.id;
                                articleDropdown.classList.add('hidden');
                                
                                // Show article text preview
                                previewContainer.innerHTML = `
                                    <div class="text-blue-800 font-black mb-1.5 flex items-center gap-1.5">
                                        <i class="fa-solid fa-file-invoice text-xs"></i> نص ${title}:
                                    </div>
                                    <div class="text-gray-600 font-normal whitespace-pre-wrap leading-relaxed bg-white p-3 rounded-lg border border-blue-50/50 text-right" dir="rtl">${art.content}</div>
                                `;
                                previewContainer.classList.remove('hidden');
                            };
                            articleDropdown.appendChild(item);
                        });
                        articleDropdown.classList.remove('hidden');
                    } else {
                        articleDropdown.classList.add('hidden');
                    }
                } catch (e) {
                    console.error("Error fetching articles:", e);
                }
            };

            articleInput.addEventListener('focus', () => {
                const systemVal = systemInput.value.trim();
                if (systemVal) {
                    fetchArticles(systemVal, articleInput.value.trim());
                }
            });

            articleInput.addEventListener('input', () => {
                const systemVal = systemInput.value.trim();
                if (systemVal) {
                    clearTimeout(articleTimeout);
                    articleTimeout = setTimeout(() => {
                        fetchArticles(systemVal, articleInput.value.trim());
                    }, 300);
                }
            });
        }

        // Initialize autocompletes on existing rows
        document.querySelectorAll('.reference-row').forEach(row => {
            initAutocomplete(row);
        });

        // Hide dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.ref-system') && !e.target.closest('.ref-system-dropdown')) {
                document.querySelectorAll('.ref-system-dropdown').forEach(d => d.classList.add('hidden'));
            }
            if (!e.target.closest('.ref-article') && !e.target.closest('.ref-article-dropdown')) {
                document.querySelectorAll('.ref-article-dropdown').forEach(d => d.classList.add('hidden'));
            }
        });

        function addReferenceRow() {
            const container = document.getElementById('references-list');
            const newRow = document.createElement('div');
            newRow.className = "bg-white p-4 rounded-xl border border-blue-100 reference-row space-y-3 relative animate-in slide-in-from-top-2 duration-200";
            newRow.innerHTML = `
                <div class="flex items-center gap-2">
                    <div class="flex-1 relative">
                        <input type="text" class="ref-system w-full px-4 py-3 bg-white border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm font-bold text-gray-700 transition" placeholder="النظام (مثال: نظام الإثبات)" autocomplete="off">
                        <div class="ref-system-dropdown hidden absolute right-0 left-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg z-50 text-right"></div>
                    </div>
                    <div class="flex-1 relative">
                        <input type="text" class="ref-article w-full px-4 py-3 bg-white border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm font-bold text-gray-700 transition" placeholder="المادة (مثال: المادة الأولى)" autocomplete="off" disabled>
                        <div class="ref-article-dropdown hidden absolute right-0 left-0 mt-1 max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg z-50 text-right"></div>
                        <input type="hidden" class="ref-article-id">
                    </div>
                    <button type="button" onclick="removeReferenceRow(this)" class="p-3 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition opacity-40 hover:opacity-100 flex-shrink-0">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>
                <div class="ref-article-preview hidden bg-blue-50/40 p-4 rounded-xl border border-blue-100/50 text-sm font-bold text-gray-700 leading-relaxed text-right transition-all duration-300"></div>
            `;
            container.appendChild(newRow);
            initAutocomplete(newRow);
        }

        function removeReferenceRow(btn) {
            const rows = document.querySelectorAll('.reference-row');
            if (rows.length > 1) {
                btn.closest('.reference-row').remove();
            } else {
                const row = btn.closest('.reference-row');
                row.querySelector('.ref-system').value = '';
                row.querySelector('.ref-article').value = '';
                row.querySelector('.ref-article').disabled = true;
                row.querySelector('.ref-article-id').value = '';
                const preview = row.querySelector('.ref-article-preview');
                preview.innerHTML = '';
                preview.classList.add('hidden');
            }
        }

        async function submitTask(isCorrect) {
            if (isSubmitting) return;
            const taskId = "{{ $task->id ?? '' }}";
            if (!taskId) return;

            isSubmitting = true;
            disableAllButtons(true);

            const selectedTags = Array.from(document.querySelectorAll('input[name="tags[]"]:checked')).map(cb => cb.value);

            // Collect all references
            const references = [];
            document.querySelectorAll('.reference-row').forEach(row => {
                const system = row.querySelector('.ref-system').value.trim();
                const article = row.querySelector('.ref-article').value.trim();
                const article_id = row.querySelector('.ref-article-id').value.trim();
                if (system || article) {
                    references.push({ 
                        system: system, 
                        article: article, 
                        article_id: article_id ? parseInt(article_id) : null 
                    });
                }
            });

            const data = {
                task_id: taskId,
                is_correct: isCorrect,
                correct_answer: isCorrect ? null : document.getElementById('correct_answer').value,
                correct_law_references: isCorrect ? [] : references,
                tags: selectedTags,
                time_spent: seconds,
                _token: "{{ csrf_token() }}"
            };

            try {
                const response = await fetch("{{ route('dashboard.expert.legal_workbench.submit') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (response.status === 419) {
                    alert('انتهت صلاحية الجلسة (Session Expired) بسبب خمول الصفحة لفترة طويلة. يرجى تحديث الصفحة وإعادة المحاولة.');
                    isSubmitting = false;
                    disableAllButtons(false);
                    return;
                }

                if (response.status === 403) {
                    alert('انتهت صلاحية حجز هذه المهمة لك (تتطلب ساعتين كحد أقصى) أو تم حظر حسابك. يرجى تحديث الصفحة لجلب مهمة جديدة.');
                    isSubmitting = false;
                    disableAllButtons(false);
                    return;
                }

                if (response.status === 401) {
                    alert('تم تسجيل الخروج. يرجى تسجيل الدخول مرة أخرى في نافذة جديدة.');
                    isSubmitting = false;
                    disableAllButtons(false);
                    return;
                }

                if (!response.ok) {
                    throw new Error('HTTP status ' + response.status);
                }

                const result = await response.json();
                if (result.success) {
                    animateAndReload();
                } else {
                    alert('خطأ: ' + result.message);
                    isSubmitting = false;
                    disableAllButtons(false);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('حدث خطأ تقني أثناء الحفظ. يرجى التأكد من اتصال الإنترنت وتحديث الصفحة.');
                isSubmitting = false;
                disableAllButtons(false);
            }
        }

        async function skipTask() {
            if (isSubmitting) return;
            const taskId = "{{ $task->id ?? '' }}";
            if (!taskId) return;
            
            isSubmitting = true;
            disableAllButtons(true);
            
            try {
                const response = await fetch("{{ route('dashboard.expert.legal_workbench.skip') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ task_id: taskId, _token: "{{ csrf_token() }}" })
                });
                const result = await response.json();
                if (result.success) {
                    animateAndReload();
                } else {
                    isSubmitting = false;
                    disableAllButtons(false);
                }
            } catch (error) {
                console.error('Error:', error);
                isSubmitting = false;
                disableAllButtons(false);
            }
        }

        async function previousTask() {
            if (isSubmitting) return;
            
            isSubmitting = true;
            disableAllButtons(true);
            
            try {
                const response = await fetch("{{ route('dashboard.expert.legal_workbench.previous') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ _token: "{{ csrf_token() }}" })
                });
                const result = await response.json();
                if (result.success && result.found) {
                    animateAndReload(true);
                } else {
                    isSubmitting = false;
                    disableAllButtons(false);
                    if (result.success && !result.found) {
                        // لا توجد مهمة سابقة
                        const btn = document.querySelector('button[onclick="previousTask()"]');
                        if (btn) {
                            btn.textContent = 'لا توجد سابقة';
                            btn.disabled = true;
                            btn.classList.add('opacity-40', 'cursor-not-allowed');
                            setTimeout(() => {
                                btn.innerHTML = '<i class="fa-solid fa-angle-right"></i> السابقة';
                                btn.disabled = false;
                                btn.classList.remove('opacity-40', 'cursor-not-allowed');
                            }, 2000);
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                isSubmitting = false;
                disableAllButtons(false);
            }
        }

        function toggleAccordion(id, btn) {
            const content = document.getElementById(id);
            const icon = btn.querySelector('i.fa-chevron-down');
            const isHidden = content.classList.contains('hidden');
            
            // Close all other accordions first (optional, but cleaner)
            document.querySelectorAll('[id^="article-"], #judgment-accordion').forEach(el => {
                if (el.id !== id) {
                    el.classList.add('hidden');
                    const otherBtn = document.querySelector(`[onclick*="'${el.id}'"]`);
                    if (otherBtn) {
                        const otherIcon = otherBtn.querySelector('i.fa-chevron-down');
                        if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                    }
                }
            });

            if (isHidden) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        async function deleteCitation(articleId, event) {
            if (event) event.stopPropagation();
            
            if (!confirm('هل أنت متأكد من رغبتك في حذف هذا المرجع القانوني؟')) {
                return;
            }

            const taskId = "{{ $task->id ?? '' }}";
            if (!taskId || !articleId) return;

            try {
                const response = await fetch("{{ route('dashboard.expert.legal_workbench.delete_citation') }}", {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        task_id: taskId, 
                        citation_id: articleId 
                    })
                });

                const result = await response.json();
                if (result.success) {
                    animateAndReload();
                } else {
                    alert('خطأ: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('حدث خطأ تقني أثناء حذف المرجع.');
            }
        }

        function animateAndReload(isPrevious = false) {
            const card = document.querySelector('.max-w-4xl');
            card.style.opacity = '0';
            card.style.transform = isPrevious ? 'translateX(20px)' : 'translateX(-20px)';
            card.style.transition = 'all 0.3s ease';
            setTimeout(() => window.location.reload(), 300);
        }
    </script>

</body>

</html>