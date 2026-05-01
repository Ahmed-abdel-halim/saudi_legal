@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap');

    :root {
        --primary: #4f46e5;
        --secondary: #0ea5e9;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --glass: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin-slow {
        animation: spin-slow 8s linear infinite;
    }

    .svg-icon {
        width: 1.5rem;
        height: 1.5rem;
        fill: currentColor;
    }

    .metric-icon .svg-icon {
        width: 2rem;
        height: 2rem;
    }

    body {
        font-family: 'Cairo', sans-serif;
    }

    .dashboard-bg {
        background: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%),
            radial-gradient(at 0% 100%, rgba(239, 68, 68, 0.05) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .glass-card {
        background: var(--glass);
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: 2rem;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.8);
    }

    .metric-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.25rem;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .glass-card:hover .metric-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .live-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--success);
        position: relative;
    }

    .live-dot::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(3); opacity: 0; }
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.02);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    .status-pill {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .btn-premium {
        background: #1e293b;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 1.25rem;
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-premium:hover {
        background: #0f172a;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .conflict-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 0.9rem;
    }
</style>

<div class="dashboard-bg min-h-screen text-slate-900 pb-20 pt-6">
    <div class="container mx-auto px-6">
        
        <!-- Premium Header -->
        <header class="flex flex-col md:flex-row items-center justify-between gap-6 mb-12">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard.projects') }}" class="w-12 h-12 glass-card flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all">
                    <svg class="w-5 h-5 fill-currentColor rtl:rotate-180" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-800">
                        {{ app()->getLocale() == 'ar' ? 'نظام حوكمة الجودة والذكاء القانوني' : 'Governance & Legal Intelligence' }}
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">
                        {{ app()->getLocale() == 'ar' ? 'متابعة مباشرة لأداء الخبراء وتدقيق البيانات' : 'Real-time expert performance & data auditing' }}
                    </p>
                </div>
            </div>
            <div class="glass-card px-6 py-3 flex items-center gap-4">
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ app()->getLocale() == 'ar' ? 'آخر تحديث للبيانات' : 'Last sync' }}</span>
                    <span class="text-sm font-black text-slate-700">{{ now()->format('H:i:s') }}</span>
                </div>
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <svg class="svg-icon animate-spin-slow" viewBox="0 0 24 24"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46A7.93 7.93 0 0020 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74A7.93 7.93 0 004 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
                </div>
            </div>
        </header>

        <!-- Main Upload Section -->
        <section class="glass-card p-10 mb-12 relative overflow-hidden group">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-600 opacity-[0.03] rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
            <div class="relative flex flex-col lg:flex-row items-center gap-10">
                <div class="lg:w-2/3">
                    <h2 class="text-2xl font-black text-slate-800 mb-4 flex items-center gap-3">
                        <svg class="svg-icon text-indigo-500" viewBox="0 0 24 24"><path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                        {{ app()->getLocale() == 'ar' ? 'استيراد مهام جديدة للتدقيق' : 'Import New Audit Tasks' }}
                    </h2>
                    <p class="text-slate-500 text-lg mb-8 leading-relaxed">
                        {{ app()->getLocale() == 'ar' 
                            ? 'قم برفع ملف البيانات (CSV) لبدء توزيع المهام آلياً على الخبراء وتفعيل خوارزميات كشف التلاعب.' 
                            : 'Upload your dataset to begin automated distribution and activate manipulation detection algorithms.' }}
                    </p>
                    <form id="upload-form" action="{{ route('client.governance.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4">
                        @csrf
                        <div class="flex-1 relative group/input">
                            <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt,.jsonl" class="hidden" onchange="updateFileName(this)"/>
                            <label for="csv_file" class="flex items-center gap-4 px-6 py-4 bg-white/50 border-2 border-dashed border-slate-200 rounded-2xl cursor-pointer hover:border-indigo-400 hover:bg-white transition-all">
                                <svg class="svg-icon text-slate-400 group-hover/input:text-indigo-500" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                <span id="file-name-display" class="text-slate-600 font-bold">{{ app()->getLocale() == 'ar' ? 'اختر ملف (CSV, JSONL)...' : 'Select file (CSV, JSONL)...' }}</span>
                            </label>
                        </div>
                        <button type="submit" id="submit-btn" class="btn-premium px-10">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg>
                            <span id="btn-text">{{ app()->getLocale() == 'ar' ? 'بدء المعالجة' : 'Launch Batch' }}</span>
                        </button>
                    </form>

                    <!-- Progress Bar (Hidden by default) -->
                    <div id="progress-container" class="mt-6 hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-black text-indigo-600 uppercase tracking-widest">{{ app()->getLocale() == 'ar' ? 'جاري الرفع...' : 'Uploading...' }}</span>
                            <span id="progress-percent" class="text-xs font-black text-indigo-600">0%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200 p-0.5">
                            <div id="progress-bar" class="bg-indigo-500 h-full rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2 italic">
                            {{ app()->getLocale() == 'ar' 
                                ? 'يتم الآن نقل الملف إلى الخادم. يرجى عدم إغلاق الصفحة.' 
                                : 'Transferring file to server. Please do not close this page.' }}
                        </p>
                    </div>
                </div>
                <div class="lg:w-1/3 flex justify-center">
                    <div class="relative w-48 h-48 bg-indigo-50 rounded-full flex items-center justify-center border-4 border-white shadow-xl overflow-hidden">
                        <svg class="w-32 h-32 text-indigo-200 animate-pulse" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M15 9H9v6h6V9zm-2 4h-2v-2h2v2zm8-2V9h-2V7c0-1.1-.9-2-2-2h-2V3h-2v2h-2V3H9v2H7c-1.1 0-2 .9-2 2v2H3v2h2v2H3v2h2v2c0 1.1.9 2 2 2h2v2h2v-2h2v2h2v-2h2c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2zm-4 6H7V7h10v10z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Metrics Section -->
        <section class="space-y-12 mb-12">
            
            <!-- Expert Pulse -->
            <div>
                <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-users-viewfinder text-indigo-500"></i>
                    {{ app()->getLocale() == 'ar' ? 'متابعة الخبراء المباشرة' : 'Live Expert Pulse' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Experts -->
                    <div class="glass-card p-8 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي الخبراء' : 'Total Workforce' }}</p>
                            <h4 class="text-4xl font-black text-slate-800">{{ number_format($liveTracking['total_experts']) }}</h4>
                        </div>
                        <div class="metric-icon bg-slate-100 text-slate-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                    </div>
                    <!-- Active Experts -->
                    <div class="glass-card p-8 flex items-center justify-between border-emerald-100">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">{{ app()->getLocale() == 'ar' ? 'نشط الآن' : 'Active Duty' }}</p>
                            <h4 class="text-4xl font-black text-emerald-700 flex items-center gap-3">
                                {{ number_format($liveTracking['active_experts']) }}
                                <span class="live-dot"></span>
                            </h4>
                        </div>
                        <div class="metric-icon bg-emerald-50 text-emerald-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M2 22h20V2z"/></svg>
                        </div>
                    </div>
                    <!-- Avg Trust -->
                    <div class="glass-card p-8 flex items-center justify-between border-purple-100">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-purple-600 mb-1">{{ app()->getLocale() == 'ar' ? 'متوسط الثقة' : 'Integrity Index' }}</p>
                            <h4 class="text-4xl font-black text-purple-700">{{ $liveTracking['avg_trust_score'] }}%</h4>
                        </div>
                        <div class="metric-icon bg-purple-50 text-purple-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                        </div>
                    </div>
                    <!-- Banned -->
                    <div class="glass-card p-8 flex items-center justify-between border-rose-100">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-rose-600 mb-1">{{ app()->getLocale() == 'ar' ? 'محظورين' : 'Quarantined' }}</p>
                            <h4 class="text-4xl font-black text-rose-700">{{ number_format($liveTracking['banned_experts']) }}</h4>
                        </div>
                        <div class="metric-icon bg-rose-50 text-rose-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm5.31-3.1L6.69 5.69A7.941 7.941 0 0112 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accuracy Benchmarks -->
            <div>
                <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-chart-line text-indigo-500"></i>
                    {{ app()->getLocale() == 'ar' ? 'مقاييس الدقة والجودة' : 'Accuracy Benchmarks' }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Tasks -->
                    <div class="glass-card p-8 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي المهام' : 'Registry Size' }}</p>
                            <h4 class="text-4xl font-black text-slate-800">{{ number_format($metrics['total_tasks']) }}</h4>
                        </div>
                        <div class="metric-icon bg-slate-100 text-slate-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-6h2v6zm0-8h-2V7h2v2zm4 8h-2V7h2v10z"/></svg>
                        </div>
                    </div>
                    <!-- Perfect Consensus -->
                    <div class="glass-card p-8 flex items-center justify-between border-emerald-100">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">{{ app()->getLocale() == 'ar' ? 'إجماع كامل' : 'Perfect Sync' }}</p>
                            <h4 class="text-4xl font-black text-emerald-700">{{ $metrics['perfect_consensus_pct'] }}%</h4>
                            <p class="text-[9px] text-emerald-400 font-bold mt-1 uppercase tracking-tighter">3/3 Experts Agreed</p>
                        </div>
                        <div class="metric-icon bg-emerald-50 text-emerald-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM1 14l4.24-4.24 1.41 1.41L1 15.41z"/></svg>
                        </div>
                    </div>
                    <!-- Majority Vote -->
                    <div class="glass-card p-8 flex items-center justify-between border-indigo-100">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1">{{ app()->getLocale() == 'ar' ? 'تصويت الأغلبية' : 'Majority Rule' }}</p>
                            <h4 class="text-4xl font-black text-indigo-700">{{ $metrics['majority_vote_pct'] }}%</h4>
                            <p class="text-[9px] text-indigo-400 font-bold mt-1 uppercase tracking-tighter">2/3 Experts Agreed</p>
                        </div>
                        <div class="metric-icon bg-indigo-50 text-indigo-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                    </div>
                    <!-- Conflicts -->
                    <div class="glass-card p-8 flex items-center justify-between border-amber-100">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-1">{{ app()->getLocale() == 'ar' ? 'النزاعات' : 'Critical Conflicts' }}</p>
                            <h4 class="text-4xl font-black text-amber-700">{{ $metrics['conflict_pct'] }}%</h4>
                            <p class="text-[9px] text-amber-400 font-bold mt-1 uppercase tracking-tighter">Requires Intervention</p>
                        </div>
                        <div class="metric-icon bg-amber-50 text-amber-600">
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- Global Task Registry Table -->
        <section class="glass-card overflow-hidden mb-12 shadow-2xl shadow-slate-200/50 border-0">
            <div class="px-10 py-8 border-b border-slate-100 bg-white/60 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <i class="fa-solid fa-list-check text-indigo-500"></i>
                        {{ app()->getLocale() == 'ar' ? 'سجل المهام العام' : 'Global Task Registry' }}
                    </h3>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.3em] mt-1 italic">V2.0 Core System</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ $tasks->total() }} Units</span>
                    </div>
                    @if($tasks->total() > 0)
                        <a href="{{ route('client.governance.task.delete-all') }}" 
                           onclick="return confirm('Confirm Purge?')"
                           class="text-[10px] bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-6 py-3 rounded-2xl font-black uppercase tracking-[0.2em] transition-all border border-rose-100 flex items-center gap-2">
                            <svg class="w-3 h-3 fill-currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            {{ app()->getLocale() == 'ar' ? 'حذف الكل' : 'Purge All' }}
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 bg-slate-50/50 border-b border-slate-100">
                            <th class="px-10 py-6">ID</th>
                            <th class="px-10 py-6">{{ app()->getLocale() == 'ar' ? 'المحتوى' : 'Context' }}</th>
                            <th class="px-10 py-6 text-center">{{ app()->getLocale() == 'ar' ? 'المجال' : 'Domain' }}</th>
                            <th class="px-10 py-6 text-center">{{ app()->getLocale() == 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th class="px-10 py-6 text-center">{{ app()->getLocale() == 'ar' ? 'التوافق' : 'Consensus' }}</th>
                            <th class="px-10 py-6 text-center">{{ app()->getLocale() == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-white transition-all duration-300">
                                <td class="px-10 py-6 text-xs font-black text-slate-300">#{{ $task->id }}</td>
                                <td class="px-10 py-6">
                                    <div class="max-w-2xl">
                                        <p class="text-sm font-bold text-slate-700 mb-1 leading-relaxed">{{ Str::limit($task->original_data, 120) }}</p>
                                        <div class="flex items-center gap-3">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $task->created_at->format('Y-m-d') }}</span>
                                            <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $task->created_at->format('H:i') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <span class="status-pill 
                                        @if($task->task_domain === 'law') bg-indigo-50 text-indigo-700 border border-indigo-100
                                        @elseif($task->task_domain === 'medicine') bg-rose-50 text-rose-700 border border-rose-100
                                        @else bg-slate-100 text-slate-600 border border-slate-200
                                        @endif">
                                        {{ $task->task_domain === 'law' ? (app()->getLocale() == 'ar' ? 'قانوني' : 'Legal') : ($task->task_domain ?? 'General') }}
                                    </span>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest
                                        {{ $task->status->value === 'completed' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 border border-amber-500/20' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $task->status->value === 'completed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $task->status->value }}
                                    </span>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <span class="status-pill
                                        @if($task->consensus_status === 'consensus_reached') bg-emerald-50 text-emerald-700 border border-emerald-100
                                        @elseif($task->consensus_status === 'conflict') bg-rose-50 text-rose-700 border border-rose-100
                                        @else bg-slate-100 text-slate-600 border border-slate-200
                                        @endif">
                                        {{ str_replace('_', ' ', is_string($task->consensus_status) ? $task->consensus_status : $task->consensus_status->value) }}
                                    </span>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center justify-center gap-3">
                                        @if($task->status->value === 'pending')
                                            <button onclick="openEditModal({{ $task->id }}, '{{ addslashes($task->original_data) }}')" class="w-10 h-10 flex items-center justify-center glass-card border border-slate-200 text-indigo-500 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                                <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 000-1.41l-2.34-2.34a.996.996 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                            </button>
                                        @endif
                                        <form action="{{ route('client.governance.task.duplicate', $task->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-10 h-10 flex items-center justify-center glass-card border border-slate-200 text-slate-400 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                                <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </form>
                                        @if($task->status->value === 'pending')
                                            <form action="{{ route('client.governance.task.delete', $task->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-10 h-10 flex items-center justify-center glass-card border border-slate-200 text-rose-500 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                    <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-10 py-20 text-center text-slate-300 font-bold uppercase tracking-widest text-sm">Registry Empty</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
                <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-100">
                    {{ $tasks->links() }}
                </div>
            @endif
        </section>

        <!-- Fraud Intelligence Log -->
        <section class="space-y-6 mb-12">
            <h3 class="text-xl font-black text-slate-800 flex items-center gap-4">
                <i class="fa-solid fa-user-shield text-rose-500"></i>
                {{ app()->getLocale() == 'ar' ? 'سجل كشف الاحتيال الذكي' : 'Fraud Intelligence Log' }}
            </h3>
            <div class="glass-card overflow-hidden border-0">
                @if($fraudLogs->count() > 0)
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-right text-sm border-collapse">
                            <thead class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 bg-slate-50/50 border-b border-slate-100">
                                <tr>
                                    <th class="px-10 py-6">{{ app()->getLocale() == 'ar' ? 'الوقت' : 'Event Time' }}</th>
                                    <th class="px-10 py-6">{{ app()->getLocale() == 'ar' ? 'الخبير' : 'Expert' }}</th>
                                    <th class="px-10 py-6 text-center">{{ app()->getLocale() == 'ar' ? 'الحدث' : 'Event' }}</th>
                                    <th class="px-10 py-6">{{ app()->getLocale() == 'ar' ? 'الوصف' : 'Description' }}</th>
                                    <th class="px-10 py-6 text-center">{{ app()->getLocale() == 'ar' ? 'تغيير النقاط' : 'Impact' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($fraudLogs->take(10) as $log)
                                    <tr class="hover:bg-white transition-all {{ $log['event'] == 'expert_banned' ? 'bg-rose-50/30' : '' }}">
                                        <td class="px-10 py-5 text-slate-400 text-xs font-bold">{{ $log['timestamp']->format('H:i:s') }}</td>
                                        <td class="px-10 py-5 font-black text-slate-700">
                                            {{ $log['expert_name'] }}
                                            <span class="text-[9px] text-slate-300 ml-2">#{{ $log['expert_id'] }}</span>
                                        </td>
                                        <td class="px-10 py-5 text-center">
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest
                                                @if($log['event'] == 'expert_banned') bg-rose-600 text-white shadow-lg shadow-rose-200
                                                @elseif($log['event'] == 'gold_task_failed') bg-amber-500 text-white
                                                @else bg-slate-800 text-white
                                                @endif">
                                                {{ str_replace('_', ' ', $log['event']) }}
                                            </span>
                                        </td>
                                        <td class="px-10 py-5 text-slate-500 font-medium italic text-xs">{{ $log['description'] }}</td>
                                        <td class="px-10 py-5 text-center font-black {{ $log['trust_score_change'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $log['trust_score_change'] > 0 ? '+' : '' }}{{ $log['trust_score_change'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-20 text-center">
                        <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-100 text-emerald-500">
                            <svg class="w-10 h-10 fill-currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                        </div>
                        <p class="text-slate-400 font-black uppercase tracking-widest text-sm italic">No security alerts in the last 24h</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Recent Conflicts Resolution -->
        <section class="space-y-6 mb-12">
            <h3 class="text-xl font-black text-slate-800 flex items-center gap-4">
                <i class="fa-solid fa-code-merge text-purple-500"></i>
                {{ app()->getLocale() == 'ar' ? 'النزاعات الأخيرة' : 'Recent Conflicts' }}
            </h3>
            
            @if($conflicts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($conflicts->take(6) as $conflict)
                        <div class="glass-card overflow-hidden border-0 shadow-xl group">
                            <div class="bg-gradient-to-r from-purple-500/10 to-indigo-600/10 px-8 py-5 border-b border-slate-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                            <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M11.19 1.36l-7 3.11C3.47 4.79 3 5.51 3 6.3V11c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V6.3c0-.79-.47-1.51-1.19-1.83l-7-3.11c-.51-.23-1.11-.23-1.62 0z"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-800 uppercase">TASK #{{ $conflict['task_id'] }}</h4>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $conflict['task_type'] }}</p>
                                        </div>
                                    </div>
                                    <span class="status-pill {{ $conflict['resolved'] ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white animate-pulse' }}">
                                        {{ $conflict['resolved'] ? 'Resolved' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach($conflict['expert_answers'] as $index => $answer)
                                        <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="conflict-avatar bg-indigo-50 text-indigo-600">E{{ $index + 1 }}</div>
                                                <span class="text-[9px] font-black text-slate-400 uppercase">Expert #{{ $answer['expert_id'] }}</span>
                                            </div>
                                            <div class="bg-white rounded-xl p-3 text-[10px] font-bold text-slate-600 h-24 overflow-y-auto custom-scrollbar shadow-inner leading-relaxed">
                                                {{ is_array($answer['answer']) ? json_encode($answer['answer'], JSON_UNESCAPED_UNICODE) : $answer['answer'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="glass-card py-20 text-center">
                    <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-purple-100 text-purple-500">
                        <svg class="w-10 h-10 fill-currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-6h2v6zm0-8h-2V7h2v2zm4 8h-2V7h2v10z"/></svg>
                    </div>
                    <p class="text-slate-400 font-black uppercase tracking-widest text-sm italic">{{ app()->getLocale() == 'ar' ? 'لا توجد نزاعات حالياً' : 'System is in complete alignment' }}</p>
                </div>
            @endif
        </section>

    </div>
</div>

<!-- Premium Edit Modal -->
<div id="editTaskModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
        <div class="relative glass-card rounded-[3rem] w-full max-w-xl overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
            <form id="editTaskForm" method="POST">
                @csrf @method('PUT')
                <div class="p-12">
                    <div class="flex items-center gap-6 mb-10">
                        <div class="w-16 h-16 bg-indigo-100 rounded-[1.5rem] flex items-center justify-center text-indigo-600">
                            <svg class="w-8 h-8 fill-currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 000-1.41l-2.34-2.34a.996.996 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-800">
                                {{ app()->getLocale() == 'ar' ? 'تعديل بيانات المهمة' : 'Refine Task Data' }}
                            </h3>
                            <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Update context registry</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Task Content</label>
                            <textarea name="original_data" id="taskOriginalData" rows="8" 
                                class="w-full px-6 py-5 bg-white border border-slate-200 rounded-[2rem] text-sm text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium leading-relaxed" 
                                placeholder="Enter updated content..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-12 py-8 bg-slate-50/80 border-t border-slate-100 flex items-center gap-4">
                    <button type="submit" class="flex-1 btn-premium justify-center py-4">
                        <svg class="w-4 h-4 fill-currentColor mr-2" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        {{ app()->getLocale() == 'ar' ? 'حفظ التعديلات' : 'Authorize Update' }}
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-10 py-4 glass-card rounded-2xl font-black uppercase text-xs tracking-widest text-slate-500 hover:bg-white transition-all">
                        {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Abort' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('upload-form').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('csv_file');
        if (!fileInput.files || fileInput.files.length === 0) return;

        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        
        const btn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const progressPercent = document.getElementById('progress-percent');

        // UI Updates
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btnText.innerText = '{{ app()->getLocale() == "ar" ? "جاري البدء..." : "Starting..." }}';
        progressContainer.classList.remove('hidden');

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressPercent.innerText = percent + '%';
                btnText.innerText = '{{ app()->getLocale() == "ar" ? "جاري الرفع... " : "Uploading... " }}' + percent + '%';
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200 || xhr.status === 201 || xhr.status === 302) {
                    // Success - Redirect manually or reload
                    window.location.href = '{{ route("client.governance.dashboard") }}';
                } else {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnText.innerText = '{{ app()->getLocale() == "ar" ? "فشل الرفع - حاول ثانية" : "Upload Failed - Retry" }}';
                    alert('Error: ' + xhr.statusText);
                }
            }
        };

        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });

    function updateFileName(input) {
        const display = document.getElementById('file-name-display');
        if (input.files && input.files[0]) {
            display.innerText = input.files[0].name;
            display.classList.add('text-indigo-600');
        }
    }

    function openEditModal(taskId, content) {
        const modal = document.getElementById('editTaskModal');
        document.getElementById('editTaskForm').action = `/client/governance/tasks/${taskId}`;
        document.getElementById('taskOriginalData').value = content;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editTaskModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>

@endsection