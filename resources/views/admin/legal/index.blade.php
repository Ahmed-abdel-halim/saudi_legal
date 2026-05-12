@extends('layouts.admin')

@section('title', 'إدارة السجلات القانونية')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">الإدارة القانونية</h1>
        <p class="text-slate-500 mt-1">مراقبة وتدقيق الـ 5000 سجل قانوني والتحقق من جودة إجابات الذكاء الاصطناعي.</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-slate-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">إجمالي الأسئلة</span>
        <span class="text-3xl font-black text-slate-800 relative z-10">{{ number_format($stats['total']) }}</span>
    </div>
    
    <div class="bg-white border border-amber-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-amber-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider relative z-10">بانتظار التدقيق</span>
        <span class="text-3xl font-black text-amber-600 relative z-10">{{ number_format($stats['pending']) }}</span>
    </div>

    <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-blue-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-blue-500 uppercase tracking-wider relative z-10">قيد المعالجة</span>
        <span class="text-3xl font-black text-blue-600 relative z-10">{{ number_format($stats['processing']) }}</span>
    </div>

    <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-16 h-16 -mr-8 -mt-8 bg-emerald-50 rounded-full transition group-hover:scale-150 duration-500"></div>
        <span class="text-xs font-bold text-emerald-500 uppercase tracking-wider relative z-10">تم التدقيق</span>
        <span class="text-3xl font-black text-emerald-600 relative z-10">{{ number_format($stats['completed']) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-4 bg-slate-50/50">
        <form method="GET" action="{{ route('admin.legal.index') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[250px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="ابحث برقم المعرف أو نص السؤال..."
                    class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 pl-9 rtl:pr-9 rtl:pl-3 p-2.5 transition outline-none">
            </div>

            {{-- Status Filter --}}
            <select name="status" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2.5 transition min-w-[160px]">
                <option value="">كل الحالات</option>
                <option value="Pending"   {{ request('status') == 'Pending'    ? 'selected' : '' }}>بانتظار التدقيق</option>
                <option value="Processing"{{ request('status') == 'Processing' ? 'selected' : '' }}>قيد المعالجة</option>
                <option value="Approved"  {{ request('status') == 'Approved'   ? 'selected' : '' }}>مقبول (Approved)</option>
                <option value="Modified"  {{ request('status') == 'Modified'   ? 'selected' : '' }}>تم التعديل (Modified)</option>
            </select>

            {{-- Expert Filter --}}
            <select name="expert_id" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2.5 transition min-w-[160px]">
                <option value="">كل المحامين</option>
                @foreach($experts as $expert)
                    <option value="{{ $expert->id }}" {{ request('expert_id') == $expert->id ? 'selected' : '' }}>{{ $expert->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-slate-800 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-slate-900 transition shadow-sm">
                <i class="fa-solid fa-filter me-1"></i> فلترة النتائج
            </button>

            @if(request()->anyFilled(['search', 'status', 'expert_id']))
                <a href="{{ route('admin.legal.index') }}" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-lg text-sm font-bold border border-red-100 hover:bg-red-100 transition">
                    <i class="fa-solid fa-xmark me-1"></i> مسح
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left rtl:text-right text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">ID</th>
                    <th class="px-5 py-4 font-bold tracking-wider">السؤال المطروح</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-center">الحالة</th>
                    <th class="px-5 py-4 font-bold tracking-wider">المحامي المراجع</th>
                    <th class="px-5 py-4 font-bold tracking-wider">آخر تحديث</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                    <tr class="hover:bg-slate-50/60 transition group">
                        <td class="px-5 py-4">
                            <span class="text-xs font-mono font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded">#{{ $item->id }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col max-w-[400px]">
                                <span class="font-bold text-slate-800 truncate mb-0.5">{{ $item->question }}</span>
                                <span class="text-[11px] text-slate-400">{{ $item->record->sub_domain ?? 'قانون عام' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusClasses = [
                                    'Pending'    => 'bg-amber-50 text-amber-700 border-amber-100',
                                    'Processing' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'Approved'   => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'Modified'   => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                ];
                                $statusClass = $statusClasses[$item->review_status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $statusClass }}">
                                {{ $item->review_status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($item->reviewer)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                        {{ mb_substr($item->reviewer->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $item->reviewer->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500 font-medium">
                            @if($item->reviewed_at)
                                <div>{{ $item->reviewed_at->format('Y/m/d') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->reviewed_at->diffForHumans() }}</div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right rtl:text-left">
                            <a href="{{ route('admin.legal.show', $item->id) }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition shadow-sm">
                                تفاصيل <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-300">
                                <i class="fa-solid fa-file-invoice text-6xl mb-4 opacity-20"></i>
                                <p class="text-lg font-bold text-slate-400">لا توجد سجلات مطابقة للبحث</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between gap-4">
            <span class="text-xs text-slate-500 font-medium">
                عرض {{ $items->firstItem() }}–{{ $items->lastItem() }} من إجمالي {{ $items->total() }} سجل
            </span>
            {{ $items->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
