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

        <!-- CSV Data Analysis Section -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 mb-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">{{ app()->getLocale() == 'ar' ? 'رفع ملف البيانات للتحليل' : 'Upload Data for Analysis' }}</h2>
            <p class="text-slate-500 mb-6">{{ app()->getLocale() == 'ar' ? 'قم برفع ملف CSV يحتوي على بياناتك لتحليل الجودة، اكتشاف التلاعب، وقياس مستوى الإجماع بين الخبراء.' : 'Upload a CSV file containing your data to analyze quality, detect fraud, and measure consensus levels.' }}</p>

            <form action="{{ route('client.governance.analyze') }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="w-full md:w-2/3">
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ app()->getLocale() == 'ar' ? 'ملف CSV' : 'CSV File' }}</label>
                    <input type="file" name="csv_file" accept=".csv" class="block w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100
                    " required>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() == 'ar' ? 'الأعمدة المطلوبة: task_id, expert_id, answer, is_gold_standard, gold_answer, submitted_at' : 'Required columns: task_id, expert_id, answer, is_gold_standard, gold_answer, submitted_at' }}</p>
                </div>
                <div class="w-full md:w-1/3">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        {{ app()->getLocale() == 'ar' ? 'رفع وتحليل الملف' : 'Upload & Analyze' }}
                    </button>
                </div>
            </form>

            <!-- Analysis Results -->
            @if(session('analysis_results'))
                <div class="mt-8 pt-8 border-t border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ app()->getLocale() == 'ar' ? 'نتائج التحليل' : 'Analysis Results' }}
                    </h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-indigo-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-indigo-700">{{ number_format(session('analysis_results')['total_rows']) }}</div>
                            <div class="text-xs text-indigo-600 font-medium">{{ app()->getLocale() == 'ar' ? 'صفوف تمت معالجتها' : 'Rows Processed' }}</div>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-indigo-700">{{ session('analysis_results')['accuracy_rate'] }}%</div>
                            <div class="text-xs text-indigo-600 font-medium">{{ app()->getLocale() == 'ar' ? 'معدل الدقة' : 'Accuracy Rate' }}</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-700">{{ session('analysis_results')['gold_failures'] }}</div>
                            <div class="text-xs text-red-600 font-medium">{{ app()->getLocale() == 'ar' ? 'فشل سؤال ذهبي' : 'Gold Failures' }}</div>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-amber-700">{{ session('analysis_results')['conflicts'] }}</div>
                            <div class="text-xs text-amber-600 font-medium">{{ app()->getLocale() == 'ar' ? 'نزاعات' : 'Conflicts' }}</div>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-emerald-700">{{ session('analysis_results')['perfect_consensus'] }}</div>
                            <div class="text-xs text-emerald-600 font-medium">{{ app()->getLocale() == 'ar' ? 'إجماع كامل' : 'Perfect Consensus' }}</div>
                        </div>
                    </div>

                    @if(!empty(session('analysis_results')['flagged_experts']))
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-bold text-red-800 text-sm mb-2">{{ app()->getLocale() == 'ar' ? 'خبراء تم وضع علامة عليهم (انخفاض الثقة)' : 'Flagged Experts (Low Trust)' }}</h4>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach(session('analysis_results')['flagged_experts'] as $expertId => $score)
                                    <li>{{ app()->getLocale() == 'ar' ? 'خبير' : 'Expert' }} #{{ $expertId }} (Score: {{ $score }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
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
                                                {{ app()->getLocale() == 'ar' ? 'الثقة:' : 'Confidence:' }} {{ $answer['confidence'] }}%
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

</div>
@endsection
