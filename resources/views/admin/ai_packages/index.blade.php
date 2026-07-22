@extends('layouts.admin')

@section('title', 'باقات المساعد الذكي')

@section('content')
<div class="space-y-6" dir="rtl">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <i class="fa-solid fa-box-open text-emerald-400"></i>
                باقات المساعد الذكي القانوني
            </h1>
            <p class="text-slate-400 text-sm mt-1">إدارة وتحكم كامل في باقات اشتراك المساعد القانوني الذكي وبوابة Stripe</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai_packages.subscriptions') }}"
               class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-users"></i> المشتركون
            </a>
            <a href="{{ route('admin.ai_packages.create') }}"
               class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm flex items-center gap-2 transition shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-plus"></i> إضافة باقة جديدة
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm font-semibold">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm font-semibold">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-sidebar rounded-xl border border-slate-700/50 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">إجمالي الباقات</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-layer-group text-blue-400 text-xs"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-white">{{ $stats['total_packages'] }}</p>
        </div>
        <div class="bg-sidebar rounded-xl border border-slate-700/50 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">الباقات المفعلة</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-check-circle text-emerald-400 text-xs"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-emerald-400">{{ $stats['active_packages'] }}</p>
        </div>
        <div class="bg-sidebar rounded-xl border border-slate-700/50 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">المشتركون النشطون</span>
                <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-users text-violet-400 text-xs"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-white">{{ $stats['total_subscriptions'] }}</p>
        </div>
        <div class="bg-sidebar rounded-xl border border-slate-700/50 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">الإيرادات (ر.س)</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-coins text-amber-400 text-xs"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-amber-400">{{ number_format($stats['total_revenue'], 0) }}</p>
        </div>
    </div>

    {{-- Packages Cards Grid --}}
    @if($packages->isEmpty())
        <div class="bg-sidebar rounded-2xl border border-slate-700/50 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-box-open text-emerald-400 text-2xl"></i>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">لا توجد باقات بعد</h3>
            <p class="text-slate-400 text-sm mb-6">قم بإنشاء أول باقة للمساعد الذكي الآن</p>
            <a href="{{ route('admin.ai_packages.create') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-400 transition">
                <i class="fa-solid fa-plus"></i> إنشاء باقة
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($packages as $package)
                @php
                    $colors = match($package->color_scheme) {
                        'indigo' => ['border' => 'border-indigo-500/30', 'badge' => 'bg-indigo-500/10 text-indigo-400', 'btn' => 'bg-indigo-500 hover:bg-indigo-400', 'glow' => 'shadow-indigo-500/10', 'icon' => 'text-indigo-400'],
                        'gold'   => ['border' => 'border-amber-500/30', 'badge' => 'bg-amber-500/10 text-amber-400', 'btn' => 'bg-amber-500 hover:bg-amber-400', 'glow' => 'shadow-amber-500/10', 'icon' => 'text-amber-400'],
                        'slate'  => ['border' => 'border-slate-500/30', 'badge' => 'bg-slate-500/10 text-slate-400', 'btn' => 'bg-slate-500 hover:bg-slate-400', 'glow' => 'shadow-slate-500/10', 'icon' => 'text-slate-400'],
                        default  => ['border' => 'border-emerald-500/30', 'badge' => 'bg-emerald-500/10 text-emerald-400', 'btn' => 'bg-emerald-500 hover:bg-emerald-400', 'glow' => 'shadow-emerald-500/10', 'icon' => 'text-emerald-400'],
                    };
                @endphp
                <div class="bg-sidebar rounded-2xl border {{ $colors['border'] }} p-5 flex flex-col gap-4 shadow-xl {{ $colors['glow'] }} relative">
                    {{-- Popular Badge --}}
                    @if($package->is_popular && $package->badge_text)
                        <div class="absolute -top-3 right-5">
                            <span class="px-3 py-1 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 text-white text-xs font-bold shadow-md">
                                {{ $package->badge_text }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-white font-extrabold text-lg">{{ $package->name }}</h3>
                            @if($package->description)
                                <p class="text-slate-400 text-xs mt-0.5">{{ $package->description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="{{ $colors['badge'] }} px-2.5 py-0.5 rounded-full text-xs font-bold">
                                {{ $package->is_active ? 'مفعلة' : 'موقوفة' }}
                            </span>
                            @if($package->is_free)
                                <span class="bg-blue-500/10 text-blue-400 px-2.5 py-0.5 rounded-full text-xs font-bold">مجانية</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-white">{{ $package->price_display }}</span>
                        @if(!$package->is_free)
                            <span class="text-slate-400 text-sm">{{ $package->billing_period_label }}</span>
                        @endif
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-sm {{ $colors['icon'] }}">
                            <i class="fa-solid fa-comments text-xs"></i>
                            <span>{{ $package->query_limit_display }}</span>
                        </div>
                        @if($package->features)
                            @foreach(array_slice($package->features, 0, 3) as $feature)
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <i class="fa-solid fa-check text-emerald-400 text-xs"></i>
                                    <span>{{ $feature }}</span>
                                </div>
                            @endforeach
                            @if(count($package->features) > 3)
                                <div class="text-xs text-slate-500">+ {{ count($package->features) - 3 }} مزايا أخرى...</div>
                            @endif
                        @endif
                    </div>

                    <div class="border-t border-slate-700/50 pt-3 flex items-center justify-between">
                        <div class="text-xs text-slate-400">
                            <i class="fa-solid fa-users text-xs me-1"></i>
                            {{ $package->active_subscriptions_count }} مشترك نشط
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- Toggle Active --}}
                            <button onclick="togglePackage({{ $package->id }}, this)"
                                    data-active="{{ $package->is_active ? 'true' : 'false' }}"
                                    class="w-8 h-8 rounded-lg {{ $package->is_active ? 'bg-emerald-500/10 text-emerald-400 hover:bg-red-500/10 hover:text-red-400' : 'bg-slate-500/10 text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400' }} flex items-center justify-center transition text-xs">
                                <i class="fa-solid {{ $package->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                            </button>

                            {{-- Edit --}}
                            <a href="{{ route('admin.ai_packages.edit', $package) }}"
                               class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 flex items-center justify-center transition text-xs">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.ai_packages.destroy', $package) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه الباقة؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center transition text-xs">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent Subscriptions --}}
    @if($recentSubscriptions->isNotEmpty())
        <div class="bg-sidebar rounded-2xl border border-slate-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700/50 flex items-center justify-between">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-400 text-sm"></i>
                    أحدث الاشتراكات
                </h3>
                <a href="{{ route('admin.ai_packages.subscriptions') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold">
                    عرض الكل →
                </a>
            </div>
            <div class="divide-y divide-slate-700/30">
                @foreach($recentSubscriptions as $sub)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center">
                                <i class="fa-solid fa-user text-emerald-400 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-white text-sm font-semibold">{{ $sub->user?->name ?? 'غير محدد' }}</p>
                                <p class="text-slate-400 text-xs">{{ $sub->package?->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold {{ $sub->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-500/10 text-slate-400' }}">
                                {{ $sub->status_label }}
                            </span>
                            <p class="text-slate-500 text-xs mt-0.5">{{ $sub->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function togglePackage(id, btn) {
    const isActive = btn.dataset.active === 'true';
    fetch(`/admin/ai-packages/${id}/toggle`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}
</script>
@endsection
