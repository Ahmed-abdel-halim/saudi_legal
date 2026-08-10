@extends('layouts.admin')

@section('title', 'إدارة اشتراكات الباقات')

@section('content')
<div class="space-y-6" dir="rtl">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-emerald-400"></i>
                جدول اشتراكات باقات الذكاء الاصطناعي
            </h1>
            <p class="text-slate-400 text-sm mt-1">عرض ومتابعة كافة عمليات اشتراك المستخدمين في باقات المساعد القانوني الذكي</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai_packages.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-sm font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-right"></i> العودة لإدارة الباقات
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-sidebar rounded-xl border border-slate-700/50 p-4">
        <form method="GET" action="{{ route('admin.ai_packages.subscriptions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-slate-400 text-xs font-semibold uppercase mb-1">حالة الاشتراك</label>
                <select name="status" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط ✅</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في الانتظار ⏳</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهي 🛑</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي ❌</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-xs font-semibold uppercase mb-1">الباقة</label>
                <select name="package_id" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500">
                    <option value="">جميع الباقات</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i> تصفية النتائج
                </button>
                @if(request()->hasAny(['status', 'package_id']))
                    <a href="{{ route('admin.ai_packages.subscriptions') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 p-2 rounded-lg text-sm border border-slate-700 transition" title="إلغاء التصفية">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Subscriptions Table --}}
    <div class="bg-sidebar rounded-2xl border border-slate-700/50 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm text-slate-300">
                <thead class="bg-slate-800/80 text-xs uppercase font-bold text-slate-400 border-b border-slate-700/80">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">المشترك</th>
                        <th class="px-6 py-4">الباقة</th>
                        <th class="px-6 py-4">المبلغ المدفوع</th>
                        <th class="px-6 py-4">حالة الاشتراك</th>
                        <th class="px-6 py-4">تاريخ البدء والانتهاء</th>
                        <th class="px-6 py-4">الاستعلامات المستهلكة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                #{{ $sub->id }}
                            </td>
                            <td class="px-6 py-4">
                                @if($sub->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">
                                            {{ mb_substr($sub->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white">{{ $sub->user->name }}</div>
                                            <div class="text-xs text-slate-400 font-mono">{{ $sub->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-500 italic">مستخدم محذوف</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($sub->package)
                                    <span class="font-bold text-white bg-slate-800 border border-slate-700 px-3 py-1 rounded-lg text-xs">
                                        {{ $sub->package->name }}
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-emerald-400">
                                {{ number_format($sub->amount_paid, 2) }} {{ $sub->currency ?? 'SAR' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match($sub->status) {
                                        'active'    => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
                                        'pending'   => 'bg-amber-500/10 border-amber-500/30 text-amber-400',
                                        'expired'   => 'bg-slate-500/10 border-slate-500/30 text-slate-400',
                                        'cancelled' => 'bg-red-500/10 border-red-500/30 text-red-400',
                                        default     => 'bg-slate-500/10 border-slate-500/30 text-slate-400',
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClasses }}">
                                    {{ $sub->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                <div>
                                    <span class="text-slate-500">بدأ:</span>
                                    <span class="font-mono text-slate-300">{{ $sub->starts_at ? $sub->starts_at->format('Y-m-d') : '—' }}</span>
                                </div>
                                <div class="mt-0.5">
                                    <span class="text-slate-500">ينتهي:</span>
                                    <span class="font-mono text-slate-300">{{ $sub->ends_at ? $sub->ends_at->format('Y-m-d') : 'مدى الحياة' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <span class="font-mono font-bold text-white">{{ $sub->queries_used }}</span>
                                <span class="text-slate-500"> / </span>
                                <span class="text-slate-400">
                                    @if($sub->package && $sub->package->is_unlimited)
                                        غير محدود
                                    @else
                                        {{ $sub->package->query_limit ?? '—' }}
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-inbox text-4xl mb-3 text-slate-600 block"></i>
                                لا توجد اشتراكات مطابقة للتصفية الحالية.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($subscriptions->hasPages())
            <div class="px-6 py-4 bg-slate-800/50 border-t border-slate-700/50">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
