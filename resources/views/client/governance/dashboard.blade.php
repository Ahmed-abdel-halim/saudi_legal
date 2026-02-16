@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-800 min-h-screen pb-20">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.projects') }}" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ app()->getLocale() == 'ar' ? '19 12H5m7 7l-7-7 7-7' : '5 12h14M12 5l7 7-7 7' }}"></path></svg>
                </a>
                <span class="font-bold text-xl text-slate-800">{{ app()->getLocale() == 'ar' ? 'لوحة الحوكمة والجودة' : 'Quality & Governance Dashboard' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-slate-500">{{ app()->getLocale() == 'ar' ? 'آخر تحديث:' : 'Last Updated:' }}</span>
                <span class="text-sm font-bold text-slate-700">{{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Task Upload & Tracking Section -->
        <div class="space-y-8 mb-8">
            <!-- New Task Upload Section -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200">
                <div class="max-w-xl">
                     <h2 class="text-xl font-bold text-slate-800 mb-2">{{ app()->getLocale() == 'ar' ? 'رفع مهام جديدة' : 'Upload New Tasks' }}</h2>
                     <p class="text-slate-500 mb-6 text-sm">
                        {{ app()->getLocale() == 'ar' 
                            ? 'قم برفع ملف CSV يحتوي على المهام الجديدة لتوزيعها على الخبراء.' 
                            : 'Upload a CSV file containing new tasks to check quality, detect manipulation, and measure expert consensus.' }}
                     </p>
                     
                     <form action="{{ route('client.governance.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1 sr-only">CSV File</label>
                            <input type="file" name="csv_file" accept=".csv,.txt" class="block w-full text-sm text-slate-500
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100
                                border border-slate-300 rounded-lg cursor-pointer
                            "/>
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            {{ app()->getLocale() == 'ar' ? 'رفع وتوزيع المهام' : 'Upload & Assign' }}
                        </button>
                    </form>
                    <div class="mt-4 text-xs text-slate-400">
                        {{ app()->getLocale() == 'ar' ? 'الأعمدة المطلوبة:' : 'Required columns:' }} {{ __('dashboard.original_data') ?? 'original_data' }} (Question/Text)
                    </div>
                </div>
            </div>

            <!-- Task List / Tracking Table -->
             <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'متابعة المهام' : 'Task Tracking' }}</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-500 bg-white px-2 py-1 rounded border border-slate-200">{{ $tasks->total() }} {{ app()->getLocale() == 'ar' ? 'مهمة' : 'Tasks' }}</span>
                        @if($tasks->total() > 0)
                            <a href="{{ route('client.governance.task.delete-all') }}" 
                               onclick="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد أنك تريد حذف جميع الأسئلة والمهام؟ لا يمكن التراجع عن هذا الإجراء.' : 'Are you sure you want to delete ALL questions and tasks? This cannot be undone.' }}')"
                               class="text-xs bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg font-bold border border-red-200 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                {{ app()->getLocale() == 'ar' ? 'حذف الجميع' : 'Delete All' }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 font-semibold">{{ __('dashboard.id') ?? 'ID' }}</th>
                                <th class="px-6 py-3 font-semibold">{{ __('dashboard.original_data') ?? 'Full texts' }}</th>
                                <th class="px-6 py-3 font-semibold">{{ app()->getLocale() == 'ar' ? 'المجال' : 'Domain' }}</th>
                                <th class="px-6 py-3 font-semibold">{{ __('dashboard.status') ?? 'Status' }}</th>
                                <th class="px-6 py-3 font-semibold">{{ __('dashboard.consensus') ?? 'Consensus' }}</th>
                               <th class="px-6 py-3 font-semibold">{{ __('dashboard.created_at') ?? 'Created At' }}</th>
                                <th class="px-6 py-3 font-semibold text-center">{{ __('dashboard.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($tasks as $task)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 text-sm text-slate-600">#{{ $task->id }}</td>
                                    <td class="px-6 py-3 text-sm text-slate-800 font-medium max-w-md truncate">
                                        {{ Str::limit($task->original_data, 60) }}
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($task->task_domain)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                @if($task->task_domain === 'medicine') bg-red-50 text-red-700
                                                @elseif($task->task_domain === 'law') bg-blue-50 text-blue-700
                                                @elseif($task->task_domain === 'engineering') bg-purple-50 text-purple-700
                                                @elseif($task->task_domain === 'business') bg-green-50 text-green-700
                                                @elseif($task->task_domain === 'education') bg-yellow-50 text-yellow-700
                                                @else bg-slate-50 text-slate-600
                                                @endif">
                                                @if(app()->getLocale() == 'ar')
                                                    @if($task->task_domain === 'medicine') طبي
                                                    @elseif($task->task_domain === 'law') قانوني
                                                    @elseif($task->task_domain === 'engineering') هندسي
                                                    @elseif($task->task_domain === 'business') تجاري
                                                    @elseif($task->task_domain === 'education') تعليمي
                                                    @else {{ $task->task_domain }}
                                                    @endif
                                                @else
                                                    {{ ucfirst($task->task_domain) }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">{{ app()->getLocale() == 'ar' ? 'عام' : 'General' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $task->status === 'completed' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </td>
                                     <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($task->consensus_status === 'consensus_reached') bg-green-50 text-green-700
                                            @elseif($task->consensus_status === 'conflict') bg-red-50 text-red-700
                                            @elseif($task->consensus_status === 'in_progress') bg-blue-50 text-blue-700
                                            @else bg-slate-50 text-slate-600
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $task->consensus_status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-slate-500">
                                        {{ $task->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($task->status === 'pending')
                                                <button onclick="openEditModal({{ $task->id }}, '{{ addslashes($task->original_data) }}')" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-all" title="{{ __('dashboard.edit') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                            @endif
                                            
                                            <form action="{{ route('client.governance.task.duplicate', $task->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من نسخ هذه المهمة؟' : 'Are you sure you want to duplicate this task?' }}')">
                                                @csrf
                                                <button type="submit" class="p-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-full transition-all" title="{{ __('dashboard.copy') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                </button>
                                            </form>

                                            @if($task->status === 'pending')
                                                <form action="{{ route('client.governance.task.delete', $task->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() == 'ar' ? 'هل أنت متأكد من حذف هذه المهمة؟' : 'Are you sure you want to delete this task?' }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-all" title="{{ __('dashboard.delete') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                        <p class="text-sm">{{ __('dashboard.no_tasks') ?? 'No tasks found.' }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($tasks->hasPages())
                    <div class="px-6 py-3 border-t border-slate-100">
                        {{ $tasks->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Live Expert Tracking -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'متابعة الخبراء المباشرة' : 'Live Expert Tracking' }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Experts -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <div class="text-slate-500 text-sm font-medium mb-1">{{ app()->getLocale() == 'ar' ? 'إجمالي الخبراء' : 'Total Experts' }}</div>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($liveTracking['total_experts']) }}</div>
                </div>

                <!-- Active Experts -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                     <div class="text-slate-500 text-sm font-medium mb-1">{{ app()->getLocale() == 'ar' ? 'نشط الآن' : 'Active Now' }}</div>
                    <div class="text-3xl font-bold text-green-600 flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        {{ number_format($liveTracking['active_experts']) }}
                    </div>
                </div>

                <!-- Avg Trust Score -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <div class="text-slate-500 text-sm font-medium mb-1">{{ app()->getLocale() == 'ar' ? 'متوسط الثقة' : 'Avg. Trust Score' }}</div>
                    <div class="text-3xl font-bold text-purple-700">{{ $liveTracking['avg_trust_score'] }}%</div>
                </div>

                <!-- Banned Experts -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <div class="text-slate-500 text-sm font-medium mb-1">{{ app()->getLocale() == 'ar' ? 'محظورين' : 'Banned Experts' }}</div>
                    <div class="text-3xl font-bold text-red-600">{{ number_format($liveTracking['banned_experts']) }}</div>
                </div>
            </div>
        </div>

        <!-- Accuracy Metrics -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'مقاييس الدقة' : 'Accuracy Metrics' }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Tasks -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-slate-500 text-sm font-medium">{{ app()->getLocale() == 'ar' ? 'إجمالي المهام' : 'Total Tasks' }}</span>
                        <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($metrics['total_tasks']) }}</div>
                </div>

                <!-- Perfect Consensus -->
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl p-6 shadow-sm border border-emerald-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-emerald-700 text-sm font-medium">{{ app()->getLocale() == 'ar' ? 'إجماع كامل' : 'Perfect Consensus' }}</span>
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-emerald-700">{{ $metrics['perfect_consensus_pct'] }}%</div>
                    <div class="text-xs text-emerald-600 mt-1">{{ app()->getLocale() == 'ar' ? 'اتفاق 3 خبراء' : 'All 3 experts agreed' }}</div>
                </div>

                <!-- Majority Vote -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 shadow-sm border border-blue-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-blue-700 text-sm font-medium">{{ app()->getLocale() == 'ar' ? 'تصويت الأغلبية' : 'Majority Vote' }}</span>
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-blue-700">{{ $metrics['majority_vote_pct'] }}%</div>
                    <div class="text-xs text-blue-600 mt-1">{{ app()->getLocale() == 'ar' ? 'اتفاق 2 من 3' : '2 out of 3 agreed' }}</div>
                </div>

                <!-- Conflicts -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 shadow-sm border border-amber-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-amber-700 text-sm font-medium">{{ app()->getLocale() == 'ar' ? 'نزاعات' : 'Conflicts' }}</span>
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-amber-700">{{ $metrics['conflict_pct'] }}%</div>
                    <div class="text-xs text-amber-600 mt-1">{{ app()->getLocale() == 'ar' ? 'يتطلب مراجعة' : 'Requires review' }}</div>
                </div>
            </div>
        </div>

        <!-- Fraud Detection Log -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'سجل كشف الاحتيال' : 'Fraud Detection Log' }}
            </h2>

            @if(count($fraudLogs) > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 font-bold">{{ app()->getLocale() == 'ar' ? 'الوقت' : 'Timestamp' }}</th>
                                    <th class="px-6 py-4 font-bold">{{ app()->getLocale() == 'ar' ? 'الخبير' : 'Expert' }}</th>
                                    <th class="px-6 py-4 font-bold">{{ app()->getLocale() == 'ar' ? 'الحدث' : 'Event' }}</th>
                                    <th class="px-6 py-4 font-bold">{{ app()->getLocale() == 'ar' ? 'التفاصيل' : 'Description' }}</th>
                                    <th class="px-6 py-4 font-bold text-center">{{ app()->getLocale() == 'ar' ? 'تغيير النقاط' : 'Trust Score Change' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($fraudLogs as $log)
                                    <tr class="hover:bg-slate-50 transition {{ $log['event'] == 'expert_banned' ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 text-slate-600 text-xs">
                                            {{ $log['timestamp']->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-800">
                                            {{ $log['expert_name'] }}
                                            <span class="text-xs text-slate-400">(#{{ $log['expert_id'] }})</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log['event'] == 'gold_task_failed')
                                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                    {{ app()->getLocale() == 'ar' ? 'فشل في السؤال الذهبي' : 'Gold Task Failed' }}
                                                </span>
                                            @elseif($log['event'] == 'trust_score_warning')
                                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                                    {{ app()->getLocale() == 'ar' ? 'تحذير النقاط' : 'Trust Warning' }}
                                                </span>
                                            @elseif($log['event'] == 'expert_banned')
                                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-600 text-white">
                                                    {{ app()->getLocale() == 'ar' ? 'حظر تلقائي' : 'Auto-Banned' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">
                                            {{ $log['description'] }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($log['trust_score_change'])
                                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $log['trust_score_change'] < 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $log['trust_score_change'] > 0 ? '+' : '' }}{{ $log['trust_score_change'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-slate-500">{{ app()->getLocale() == 'ar' ? 'لا توجد أحداث احتيال مسجلة' : 'No fraud events detected' }}</p>
                </div>
            @endif
        </div>

        <!-- Recent Conflicts -->
        <div>
            <h2 class="text-2xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                {{ app()->getLocale() == 'ar' ? 'النزاعات الأخيرة' : 'Recent Conflicts' }}
            </h2>

            @if(count($conflicts) > 0)
                <div class="space-y-4">
                    @foreach($conflicts as $conflict)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-purple-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-slate-800">{{ app()->getLocale() == 'ar' ? 'مهمة' : 'Task' }} #{{ $conflict['task_id'] }} - {{ $conflict['task_type'] }}</h4>
                                        <p class="text-xs text-slate-500 mt-1">{{ $conflict['created_at']->format('Y-m-d H:i') }}</p>
                                    </div>
                                    @if($conflict['resolved'])
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                            {{ app()->getLocale() == 'ar' ? 'تم الحل' : 'Resolved' }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                            {{ app()->getLocale() == 'ar' ? 'قيد الانتظار' : 'Pending' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    @foreach($conflict['expert_answers'] as $index => $answer)
                                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-bold text-indigo-700">{{ $index + 1 }}</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-700">{{ app()->getLocale() == 'ar' ? 'خبير' : 'Expert' }} #{{ $answer['expert_id'] }}</span>
                                            </div>
                                            <div class="bg-white rounded-lg p-3 text-xs font-mono text-slate-600 max-h-32 overflow-y-auto">
                                                {{ json_encode($answer['answer'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500">
                                                {{ app()->getLocale() == 'ar' ? 'الثقة:' : 'Confidence:' }} {{ $answer['confidence'] ?? 'N/A' }}%
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($conflict['conflict_notes'])
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                        <p class="text-sm text-amber-800"><strong>{{ app()->getLocale() == 'ar' ? 'ملاحظات:' : 'Notes:' }}</strong> {{ $conflict['conflict_notes'] }}</p>
                                    </div>
                                @endif

                                @if($conflict['resolved'])
                                    <div class="mt-4 text-sm text-slate-600">
                                        {{ app()->getLocale() == 'ar' ? 'تم الحل بواسطة:' : 'Resolved by:' }} <strong>{{ $conflict['resolved_by'] }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-slate-500">{{ app()->getLocale() == 'ar' ? 'لا توجد نزاعات حالياً' : 'No conflicts at this time' }}</p>
                </div>
            @endif
        </div>

    </div>

    <!-- Edit Task Modal -->
    <div id="editTaskModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editTaskForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-slate-900 mb-4" id="modal-title">
                            {{ app()->getLocale() == 'ar' ? 'تعديل المهمة' : 'Edit Task' }}
                        </h3>
                        <div class="mb-4">
                            <label for="taskOriginalData" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ app()->getLocale() == 'ar' ? 'محتوى المهمة' : 'Task Content' }}
                            </label>
                            <textarea name="original_data" id="taskOriginalData" rows="6" class="w-full px-3 py-2 text-slate-700 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required></textarea>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ app()->getLocale() == 'ar' ? 'حفظ التعديلات' : 'Save Changes' }}
                        </button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ app()->getLocale() == 'ar' ? 'إلغاء' : 'Cancel' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(taskId, content) {
            const modal = document.getElementById('editTaskModal');
            const form = document.getElementById('editTaskForm');
            const textarea = document.getElementById('taskOriginalData');
            
            // Set form action
            form.action = `/client/governance/tasks/${taskId}`;
            
            // Set content (unescape characters if needed)
            textarea.value = content;
            
            // Show modal
            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editTaskModal').classList.add('hidden');
        }
    </script>

</div>
@endsection
