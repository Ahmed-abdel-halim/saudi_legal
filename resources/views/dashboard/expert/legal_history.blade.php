<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل الإنجازات القانونية | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
        .diff-added { background-color: #ecfdf5; color: #065f46; padding: 2px 4px; border-radius: 4px; }
        .diff-removed { background-color: #fef2f2; color: #991b1b; text-decoration: line-through; padding: 2px 4px; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>

<body class="text-slate-800 antialiased custom-scrollbar">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard.expert') }}" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition shadow-sm">
                    <i class="fa-solid fa-arrow-right rtl:rotate-0"></i>
                </a>
                <h1 class="text-xl font-black text-slate-800">سجل الإنجازات القانونية</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">إجمالي المراجعات</p>
                    <p class="text-lg font-black text-emerald-600 leading-none">{{ $reviews->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-award"></i>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10 max-w-5xl">

        <div class="space-y-8">
            @forelse($reviews as $item)
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    
                    <!-- Item Header -->
                    <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-400 shadow-sm">#{{ $item->id }}</span>
                            <span class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-emerald-500"></i>
                                {{ $item->reviewed_at->format('Y/m/d - h:i A') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if($item->review_status === 'Approved')
                                <span class="px-4 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-black border border-emerald-100 flex items-center gap-2">
                                    <i class="fa-solid fa-check-double"></i> اعتمدت كما هي
                                </span>
                            @elseif($item->review_status === 'Modified')
                                <span class="px-4 py-1.5 bg-amber-50 text-amber-700 rounded-full text-xs font-black border border-amber-100 flex items-center gap-2">
                                    <i class="fa-solid fa-pen-nib"></i> تم تعديلها
                                </span>
                            @else
                                <span class="px-4 py-1.5 bg-rose-50 text-rose-700 rounded-full text-xs font-black border border-rose-100 flex items-center gap-2">
                                    <i class="fa-solid fa-xmark"></i> مرفوضة
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-8 md:p-10 space-y-8">
                        
                        <!-- Question -->
                        <div class="space-y-3">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-circle-question text-blue-500"></i> السؤال
                            </h3>
                            <p class="text-xl font-bold text-slate-700 leading-relaxed">{{ $item->question }}</p>
                        </div>

                        <!-- Comparison -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <!-- AI Answer -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    إجابة الذكاء الاصطناعي <i class="fa-solid fa-robot"></i>
                                </h4>
                                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 text-sm leading-loose text-slate-600 font-medium whitespace-pre-wrap {{ $item->review_status === 'Modified' ? 'opacity-60' : '' }}">
                                    {{ $item->generated_answer }}
                                </div>
                            </div>

                            <!-- Expert Correction -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 text-emerald-600">
                                    إجابتك المعتمدة <i class="fa-solid fa-user-tie"></i>
                                </h4>
                                <div class="bg-emerald-50/30 p-6 rounded-3xl border border-emerald-100 text-sm leading-loose text-slate-800 font-bold shadow-inner whitespace-pre-wrap">
                                    @if($item->review_status === 'Modified')
                                        {{ $item->corrected_answer }}
                                    @else
                                        {{ $item->generated_answer }}
                                    @endif
                                </div>
                            </div>

                        </div>

                        <!-- Citations -->
                        @if($item->record && $item->record->citations->count() > 0)
                            <div class="pt-6 border-t border-slate-50">
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fa-solid fa-scale-balanced text-emerald-500 text-xs"></i>
                                    <span class="text-xs font-black text-slate-400">المراجع القانونية المرتبطة</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($item->record->citations as $citation)
                                        <div class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-[11px] font-bold text-slate-600 hover:border-emerald-200 transition cursor-default flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            {{ $citation->system_name }} {{ $citation->article_number ? ' - المادة ' . $citation->article_number : '' }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            @empty
                <div class="text-center py-20 bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                    <div class="w-24 h-24 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-box-open text-4xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-400">لا يوجد سجل إنجازات بعد</h2>
                    <p class="text-slate-300 mt-2">ابدأ بمراجعة المهام لتظهر هنا</p>
                    <a href="{{ route('dashboard.expert.legal_workbench') }}" class="inline-flex items-center gap-2 mt-8 px-8 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-100">
                        ابدأ المراجعة الآن
                    </a>
                </div>
            @endforelse

            <!-- Pagination -->
            <div class="py-10">
                {{ $reviews->links() }}
            </div>
        </div>

    </main>

</body>
</html>
