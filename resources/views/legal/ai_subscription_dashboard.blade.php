<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
    @include('partials.google-analytics')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إدارة الاشتراك — رديف القانوني</title>
    <meta name="description" content="إدارة اشتراكك في المساعد القانوني الذكي — عرض الفواتير وتجديد الاشتراك والإلغاء.">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Tajawal', sans-serif; }

        body {
            background-color: #0b1120;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(13, 148, 136, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(79, 70, 229, 0.1) 0%, transparent 55%);
            min-height: 100vh;
        }

        /* Subtle dot-grid overlay matching main legal assistant UI */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .glass {
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-light {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .card-glow {
            box-shadow: 0 0 40px rgba(13, 148, 136, 0.15), 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .badge-active {
            background: linear-gradient(135deg, #0f766e, #0d9488);
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(13, 148, 136, 0.5); }
            50%  { box-shadow: 0 0 20px rgba(13, 148, 136, 0.9); }
        }

        .countdown-ring {
            stroke-dasharray: 176;
            transition: stroke-dashoffset 1s ease;
        }

        .invoice-row:hover {
            background: rgba(13, 148, 136, 0.08);
            transform: translateX(-2px);
        }

        .invoice-row {
            transition: all 0.2s ease;
        }

        .btn-cancel {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background: rgba(239, 68, 68, 0.25);
            border-color: rgba(239, 68, 68, 0.6);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        .package-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .package-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(13, 148, 136, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(13, 148, 136, 0.2);
        }

        .usage-bar {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            overflow: hidden;
        }
        .usage-fill {
            background: linear-gradient(90deg, #0d9488, #2dd4bf);
            border-radius: 999px;
            height: 100%;
            transition: width 1s ease;
        }

        @media (max-width: 768px) {
            .hide-mobile { display: none; }
        }
    </style>
</head>

<body class="text-white relative">

    {{-- ── NAV ─────────────────────────────────────────────── --}}
    <nav class="glass border-b border-white/10 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('legal_assistant.public') }}" class="flex items-center gap-2.5 hover:opacity-80 transition">
                    <img src="{{ asset('images/icon.png') }}" class="w-8 h-8 rounded-lg shadow-sm" alt="رديف">
                    <div>
                        <span class="font-black text-white text-sm leading-none block">المستشار القضائي والنظامي الذكي</span>
                        <span class="text-teal-400 text-[10px] font-bold tracking-wider">إدارة الاشتراك والفواتير</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('legal_assistant.public') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl glass-light hover:bg-white/15 transition text-sm font-bold text-slate-200">
                    <i class="fa-solid fa-comment-dots text-teal-400"></i>
                    <span class="hide-mobile">العودة للمساعد</span>
                </a>
                <a href="{{ route('ai.packages') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#0f766e] to-[#0d9488] hover:from-[#115e59] hover:to-[#0f766e] transition text-sm font-bold shadow-md shadow-teal-900/30">
                    <i class="fa-solid fa-box-open"></i>
                    <span class="hide-mobile">عرض الباقات</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-10 relative z-10">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl glass-light border border-teal-500/30 flex items-center gap-3 text-teal-300">
            <div class="w-10 h-10 rounded-xl bg-teal-500/20 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-check text-teal-400 text-xl"></i>
            </div>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl glass-light border border-red-500/30 flex items-center gap-3 text-red-300">
            <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-xmark text-red-400 text-xl"></i>
            </div>
            <span class="font-bold">{{ session('error') }}</span>
        </div>
        @endif

        {{-- ── HEADER ────────────────────────────────────────────── --}}
        <div class="mb-10">
            <h1 class="text-3xl md:text-4xl font-black mb-2">
                <span class="bg-gradient-to-l from-teal-300 via-emerald-300 to-indigo-300 bg-clip-text text-transparent">إدارة الاشتراك</span>
                والفواتير
            </h1>
            <p class="text-slate-400 font-medium">اطّلع على تفاصيل اشتراكك، وسجل الفواتير، وخيارات الإدارة.</p>
        </div>

        @if($currentSubscription)
        {{-- ── ACTIVE SUBSCRIPTION CARD ───────────────────────────── --}}
        <div class="glass rounded-3xl p-8 card-glow mb-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            @php
                                $statusColor = match($currentSubscription->status) {
                                    'active'    => 'badge-active',
                                    'cancelled' => 'bg-red-500/80',
                                    'pending'   => 'bg-yellow-500/80',
                                    default     => 'bg-slate-500/80',
                                };
                                $statusLabel = match($currentSubscription->status) {
                                    'active'    => 'نشط ✓',
                                    'cancelled' => 'تم الإلغاء',
                                    'pending'   => 'قيد الانتظار',
                                    default     => 'منتهي',
                                };
                            @endphp
                            <span class="px-3.5 py-1 rounded-full text-xs font-black {{ $statusColor }} text-white shadow-lg">
                                {{ $statusLabel }}
                            </span>
                            @if($currentSubscription->status === 'cancelled')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                ينتهي في {{ $currentSubscription->ends_at?->format('Y/m/d') }}
                            </span>
                            @endif
                        </div>
                        <h2 class="text-2xl md:text-3xl font-black text-white mb-1">
                            {{ $currentSubscription->package->name ?? 'باقة غير محددة' }}
                        </h2>
                        <p class="text-slate-400 font-medium">
                            {{ $currentSubscription->package->description ?? '' }}
                        </p>
                    </div>

                    <div class="text-center glass-light rounded-2xl p-5 min-w-[140px] border border-teal-500/20">
                        <div class="text-3xl font-black text-white">
                            {{ number_format($currentSubscription->amount_paid, 0) }}
                        </div>
                        <div class="text-teal-400 font-bold text-sm mt-1">
                            {{ $currentSubscription->currency }} /
                            {{ $currentSubscription->package->billing_period_label ?? 'شهرياً' }}
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="glass-light rounded-2xl p-4 text-center">
                        <div class="w-10 h-10 bg-teal-500/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-calendar-check text-teal-400"></i>
                        </div>
                        <p class="text-xs text-slate-400 mb-1">تاريخ التفعيل</p>
                        <p class="font-bold text-white text-sm">{{ $currentSubscription->starts_at?->format('Y/m/d') ?? 'غير محدد' }}</p>
                    </div>

                    <div class="glass-light rounded-2xl p-4 text-center">
                        <div class="w-10 h-10 {{ $currentSubscription->status === 'active' ? 'bg-emerald-500/20' : 'bg-red-500/20' }} rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-calendar-xmark {{ $currentSubscription->status === 'active' ? 'text-emerald-400' : 'text-red-400' }}"></i>
                        </div>
                        <p class="text-xs text-slate-400 mb-1">
                            {{ $currentSubscription->package->billing_period === 'lifetime' ? 'صلاحية' : 'تاريخ التجديد' }}
                        </p>
                        <p class="font-bold text-white text-sm">
                            @if($currentSubscription->package->billing_period === 'lifetime')
                                مدى الحياة ♾️
                            @else
                                {{ $currentSubscription->ends_at?->format('Y/m/d') ?? 'غير محدد' }}
                            @endif
                        </p>
                    </div>

                    <div class="glass-light rounded-2xl p-4 text-center">
                        <div class="w-10 h-10 bg-teal-500/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-comments text-teal-400"></i>
                        </div>
                        <p class="text-xs text-slate-400 mb-1">الاستعلامات المستخدمة</p>
                        <p class="font-bold text-white text-sm">{{ number_format($currentSubscription->queries_used ?? 0) }}</p>
                    </div>

                    <div class="glass-light rounded-2xl p-4 text-center">
                        <div class="w-10 h-10 bg-cyan-500/20 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i class="fa-solid fa-infinity text-cyan-400"></i>
                        </div>
                        <p class="text-xs text-slate-400 mb-1">الاستعلامات المتاحة</p>
                        <p class="font-bold text-white text-sm">
                            @if($currentSubscription->package->is_unlimited ?? false)
                                غير محدودة ∞
                            @else
                                {{ number_format(max(0, ($currentSubscription->package->query_limit ?? 0) - ($currentSubscription->queries_used ?? 0))) }}
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Usage Bar --}}
                @if(!($currentSubscription->package->is_unlimited ?? false) && ($currentSubscription->package->query_limit ?? 0) > 0)
                @php
                    $used = $currentSubscription->queries_used ?? 0;
                    $limit = $currentSubscription->package->query_limit ?? 1;
                    $pct = min(100, round($used / $limit * 100));
                @endphp
                <div class="mb-6">
                    <div class="flex justify-between text-xs text-slate-400 mb-2">
                        <span>الاستخدام الشهري</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="usage-bar h-2">
                        <div class="usage-fill" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endif

                {{-- Days Remaining --}}
                @if($currentSubscription->ends_at && $currentSubscription->package->billing_period !== 'lifetime')
                @php
                    $secondsLeft = max(0, now()->diffInSeconds($currentSubscription->ends_at, false));
                    $daysLeft = (int) ceil($secondsLeft / 86400);
                    $totalDays = $currentSubscription->starts_at ? max(1, (int) round($currentSubscription->starts_at->diffInDays($currentSubscription->ends_at))) : 30;
                    $progressPct = min(100, max(0, round((1 - ($daysLeft / $totalDays)) * 100)));
                @endphp
                <div class="flex items-center gap-4 glass-light rounded-2xl p-4 mb-6">
                    <div class="relative flex-shrink-0">
                        <svg width="64" height="64" viewBox="0 0 64 64">
                            <circle cx="32" cy="32" r="28" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="5"/>
                            <circle cx="32" cy="32" r="28" fill="none"
                                stroke="{{ $daysLeft < 7 ? '#ef4444' : ($daysLeft < 14 ? '#f59e0b' : '#0d9488') }}"
                                stroke-width="5"
                                stroke-linecap="round"
                                transform="rotate(-90 32 32)"
                                stroke-dasharray="176"
                                stroke-dashoffset="{{ 176 - ($progressPct / 100 * 176) }}"
                                class="countdown-ring"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-black text-white">{{ $daysLeft }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-white">{{ $daysLeft }} يوم متبقي</p>
                        <p class="text-xs text-slate-400 mt-1">
                            ينتهي الاشتراك في {{ $currentSubscription->ends_at->format('d/m/Y') }}
                            @if($currentSubscription->status === 'active')
                            — سيتم إرسال تذكير بالتجديد قبل الانتهاء
                            @endif
                        </p>
                    </div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('legal_assistant.public') }}"
                       class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#0f766e] to-[#0d9488] hover:from-[#115e59] hover:to-[#0f766e] transition font-bold shadow-lg shadow-teal-900/40">
                        <i class="fa-solid fa-comment-dots"></i>
                        ابدأ الاستخدام الآن
                    </a>
                    <a href="{{ route('ai.packages') }}"
                       class="flex items-center gap-2 px-6 py-3 rounded-xl glass-light hover:bg-white/15 transition font-bold border border-white/10 text-slate-200">
                        <i class="fa-solid fa-arrow-up-right-dots text-teal-400"></i>
                        ترقية الباقة
                    </a>
                    @if($currentSubscription->status === 'active')
                    <button onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
                            class="flex items-center gap-2 px-6 py-3 rounded-xl btn-cancel font-bold ms-auto">
                        <i class="fa-solid fa-ban"></i>
                        إلغاء التجديد التلقائي
                    </button>
                    @endif
                </div>
            </div>
        </div>

        @else
        {{-- ── NO SUBSCRIPTION ─────────────────────────────────────── --}}
        <div class="glass rounded-3xl p-12 card-glow mb-8 text-center">
            <div class="w-20 h-20 bg-teal-500/10 rounded-full flex items-center justify-center mx-auto mb-5 border border-teal-500/20">
                <i class="fa-solid fa-scale-balanced text-teal-400 text-4xl"></i>
            </div>
            <h2 class="text-2xl font-black text-white mb-3">لا يوجد اشتراك نشط حالياً</h2>
            <p class="text-slate-400 mb-8 max-w-md mx-auto">اشترك الآن في إحدى باقات المساعد القانوني للوصول إلى التحليل والنظام القضائي الذكي.</p>
            <a href="{{ route('ai.packages') }}"
               class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[#0f766e] to-[#0d9488] hover:from-[#115e59] hover:to-[#0f766e] transition font-black text-lg shadow-xl shadow-teal-900/40">
                <i class="fa-solid fa-box-open"></i>
                عرض الباقات والأسعار
            </a>
        </div>
        @endif

        {{-- ── INVOICES TABLE ──────────────────────────────────────── --}}
        <div class="glass rounded-3xl overflow-hidden card-glow mb-8">
            <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-500/20 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-receipt text-teal-400"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-white text-lg">سجل الفواتير</h3>
                        <p class="text-xs text-slate-400">جميع المعاملات السابقة والدفعات</p>
                    </div>
                </div>
                <span class="glass-light px-3 py-1 rounded-full text-xs font-bold text-teal-300 border border-teal-500/20">
                    {{ $allSubscriptions->count() }} فاتورة
                </span>
            </div>

            @if($allSubscriptions->isEmpty())
            <div class="py-16 text-center">
                <i class="fa-regular fa-folder-open text-slate-600 text-5xl mb-4 block"></i>
                <p class="text-slate-400">لا توجد فواتير حتى الآن</p>
            </div>
            @else
            <div class="w-full">
                <table class="w-full text-right table-auto">
                    <thead class="border-b border-white/5 bg-white/[0.02]">
                        <tr class="text-xs text-slate-400 uppercase tracking-wider">
                            <th class="px-4 py-3.5 font-bold">#</th>
                            <th class="px-4 py-3.5 font-bold">الباقة</th>
                            <th class="px-4 py-3.5 font-bold">المبلغ</th>
                            <th class="px-4 py-3.5 font-bold">الحالة</th>
                            <th class="px-4 py-3.5 font-bold">تاريخ الدفع</th>
                            <th class="px-4 py-3.5 font-bold text-center">الفاتورة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($allSubscriptions as $sub)
                        @php
                            $rowStatus = match($sub->status) {
                                'active'    => ['text-teal-300', 'bg-teal-500/15 border-teal-500/30', 'نشط ✓'],
                                'cancelled' => ['text-red-400', 'bg-red-500/15 border-red-500/30', 'ملغي'],
                                'pending'   => ['text-yellow-400', 'bg-yellow-500/15 border-yellow-500/30', 'قيد الانتظار'],
                                'expired'   => ['text-slate-400', 'bg-slate-500/15 border-slate-500/30', 'منتهي'],
                                default     => ['text-slate-400', 'bg-slate-500/15 border-slate-500/30', $sub->status],
                            };
                            $invoiceNo = 'INV-' . date('Y') . '-' . str_pad($sub->id, 5, '0', STR_PAD_LEFT);
                        @endphp
                        <tr class="invoice-row">
                            <td class="px-4 py-3.5">
                                <span class="font-mono text-xs text-slate-400 bg-white/5 px-2 py-1 rounded">#{{ $sub->id }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-white text-sm">{{ $sub->package->name ?? 'غير محدد' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $sub->package->billing_period_label ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-black text-white text-base">{{ number_format($sub->amount_paid, 0) }}</span>
                                <span class="text-xs text-slate-400 ms-1">{{ $sub->currency }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $rowStatus[1] }} {{ $rowStatus[0] }}">
                                    {{ $rowStatus[2] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-300">
                                {{ $sub->starts_at?->format('Y/m/d H:i') ?? ($sub->created_at?->format('Y/m/d H:i') ?? '—') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <button onclick="document.getElementById('invoice-modal-{{ $sub->id }}').classList.remove('hidden')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-500/10 hover:bg-teal-500/20 text-teal-300 border border-teal-500/20 text-xs font-bold transition">
                                    <i class="fa-solid fa-file-invoice me-0.5"></i>
                                    معاينة الفاتورة
                                </button>
                            </td>
                        </tr>

                        {{-- ── INVOICE MODAL FOR EACH ITEM ───────────────────────────── --}}
                        <div id="invoice-modal-{{ $sub->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('invoice-modal-{{ $sub->id }}').classList.add('hidden')"></div>
                            <div class="glass rounded-3xl p-6 md:p-8 max-w-lg w-full relative z-10 card-glow text-white overflow-hidden">

                                {{-- Printable Invoice Container --}}
                                <div id="printable-invoice-{{ $sub->id }}" class="space-y-6">

                                    {{-- Receipt Header --}}
                                    <div class="flex justify-between items-start border-b border-white/10 pb-5">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ asset('images/icon.png') }}" class="w-10 h-10 rounded-xl" alt="RADIIF">
                                            <div>
                                                <h4 class="font-black text-lg text-white leading-none">RADIIF LTD</h4>
                                                <p class="text-[11px] text-teal-400 font-medium mt-1">رديف للتقنية القانونية</p>
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            <span class="text-xs font-mono bg-teal-500/20 text-teal-300 px-3 py-1 rounded-full border border-teal-500/30">
                                                {{ $invoiceNo }}
                                            </span>
                                            <p class="text-[11px] text-slate-400 mt-1">
                                                {{ $sub->starts_at?->format('d/m/Y') ?? $sub->created_at?->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Customer Info --}}
                                    <div class="grid grid-cols-2 gap-4 bg-white/5 rounded-2xl p-4 text-xs">
                                        <div>
                                            <p class="text-slate-400 mb-1">العميل</p>
                                            <p class="font-bold text-white">{{ $user->name }}</p>
                                            <p class="text-slate-400 text-[11px] truncate mt-0.5">{{ $user->email }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400 mb-1">وسيلة الدفع</p>
                                            <p class="font-bold text-teal-300 flex items-center gap-1">
                                                <i class="fa-solid fa-credit-card"></i> Stripe / بطاقة مدى
                                            </p>
                                            <p class="text-slate-400 text-[10px] font-mono mt-0.5 truncate">
                                                {{ Str::limit($sub->stripe_payment_intent_id ?? $sub->stripe_session_id ?? 'N/A', 20) }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Summary Table --}}
                                    <div class="border border-white/10 rounded-2xl overflow-hidden">
                                        <table class="w-full text-xs text-right">
                                            <thead class="bg-white/5 text-slate-400 border-b border-white/10">
                                                <tr>
                                                    <th class="p-3 font-bold">المنتج / الباقة</th>
                                                    <th class="p-3 font-bold text-left">المبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="p-3">
                                                        <div class="font-bold text-white">{{ $sub->package->name ?? 'اشتراك المساعد الذكي' }}</div>
                                                        <div class="text-[10px] text-slate-400 mt-0.5">اشتراك {{ $sub->package->billing_period_label ?? 'شهري' }} — {{ $sub->package->query_limit_display ?? 'استعلامات' }}</div>
                                                    </td>
                                                    <td class="p-3 text-left font-mono font-bold text-white text-sm">
                                                        {{ number_format($sub->amount_paid, 2) }} {{ $sub->currency }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="bg-white/5 border-t border-white/10 font-bold">
                                                <tr>
                                                    <td class="p-3 text-white">إجمالي المدفوع</td>
                                                    <td class="p-3 text-left font-mono text-teal-300 text-base">
                                                        {{ number_format($sub->amount_paid, 2) }} {{ $sub->currency }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- Status Banner --}}
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-teal-500/10 border border-teal-500/30 text-xs">
                                        <span class="text-slate-300">حالة الدفع:</span>
                                        <span class="font-bold text-teal-300 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> مدفوع بنجاح عبر Stripe
                                        </span>
                                    </div>

                                    {{-- Company Contact Footer --}}
                                    <div class="text-center text-[10px] text-slate-400 space-y-1 pt-2 border-t border-white/5">
                                        <p>لأي استفسارات بخصوص الفاتورة، يُرجى التواصل معنا عبر <strong>info@radiif.com</strong> أو الهاتف <strong>+966 57 007 9182</strong></p>
                                        <p class="text-slate-500">شكراً لاستخدامك المستشار القضائي والنظامي الذكي — RADIIF LTD</p>
                                    </div>

                                </div>

                                {{-- Modal Buttons --}}
                                <div class="flex gap-3 mt-6 pt-4 border-t border-white/10 no-print">
                                    <button onclick="printInvoice('printable-invoice-{{ $sub->id }}')"
                                            class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-[#0f766e] to-[#0d9488] hover:from-[#115e59] hover:to-[#0f766e] transition font-bold text-xs text-white flex items-center justify-center gap-2 shadow-md">
                                        <i class="fa-solid fa-print"></i>
                                        طباعة / حفظ الفاتورة (PDF)
                                    </button>
                                    <button onclick="document.getElementById('invoice-modal-{{ $sub->id }}').classList.add('hidden')"
                                            class="px-5 py-2.5 rounded-xl glass-light hover:bg-white/15 transition font-bold text-xs text-slate-300">
                                        إغلاق
                                    </button>
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── OTHER PACKAGES ──────────────────────────────────────── --}}
        @if($packages->isNotEmpty())
        <div class="glass rounded-3xl p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-teal-500/20 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-layer-group text-teal-400"></i>
                </div>
                <div>
                    <h3 class="font-black text-white text-lg">الباقات المتاحة للترقية</h3>
                    <p class="text-xs text-slate-400">اختر الباقة المناسبة لاحتياجاتك</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($packages->take(3) as $pkg)
                <div class="package-card rounded-2xl p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-white text-base">{{ $pkg->name }}</h4>
                            <p class="text-xs text-slate-400 mt-1">{{ $pkg->query_limit_display }}</p>
                        </div>
                        @if($pkg->is_popular)
                        <span class="bg-teal-500/20 text-teal-300 text-[10px] font-black px-2.5 py-0.5 rounded-full border border-teal-500/30">
                            الأكثر شعبية
                        </span>
                        @endif
                    </div>
                    <div class="text-2xl font-black text-white mb-4">
                        {{ $pkg->price_display }}
                        @if(!$pkg->is_free)
                        <span class="text-xs text-slate-400 font-normal">{{ $pkg->billing_period_label }}</span>
                        @endif
                    </div>
                    <a href="{{ route('ai.subscription.checkout', $pkg) }}"
                       class="block w-full text-center py-2.5 rounded-xl font-bold text-sm transition
                              {{ $currentSubscription && $currentSubscription->ai_package_id === $pkg->id
                                    ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30 cursor-default'
                                    : 'bg-gradient-to-r from-[#0f766e] to-[#0d9488] hover:from-[#115e59] hover:to-[#0f766e] text-white shadow-md' }}">
                        {{ $currentSubscription && $currentSubscription->ai_package_id === $pkg->id ? 'باقتك الحالية ✓' : 'الاشتراك الآن' }}
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ── CANCEL CONFIRMATION MODAL ───────────────────────────────── --}}
    <div id="cancel-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('cancel-modal').classList.add('hidden')"></div>
        <div class="glass rounded-3xl p-8 max-w-md w-full relative z-10 card-glow">
            <div class="w-16 h-16 bg-red-500/20 rounded-2xl flex items-center justify-center mx-auto mb-5 border border-red-500/30">
                <i class="fa-solid fa-triangle-exclamation text-red-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-white text-center mb-3">تأكيد إلغاء التجديد التلقائي</h3>
            <p class="text-slate-400 text-center text-sm mb-2">
                هل أنت متأكد من إلغاء تجديد اشتراكك في
                <strong class="text-white">{{ $currentSubscription?->package->name }}</strong>؟
            </p>
            @if($currentSubscription?->ends_at)
            <div class="glass-light rounded-xl p-3 text-center mb-6 border border-teal-500/20">
                <p class="text-xs text-slate-400">ستبقى خدماتك متاحة حتى</p>
                <p class="font-black text-teal-300 text-base mt-1">{{ $currentSubscription?->ends_at->format('d/m/Y') }}</p>
            </div>
            @endif
            <div class="flex gap-3">
                <button onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                        class="flex-1 py-3 rounded-xl glass-light hover:bg-white/15 transition font-bold text-sm text-slate-200">
                    تراجع
                </button>
                <form method="POST" action="{{ route('ai.subscription.cancel') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-red-600/80 hover:bg-red-600 transition font-bold text-sm text-white shadow-lg">
                        نعم، إلغاء التجديد
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── FOOTER ─────────────────────────────────────────────────── --}}
    <div class="text-center pb-8 text-slate-500 text-xs relative z-10">
        <p>جميع المدفوعات مؤمّنة عبر <span class="text-slate-400 font-bold">Stripe</span> — المستشار القضائي والنظامي الذكي &copy; {{ date('Y') }}</p>
    </div>

    <script>
        function printInvoice(elementId) {
            const contentElement = document.getElementById(elementId);
            if (!contentElement) return;

            const printWindow = window.open('', '_blank', 'height=750,width=850');
            const doc = printWindow.document;

            doc.open();
            doc.write('<!DOCTYPE html><html lang="ar" dir="rtl"><head>');
            doc.write('<title>فاتورة شراء — RADIIF LTD</title>');
            doc.write('<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">');
            doc.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">');
            doc.write('<script src="https://cdn.tailwindcss.com"><' + '/script>');
            doc.write('<' + 'style' + '>');
            doc.write('body { font-family: "Tajawal", sans-serif; background: #ffffff !important; color: #0f172a !important; padding: 24px; }');
            doc.write('.text-white { color: #0f172a !important; }');
            doc.write('.bg-white\\/5 { background: #f8fafc !important; border: 1px solid #e2e8f0 !important; }');
            doc.write('.border-white\\/10 { border-color: #e2e8f0 !important; }');
            doc.write('.border-white\\/5 { border-color: #f1f5f9 !important; }');
            doc.write('.text-slate-400 { color: #64748b !important; }');
            doc.write('.text-slate-300 { color: #334155 !important; }');
            doc.write('.text-teal-400, .text-teal-300 { color: #0d9488 !important; }');
            doc.write('.bg-teal-500\\/10, .bg-teal-500\\/20 { background: #f0fdf4 !important; border-color: #bbf7d0 !important; }');
            doc.write('.no-print { display: none !important; }');
            doc.write('<' + '/style' + '>');
            doc.write('</head><body onload="setTimeout(function(){ window.print(); window.close(); }, 500);">');
            doc.write('<div style="max-width: 700px; margin: 0 auto;">' + contentElement.innerHTML + '</div>');
            doc.write('</body></html>');
            doc.close();
        }
    </script>
</body>
</html>
