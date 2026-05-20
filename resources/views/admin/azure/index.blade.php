@extends('layouts.admin')

@section('title', 'بوابة تكامل Azure')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">بوابة تكامل Azure</h1>
        <p class="text-slate-500 mt-1">مراقبة محرك البحث الذكي (AI Hybrid Search) وحالة التخزين السحابي.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.legal.index') }}" class="bg-white border border-slate-200 text-slate-600 px-4 py-2.5 rounded-lg font-bold shadow-sm hover:bg-slate-50 transition flex items-center gap-2 text-sm">
            <i class="fa-solid fa-file-contract"></i> الإدارة القانونية
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Left Column: Services & Actions --}}
    <div class="lg:col-span-2 space-y-8">
        {{-- Status Overview Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{-- Search Service Status Card --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <i class="fa-solid fa-magnifying-glass text-8xl text-indigo-500"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    @if($searchEnabled && $connectionOk)
                        <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-[10px] font-black uppercase border border-emerald-100 tracking-wider">متصل ومفعل</span>
                    @elseif($searchEnabled)
                        <span class="bg-red-50 text-red-700 px-2.5 py-1 rounded-full text-[10px] font-black uppercase border border-red-100 tracking-wider">فشل الاتصال</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full text-[10px] font-black uppercase border border-slate-200 tracking-wider">غير مفعل</span>
                    @endif
                </div>
                <h3 class="text-slate-800 font-bold text-lg mb-1">Azure AI Search</h3>
                <p class="text-xs text-slate-500 mb-4">{{ $searchEndpoint ?: 'لم يتم ضبط عنوان الخدمة بعد.' }}</p>
                <div class="border-t border-slate-100 pt-4 flex justify-between items-center text-xs font-semibold text-slate-600">
                    <span>الـ Index:</span>
                    <span class="font-mono text-indigo-600">{{ config('azure.search.index') }}</span>
                </div>
            </div>

            {{-- Blob Storage Status Card --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <i class="fa-solid fa-box-archive text-8xl text-emerald-500"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    @if($storageEnabled)
                        <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-[10px] font-black uppercase border border-emerald-100 tracking-wider">مكون بنجاح</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full text-[10px] font-black uppercase border border-slate-200 tracking-wider">غير مضبوط</span>
                    @endif
                </div>
                <h3 class="text-slate-800 font-bold text-lg mb-1">Azure Blob Storage</h3>
                <p class="text-xs text-slate-500 mb-4">{{ config('azure.storage.name') ?: 'لا توجد بيانات حساب.' }}</p>
                <div class="border-t border-slate-100 pt-4 flex justify-between items-center text-xs font-semibold text-slate-600">
                    <span>الـ Container الرئيسي:</span>
                    <span class="font-mono text-emerald-600">{{ config('azure.storage.containers.legal_data') }}</span>
                </div>
            </div>
        </div>

        {{-- Sync and Reindex Management Panel --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-rotate me-1 text-primary"></i> مزامنة وفهرسة البيانات</h3>
                <span class="text-xs text-slate-400 font-medium">إعادة بناء الفهرس ورفع البيانات لـ Azure</span>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    يمكنك تشغيل عملية فهرسة كاملة لجميع السجلات القانونية والأسئلة الشائعة من خلال الضغط على الزر أدناه. ستقوم هذه العملية بطلب المتجهات (Vectors) من Gemini API ورفعها دفعة واحدة إلى Azure AI Search. يتم تشغيل المزامنة في الخلفية عبر الـ Queue.
                </p>

                <form method="POST" action="{{ route('admin.azure.sync') }}" class="flex flex-wrap gap-4 items-center">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1.5">نوع البيانات المراد فهرستها</label>
                        <select name="type" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 transition min-w-[200px] outline-none">
                            <option value="all">كل شيء (All)</option>
                            <option value="tasks">المهام القضائية فقط (Tasks)</option>
                            <option value="articles">المواد والأنظمة القانونية (Articles)</option>
                            <option value="qa_pairs">الأسئلة الشائعة المدققة (QA Pairs)</option>
                        </select>
                    </div>

                    <div class="pt-5">
                        <button type="submit" class="bg-primary hover:bg-primary/95 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md shadow-primary/30 transition flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up"></i> بدء المزامنة الآن
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Index Statistics Panel --}}
        @if($searchStats)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-chart-simple me-1 text-indigo-500"></i> إحصائيات الفهرس (Index Stats)</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 flex flex-col justify-center">
                        <span class="text-xs font-bold text-slate-400 mb-1">إجمالي المستندات المفهرسة</span>
                        <span class="text-3xl font-black text-slate-800">{{ number_format($searchStats['documentCount'] ?? 0) }} مستند</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 flex flex-col justify-center">
                        <span class="text-xs font-bold text-slate-400 mb-1">المساحة التخزينية المستخدمة</span>
                        <span class="text-3xl font-black text-slate-800">{{ number_format(($searchStats['storageSize'] ?? 0) / 1024, 2) }} KB</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Live Hybrid Search Testing Panel --}}
    <div class="space-y-8">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-vial me-1 text-rose-500"></i> اختبار البحث الهجين (Hybrid Search)</h3>
            </div>
            <div class="p-6">
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    اكتب سؤالاً قانونياً بالأسفل لاختبار دقة البحث الهجين وحساب سرعة الاستجابة من Azure AI Search ومقارنة النتائج.
                </p>

                <form method="GET" action="{{ route('admin.azure.index') }}" class="space-y-4">
                    <div>
                        <input type="text" name="q" value="{{ $testQuery }}" placeholder="مثال: نفقة المطلقة الحامل..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition outline-none">
                    </div>
                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-2.5 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> تنفيذ البحث
                    </button>
                </form>

                @if($testResults !== null)
                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <div class="flex items-center justify-between text-xs text-slate-400 font-bold mb-4">
                            <span>زمن الاستجابة:</span>
                            <span class="text-emerald-600 font-mono">{{ $elapsedMs }} ms</span>
                        </div>

                        <div class="space-y-4">
                            @forelse($testResults as $result)
                                <div class="bg-slate-50 hover:bg-slate-100/80 transition border border-slate-100 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider">
                                            {{ $result->source_type ?? 'مستند' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400">
                                            معدل الثقة: {{ round($result->relevance_score ?? 0, 3) }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-xs mb-1">{{ $result->question }}</h4>
                                    <p class="text-[11px] text-slate-500 leading-relaxed truncate">{{ $result->answer }}</p>
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-300">
                                    <i class="fa-solid fa-circle-question text-4xl mb-2 opacity-30"></i>
                                    <p class="text-xs font-bold">لا توجد نتائج بحث مطابقة</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
