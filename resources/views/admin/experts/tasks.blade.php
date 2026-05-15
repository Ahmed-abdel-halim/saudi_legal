@extends('layouts.admin')

@section('title', 'تفاصيل التدقيق | ' . $expert->name)

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-black text-slate-800">تفاصيل تدقيق الخبير: {{ $expert->name }}</h1>
        <p class="text-slate-500 mt-1">مراجعة جودة المهام التي تم إنجازها بواسطة هذا الخبير.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.experts.index') }}" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-lg font-bold text-sm hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left me-1"></i> العودة لقائمة الخبراء
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-emerald-100">
                <img src="{{ $expert->avatar_url }}" class="w-full h-full object-cover">
            </div>
            <div>
                <h3 class="font-black text-lg text-slate-800">{{ $expert->name }}</h3>
                <p class="text-sm text-slate-500">{{ $expert->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] font-black uppercase bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded border border-emerald-100">
                        {{ $expert->expert_domain ?? 'خبير' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 flex flex-col items-center justify-center text-center">
            <i class="fa-solid fa-scale-balanced text-blue-500 text-xl mb-2"></i>
            <span class="text-2xl font-black text-blue-700">{{ $legalTasks->total() }}</span>
            <span class="text-xs font-bold text-blue-600 uppercase">مهام قانونية</span>
        </div>
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex flex-col items-center justify-center text-center">
            <i class="fa-solid fa-clock text-indigo-500 text-xl mb-2"></i>
            <span class="text-2xl font-black text-indigo-700">{{ $expert->total_time_spent }}</span>
            <span class="text-xs font-bold text-indigo-600 uppercase">إجمالي الوقت</span>
        </div>
    </div>
</div>

{{-- Legal Tasks Section --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <h3 class="font-black text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-blue-500"></i> أرشيف المهام القانونية (Audited Cases)
        </h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-right whitespace-normal">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 font-bold w-1/4 text-right">السؤال</th>
                    <th class="px-6 py-4 font-bold w-1/3 text-right">التصحيح المعتمد</th>
                    <th class="px-6 py-4 font-bold text-center">الحالة</th>
                    <th class="px-6 py-4 font-bold text-center">الوقت المستغرق</th>
                    <th class="px-6 py-4 font-bold text-right">التاريخ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($legalTasks as $task)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800 line-clamp-3" title="{{ $task->question }}">
                            {{ $task->question }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="bg-emerald-50 text-emerald-800 p-3 rounded-lg border border-emerald-100 text-xs font-bold whitespace-pre-wrap">
                            {{ $task->correct_answer }}
                        </div>
                        @if($task->expert_comment)
                            <div class="mt-2 text-[11px] text-slate-400 italic">
                                <strong>ملاحظة:</strong> {{ $task->expert_comment }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($task->is_correct)
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md text-[10px] font-black">طابق الإجابة</span>
                        @else
                            <span class="bg-rose-100 text-rose-700 px-2 py-1 rounded-md text-[10px] font-black">قام بالتعديل</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold text-slate-500">{{ $task->time_spent ? floor($task->time_spent / 60) . ':' . str_pad($task->time_spent % 60, 2, '0', STR_PAD_LEFT) : '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs">
                        {{ $task->completed_at ? $task->completed_at->format('Y/m/d H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-slate-400 italic">لم يقم بتدقيق أي مهام قانونية بعد.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($legalTasks->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $legalTasks->appends(['ai_page' => $aiResponses->currentPage()])->links() }}
    </div>
    @endif
</div>

{{-- AI Responses Section --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <h3 class="font-black text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-robot text-amber-500"></i> مهام الذكاء الاصطناعي (Refinement)
        </h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-right whitespace-normal">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 font-bold w-1/4 text-right">المهمة الأصلية</th>
                    <th class="px-6 py-4 font-bold w-1/3 text-right">التصحيح المعتمد</th>
                    <th class="px-6 py-4 font-bold text-center">الإجراء</th>
                    <th class="px-6 py-4 font-bold text-center">الوقت المستغرق</th>
                    <th class="px-6 py-4 font-bold text-right">التاريخ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($aiResponses as $resp)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800 line-clamp-3">
                            {{ $resp->task->prompt ?? '—' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="bg-blue-50 text-blue-800 p-3 rounded-lg border border-blue-100 text-xs font-bold whitespace-pre-wrap">
                            {{ $resp->corrected_data }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-[10px] font-black uppercase">{{ $resp->action }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold text-slate-500">{{ $resp->time_spent ? floor($resp->time_spent / 60) . ':' . str_pad($resp->time_spent % 60, 2, '0', STR_PAD_LEFT) : '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs">
                        {{ $resp->created_at->format('Y/m/d H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-slate-400 italic">لم يقم بتدقيق أي مهام ذكاء اصطناعي بعد.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($aiResponses->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $aiResponses->appends(['legal_page' => $legalTasks->currentPage()])->links() }}
    </div>
    @endif
</div>
{{-- Legal QA Pairs Section --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <h3 class="font-black text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-check-double text-emerald-500"></i> مراجعات البيانات القانونية (QA Pairs)
        </h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-right whitespace-normal">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 font-bold w-1/4 text-right">السؤال</th>
                    <th class="px-6 py-4 font-bold w-1/3 text-right">الإجابة النهائية</th>
                    <th class="px-6 py-4 font-bold text-center">الحالة</th>
                    <th class="px-6 py-4 font-bold text-center">الوقت المستغرق</th>
                    <th class="px-6 py-4 font-bold text-right">التاريخ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($legalQaPairs as $qa)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800 line-clamp-3">
                            {{ $qa->question }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="bg-emerald-50 text-emerald-800 p-3 rounded-lg border border-emerald-100 text-xs font-bold whitespace-pre-wrap">
                            {{ $qa->final_answer }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-[10px] font-black uppercase">{{ $qa->review_status }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold text-slate-500">{{ $qa->time_spent ? floor($qa->time_spent / 60) . ':' . str_pad($qa->time_spent % 60, 2, '0', STR_PAD_LEFT) : '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs">
                        {{ $qa->reviewed_at->format('Y/m/d H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">لم يقم بمراجعة أي بيانات قانونية بعد.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($legalQaPairs->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $legalQaPairs->appends(['legal_page' => $legalTasks->currentPage(), 'ai_page' => $aiResponses->currentPage()])->links() }}
    </div>
    @endif
</div>
@endsection
