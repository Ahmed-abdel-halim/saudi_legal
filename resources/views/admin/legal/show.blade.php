@extends('layouts.admin')

@section('title', 'تفاصيل السجل القانوني #' . $item->id)

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.legal.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-300 transition shadow-sm">
            <i class="fa-solid fa-arrow-right rtl:rotate-0"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">تفاصيل المراجعة القانونية</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">ID: #{{ $item->id }} • {{ $item->record->sub_domain ?? 'قانون عام' }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        @php
            $statusClasses = [
                'Pending'    => 'bg-amber-50 text-amber-700 border-amber-100',
                'Processing' => 'bg-blue-50 text-blue-700 border-blue-100',
                'Approved'   => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'Modified'   => 'bg-indigo-50 text-indigo-700 border-indigo-100',
            ];
            $statusClass = $statusClasses[$item->review_status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
        @endphp
        <span class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-wider {{ $statusClass }}">
            {{ $item->review_status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- Left Column: Question & Comparison --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Question Card --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">السؤال المستخرج من القضية</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 leading-relaxed">{{ $item->question }}</h2>
            </div>
        </div>

        {{-- Comparison Card --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-slate-100">
                
                {{-- AI Answer --}}
                <div class="p-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fa-solid fa-robot text-slate-300"></i> إجابة الذكاء الاصطناعي
                        </span>
                        <span class="text-[10px] font-bold text-slate-300 bg-slate-50 px-2 py-0.5 rounded">ORIGINAL</span>
                    </div>
                    <div class="text-sm leading-loose text-slate-500 font-medium whitespace-pre-wrap {{ $item->review_status === 'Modified' ? 'line-through opacity-40' : '' }}">
                        {{ $item->generated_answer }}
                    </div>
                </div>

                {{-- Expert Correction --}}
                <div class="p-8 space-y-4 bg-emerald-50/20">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                            <i class="fa-solid fa-user-tie"></i> الإجابة المعتمدة
                        </span>
                        @if($item->review_status === 'Modified')
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">MODIFIED</span>
                        @endif
                    </div>
                    <div class="text-sm leading-loose text-slate-800 font-bold whitespace-pre-wrap">
                        @if($item->review_status === 'Modified')
                            {{ $item->corrected_answer }}
                        @elseif($item->review_status === 'Approved')
                            {{ $item->generated_answer }}
                        @else
                            <div class="flex flex-col items-center justify-center py-10 text-slate-300">
                                <i class="fa-solid fa-hourglass-start text-2xl mb-2"></i>
                                <p class="text-xs">بانتظار تدقيق الخبير...</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Full Judicial Text --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-8">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 text-sm">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <span class="text-xs font-black text-slate-800">نص الحكم القضائي بالكامل</span>
                </div>
                <div class="bg-slate-50 rounded-2xl p-6 text-sm leading-loose text-slate-600 max-h-[500px] overflow-y-auto custom-scrollbar whitespace-pre-wrap font-medium">
                    {{ $item->record->full_text }}
                </div>
            </div>
        </div>

    </div>

    {{-- Right Column: Metadata & Citations --}}
    <div class="space-y-6">
        
        {{-- Reviewer Info --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">معلومات التدقيق</h4>
            
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-2xl">
                    <div class="w-12 h-12 rounded-full bg-white border border-slate-200 flex items-center justify-center text-lg font-black text-slate-800 shadow-sm overflow-hidden">
                        @if($item->reviewer && $item->reviewer->avatar_path)
                            <img src="{{ asset('uploads/' . $item->reviewer->avatar_path) }}" class="w-full h-full object-cover">
                        @else
                            {{ mb_substr($item->reviewer->name ?? '?', 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400">المحامي المراجع</p>
                        <h6 class="font-bold text-slate-800">{{ $item->reviewer->name ?? 'غير محدد' }}</h6>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">الوقت</p>
                        <p class="text-xs font-bold text-slate-700">{{ $item->reviewed_at ? $item->reviewed_at->format('h:i A') : '--:--' }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">التاريخ</p>
                        <p class="text-xs font-bold text-slate-700">{{ $item->reviewed_at ? $item->reviewed_at->format('Y/m/d') : '----/--/--' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Citations Card --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">المواد القانونية</h4>
                <span class="bg-emerald-100 text-emerald-600 text-[10px] font-black px-2 py-0.5 rounded-full">{{ $item->record->citations->count() }}</span>
            </div>

            <div class="space-y-3">
                @forelse($item->record->citations as $citation)
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-emerald-200 transition group">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span class="text-xs font-black text-slate-800">{{ $citation->system_name }}</span>
                        </div>
                        @if($citation->article_number)
                            <p class="text-[11px] font-bold text-emerald-600 mb-2">المادة رقم ({{ $citation->article_number }})</p>
                        @endif
                        <p class="text-[11px] leading-relaxed text-slate-500 font-medium">
                            {{ Str::limit($citation->citation_text, 100) }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-300 italic text-xs">
                        لا توجد استشهادات مربوطة
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

@endsection
