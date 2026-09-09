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
            {{-- زر تفعيل اشتراك جديد --}}
            <button onclick="openGrantModal()"
                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-white text-sm font-bold flex items-center gap-2 transition shadow-lg shadow-amber-500/20">
                <i class="fa-solid fa-bolt"></i> + تفعيل اشتراك لمستخدم
            </button>
            <a href="{{ route('admin.ai_packages.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-sm font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-right"></i> العودة لإدارة الباقات
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-3 text-sm font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

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
            <div>
                <label class="block text-slate-400 text-xs font-semibold uppercase mb-1">بحث بالاسم أو الإيميل</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i> تصفية
                </button>
                @if(request()->hasAny(['status', 'package_id', 'search']))
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
                        <th class="px-6 py-4">الاستعلامات</th>
                        <th class="px-6 py-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                #{{ $sub->id }}
                                @if($sub->granted_by)
                                    <div class="text-[10px] text-amber-400/70 mt-0.5">
                                        <i class="fa-solid fa-user-shield text-[9px]"></i> {{ $sub->granted_by }}
                                    </div>
                                @endif
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
                                {{-- Lifetime Badge --}}
                                @if($sub->isLifetime())
                                    <span class="block mt-1 text-[10px] font-bold text-amber-400">♾️ مدى الحياة</span>
                                @elseif($sub->is_unlimited)
                                    <span class="block mt-1 text-[10px] font-bold text-emerald-400">♾️ غير محدود</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($sub->amount_paid > 0)
                                    <span class="font-bold text-emerald-400">{{ number_format($sub->amount_paid, 2) }} {{ $sub->currency ?? 'SAR' }}</span>
                                @else
                                    <span class="text-amber-400 font-bold text-xs">🎁 مجاني (إدارة)</span>
                                @endif
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
                                @if($sub->notes)
                                    <div class="text-[10px] text-slate-500 mt-1 max-w-[140px] truncate" title="{{ $sub->notes }}">
                                        {{ $sub->notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                <div>
                                    <span class="text-slate-500">بدأ:</span>
                                    <span class="font-mono text-slate-300">{{ $sub->starts_at ? $sub->starts_at->format('Y-m-d') : '—' }}</span>
                                </div>
                                <div class="mt-0.5">
                                    <span class="text-slate-500">ينتهي:</span>
                                    <span class="font-mono {{ $sub->ends_at ? 'text-slate-300' : 'text-amber-400 font-bold' }}">
                                        {{ $sub->ends_at ? $sub->ends_at->format('Y-m-d') : '♾️ مدى الحياة' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <span class="font-mono font-bold text-white">{{ $sub->queries_used }}</span>
                                <span class="text-slate-500"> / </span>
                                <span class="text-slate-400">
                                    @if($sub->isEffectivelyUnlimited())
                                        ♾️ غير محدود
                                    @else
                                        {{ $sub->package->query_limit ?? '—' }}
                                    @endif
                                </span>
                            </td>
                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if($sub->status === 'active')
                                        {{-- Revoke --}}
                                        <form action="{{ route('admin.ai_packages.subscriptions.revoke', $sub->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                onclick="return confirm('إلغاء اشتراك {{ $sub->user->name ?? 'المستخدم' }}؟')"
                                                class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500/20 text-xs font-bold transition"
                                                title="إلغاء الاشتراك">
                                                <i class="fa-solid fa-ban"></i> إلغاء
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Delete --}}
                                    <form action="{{ route('admin.ai_packages.subscriptions.destroy', $sub->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('حذف سجل الاشتراك نهائياً؟')"
                                            class="px-2.5 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 text-xs font-bold transition"
                                            title="حذف السجل">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
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

{{-- ═══════════════════════════════════════════════
     Modal: تفعيل اشتراك لمستخدم يدوياً
     ═══════════════════════════════════════════════ --}}
<div id="grant-sub-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4"
     onclick="if(event.target===this)closeGrantModal()">

    <div class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg p-6 border border-slate-700">

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-bolt text-amber-400 text-lg"></i>
                </div>
                <div>
                    <h2 class="font-black text-white text-lg">تفعيل اشتراك يدوياً</h2>
                    <p class="text-slate-400 text-xs">منح مستخدم وصولاً مجانياً بدون دفع</p>
                </div>
            </div>
            <button onclick="closeGrantModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.ai_packages.subscriptions.grant') }}" class="space-y-4">
            @csrf

            {{-- اختيار المستخدم --}}
            <div>
                <label class="block text-slate-300 text-sm font-bold mb-1.5">
                    <i class="fa-solid fa-user text-sky-400 mr-1"></i> المستخدم
                </label>
                <select name="user_id" required
                    class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                    <option value="">اختر مستخدماً...</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                    @endforeach
                </select>
            </div>

            {{-- اختيار الباقة --}}
            <div>
                <label class="block text-slate-300 text-sm font-bold mb-1.5">
                    <i class="fa-solid fa-box-open text-amber-400 mr-1"></i> الباقة
                </label>
                <select name="ai_package_id" required
                    class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- المدة --}}
            <div>
                <label class="block text-slate-300 text-sm font-bold mb-2">
                    <i class="fa-solid fa-clock text-emerald-400 mr-1"></i> مدة الاشتراك
                </label>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    @foreach(['lifetime' => '♾️ مدى الحياة', '1_month' => '🗓️ شهر', '3_months' => '🗓️ 3 أشهر', '6_months' => '🗓️ 6 أشهر', '1_year' => '🗓️ سنة'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="duration_type" value="{{ $val }}" class="sr-only peer" {{ $val === 'lifetime' ? 'checked' : '' }}>
                            <div class="peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 border border-slate-600 rounded-xl py-2.5 text-center text-xs font-bold text-slate-400 hover:border-amber-400 transition">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ملاحظة --}}
            <div>
                <label class="block text-slate-300 text-sm font-bold mb-1.5">
                    <i class="fa-solid fa-note-sticky text-slate-500 mr-1"></i> ملاحظة (اختيارية)
                </label>
                <textarea name="notes" rows="2" placeholder="مثال: تم المنح بناءً على طلب العميل..."
                    class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-slate-600 transition resize-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 py-3 bg-amber-500 hover:bg-amber-400 text-white font-black rounded-xl text-sm transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-bolt"></i> تفعيل الاشتراك الآن
                </button>
                <button type="button" onclick="closeGrantModal()"
                    class="px-5 py-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 font-bold rounded-xl text-sm transition">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openGrantModal() {
        const m = document.getElementById('grant-sub-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeGrantModal() {
        const m = document.getElementById('grant-sub-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
</script>

@endsection
