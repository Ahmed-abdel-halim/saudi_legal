@extends('layouts.admin')

@section('title', __('admin.user_management') ?? 'إدارة المستخدمين')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center text-lg shadow-glow">
                <i class="fa-solid fa-users"></i>
            </span>
            <span>{!! __('admin.user_management') !!}</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">{!! __('admin.user_management_desc') ?? 'إدارة والإشراف على كافة مستخدمي المنصة وصلاحياتهم وباقاتهم.' !!}</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    {{-- Total Users --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-users text-teal-400"></i> {!! __('admin.total_users_kpi') !!}
        </span>
        <span class="text-3xl font-black text-white">{{ number_format($totalUsers) }}</span>
    </div>

    {{-- Active Users --}}
    <div class="bg-dark-card border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-circle-check"></i> {!! __('admin.active') !!}
        </span>
        <span class="text-3xl font-black text-emerald-400">{{ number_format($activeUsers) }}</span>
    </div>

    {{-- Suspended Users --}}
    <div class="bg-dark-card border border-rose-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-rose-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-ban"></i> {!! __('admin.suspended') !!}
        </span>
        <span class="text-3xl font-black text-rose-400">{{ number_format($suspendedUsers) }}</span>
    </div>

    {{-- Experts --}}
    <div class="bg-dark-card border border-indigo-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-user-tie"></i> {!! __('admin.expert_role') !!}
        </span>
        <span class="text-3xl font-black text-indigo-400">{{ number_format($expertCount) }}</span>
    </div>

    {{-- Companies & Suppliers --}}
    <div class="bg-dark-card border border-amber-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group col-span-2 sm:col-span-1">
        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-building"></i> {!! __('admin.company_role') !!}
        </span>
        <span class="text-3xl font-black text-amber-400">{{ number_format($companyCount) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden mb-8">
    <div class="p-4 bg-white/[0.02]">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 items-center">
            
            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_users') ?? 'ابحث بالاسم، البريد، أو الهاتف...' }}"
                    class="w-full bg-dark-navy/60 border border-dark-border text-white text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 pl-9 rtl:pr-9 rtl:pl-3 py-2.5 transition outline-none">
            </div>

            {{-- Role Filter --}}
            <select name="role" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[150px]">
                <option value="">{!! __('admin.all_roles') !!}</option>
                <option value="expert"    {{ request('role') == 'expert'     ? 'selected' : '' }}>⚖️ {!! __('admin.expert_role') !!}</option>
                <option value="company"   {{ request('role') == 'company'    ? 'selected' : '' }}>🏢 {!! __('admin.company_role') !!}</option>
                <option value="supplier"  {{ request('role') == 'supplier'   ? 'selected' : '' }}>🏬 {!! __('admin.supplier_role') ?? 'مورد' !!}</option>
                <option value="freelancer"{{ request('role') == 'freelancer' ? 'selected' : '' }}>💼 {!! __('admin.freelancer_role') !!}</option>
                <option value="student"   {{ request('role') == 'student'    ? 'selected' : '' }}>🎓 {!! __('admin.student_role') !!}</option>
                <option value="client"    {{ request('role') == 'client'     ? 'selected' : '' }}>👤 {!! __('admin.client_role') ?? 'عميل' !!}</option>
            </select>

            {{-- Status Filter --}}
            <select name="status" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[130px]">
                <option value="">{!! __('admin.all_statuses') ?? 'جميع الحالات' !!}</option>
                <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>{!! __('admin.active') !!}</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{!! __('admin.suspended') !!}</option>
            </select>

            <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                <i class="fa-solid fa-filter"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="bg-red-500/10 text-red-400 px-4 py-2.5 rounded-xl text-xs font-bold border border-red-500/30 hover:bg-red-500/20 transition whitespace-nowrap">
                    <i class="fa-solid fa-rotate-left"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="mr-auto rtl:mr-auto rtl:ml-0 text-xs text-gray-400 font-medium bg-white/[0.04] px-3 py-1.5 rounded-xl border border-white/5">
                {{ $users->total() }} {{ __('admin.results_found') ?? 'مستخدم' }}
            </span>
        </form>
    </div>
</div>

{{-- Main Table --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-white/[0.02] border-b border-dark-border text-gray-400 text-xs uppercase font-bold tracking-wider">
                <tr>
                    <th class="p-4">{!! __('admin.user_details_col') ?? 'بيانات المستخدم' !!}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.role_col') ?? 'نوع الحساب' !!}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.phone_col') ?? 'رقم الجوال' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.domain_col') ?? 'المجال / التخصص' }}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.status_col') !!}</th>
                    <th class="p-4 whitespace-nowrap">باقة المساعد الذكي</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.joined_at_col') !!}</th>
                    <th class="p-4 text-center whitespace-nowrap">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border text-gray-300 text-xs">
                @forelse($users as $user)
                    <tr class="hover:bg-white/[0.03] transition {{ $user->is_active ? '' : 'opacity-60 bg-red-500/[0.02]' }}">

                        {{-- User Details --}}
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="relative flex-shrink-0">
                                    @if($user->avatar_url)
                                        <img class="w-10 h-10 rounded-full object-cover border border-white/10 shadow-sm"
                                             src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500/30 to-indigo-500/30 border border-teal-500/40 text-teal-300 flex items-center justify-center font-bold text-xs shadow-sm">
                                            {{ mb_substr($user->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-dark-card {{ $user->is_active ? 'bg-emerald-500 shadow-green-glow' : 'bg-gray-500' }}" title="{{ $user->is_active ? 'نشط' : 'غير نشط' }}"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-white text-xs truncate max-w-[180px]">{{ $user->name }}</div>
                                    <div class="text-[11px] text-gray-400 truncate max-w-[180px] mt-0.5">
                                        <a href="mailto:{{ $user->email }}" class="text-teal-400 hover:underline flex items-center gap-1">
                                            <i class="fa-regular fa-envelope text-[10px]"></i> {{ $user->email }}
                                        </a>
                                    </div>
                                    @if($user->job_title)
                                        <div class="text-[10px] text-gray-500 mt-0.5 italic truncate max-w-[180px]">{{ $user->job_title }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Role Badge --}}
                        <td class="p-4 whitespace-nowrap">
                            @php
                                $roleKey = strtolower($user->role ?? '');
                                $roleBadges = [
                                    'expert'     => ['bg' => 'bg-indigo-500/15', 'text' => 'text-indigo-400', 'border' => 'border-indigo-500/30', 'icon' => 'fa-solid fa-scale-balanced', 'label' => __('admin.expert_role') ?: 'خبير'],
                                    'company'    => ['bg' => 'bg-amber-500/15',  'text' => 'text-amber-400',  'border' => 'border-amber-500/30',  'icon' => 'fa-solid fa-building',       'label' => __('admin.company_role') ?: 'شركة'],
                                    'supplier'   => ['bg' => 'bg-amber-500/15',  'text' => 'text-amber-400',  'border' => 'border-amber-500/30',  'icon' => 'fa-solid fa-store',          'label' => __('admin.supplier_role') ?: 'مورد'],
                                    'freelancer' => ['bg' => 'bg-blue-500/15',   'text' => 'text-blue-400',   'border' => 'border-blue-500/30',   'icon' => 'fa-solid fa-briefcase',      'label' => __('admin.freelancer_role') ?: 'مستقل'],
                                    'student'    => ['bg' => 'bg-teal-500/15',   'text' => 'text-teal-400',   'border' => 'border-teal-500/30',   'icon' => 'fa-solid fa-graduation-cap', 'label' => __('admin.student_role') ?: 'طالب'],
                                    'client'     => ['bg' => 'bg-purple-500/15', 'text' => 'text-purple-400', 'border' => 'border-purple-500/30', 'icon' => 'fa-solid fa-user',           'label' => __('admin.client_role') ?: 'عميل'],
                                ];
                                $rb = $roleBadges[$roleKey] ?? ['bg' => 'bg-gray-500/15', 'text' => 'text-gray-300', 'border' => 'border-gray-500/30', 'icon' => 'fa-solid fa-user', 'label' => strtoupper($user->role ?? 'user')];
                            @endphp
                            <span class="{{ $rb['bg'] }} {{ $rb['text'] }} border {{ $rb['border'] }} px-2.5 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                                <i class="{{ $rb['icon'] }} text-[10px]"></i>
                                <span>{{ $rb['label'] }}</span>
                            </span>
                        </td>

                        {{-- Phone --}}
                        <td class="p-4 whitespace-nowrap">
                            @if($user->phone)
                                <div class="inline-flex items-center gap-1.5" dir="ltr">
                                    <a href="tel:{{ $user->phone }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/10 text-gray-200 hover:text-teal-400 hover:border-teal-500/30 font-mono text-xs transition" title="اتصال هاتفي">
                                        <i class="fa-solid fa-phone text-[10px] text-teal-400"></i>
                                        <span>{{ $user->phone }}</span>
                                    </a>
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone);
                                        if (str_starts_with($cleanPhone, '05')) {
                                            $cleanPhone = '966' . substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="p-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-lg transition" title="محادثة واتساب">
                                        <i class="fa-brands fa-whatsapp text-xs"></i>
                                    </a>
                                </div>
                            @else
                                <span class="text-gray-500 text-xs italic">—</span>
                            @endif
                        </td>

                        {{-- Domain / Specialization --}}
                        <td class="p-4 whitespace-nowrap">
                            @php
                                $domain = strtolower($user->expert_domain ?? '');
                            @endphp
                            <div>
                                @if($domain === 'law')
                                    <span class="bg-blue-500/15 text-blue-400 border border-blue-500/30 px-2 py-0.5 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-scale-balanced text-[9px]"></i> قانوني
                                    </span>
                                @elseif($domain === 'tech')
                                    <span class="bg-cyan-500/15 text-cyan-400 border border-cyan-500/30 px-2 py-0.5 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-code text-[9px]"></i> تقني
                                    </span>
                                @elseif($domain === 'linguistic')
                                    <span class="bg-purple-500/15 text-purple-400 border border-purple-500/30 px-2 py-0.5 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-pen-nib text-[9px]"></i> لغوي
                                    </span>
                                @elseif($domain === 'sharia')
                                    <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                        <i class="fa-solid fa-kaaba text-[9px]"></i> شرعي
                                    </span>
                                @elseif($user->expert_domain)
                                    <span class="bg-gray-500/15 text-gray-300 border border-gray-500/30 px-2 py-0.5 rounded-lg text-xs font-bold inline-block">
                                        {{ $user->expert_domain }}
                                    </span>
                                @else
                                    <span class="text-gray-500 text-xs">—</span>
                                @endif

                                @if($user->expert_specialization)
                                    <div class="text-[11px] text-gray-400 mt-0.5 max-w-[130px] truncate" title="{{ $user->expert_specialization }}">
                                        {{ $user->expert_specialization }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="p-4 whitespace-nowrap">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-xl text-xs font-bold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    {!! __('admin.active') !!}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-rose-500/15 text-rose-300 border border-rose-500/30 px-2.5 py-1 rounded-xl text-xs font-bold">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    {!! __('admin.suspended') !!}
                                </span>
                            @endif
                        </td>

                        {{-- AI Subscription Badge --}}
                        @php
                            $activeSub = $user->activeAiSubscription;
                        @endphp
                        <td class="p-4 whitespace-nowrap">
                            @if($activeSub)
                                @if($activeSub->isLifetime())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 shadow-sm">
                                        👑 {{ $activeSub->package->name ?? 'مدى الحياة' }} ♾️
                                    </span>
                                @elseif($activeSub->isEffectivelyUnlimited())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-sm">
                                        ⚡ {{ $activeSub->package->name ?? 'غير محدود' }} ♾️
                                    </span>
                                @else
                                    <div class="inline-flex flex-col gap-0.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-xl text-xs font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30">
                                            ✅ {{ $activeSub->package->name ?? 'باقة نشطة' }}
                                        </span>
                                        <span class="text-[10px] text-gray-500 font-mono">حتى {{ $activeSub->ends_at?->format('Y-m-d') ?? '—' }}</span>
                                    </div>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-medium bg-gray-500/10 text-gray-400 border border-gray-500/20">
                                    مجاني
                                </span>
                            @endif
                        </td>

                        {{-- Joined At --}}
                        <td class="p-4 whitespace-nowrap">
                            <div class="text-white text-xs font-semibold">{{ $user->created_at->format('M d, Y') }}</div>
                            <span class="text-[10px] text-gray-500">{{ $user->created_at->diffForHumans() }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="p-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">

                                {{-- Grant AI Subscription --}}
                                <button type="button"
                                    onclick="openGrantModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    class="p-2 bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 rounded-lg transition"
                                    title="تفعيل باقة ذكاء اصطناعي">
                                    <i class="fa-solid fa-bolt text-xs"></i>
                                </button>

                                {{-- Impersonate --}}
                                @if(auth()->user()->role === 'superadmin' && $user->role !== 'superadmin' && $user->is_active)
                                    <form action="{{ route('admin.impersonate.start', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="p-2 bg-purple-500/10 text-purple-400 border border-purple-500/20 hover:bg-purple-500/20 rounded-lg transition"
                                            title="تسجيل الدخول بحساب المستخدم">
                                            <i class="fa-solid fa-user-secret text-xs"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Toggle Suspend/Activate --}}
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="p-2 {{ $user->is_active ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' }} border rounded-lg transition"
                                        title="{{ $user->is_active ? __('admin.suspend_user') : __('admin.reactivate_user') }}"
                                        onclick="return confirm('{{ $user->is_active ? __('admin.suspend_confirm') : __('admin.reactivate_confirm') }}')">
                                        <i class="fa-solid fa-{{ $user->is_active ? 'ban' : 'check' }} text-xs"></i>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-2 bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500/20 rounded-lg transition"
                                        title="{{ __('admin.delete_perm') }}"
                                        onclick="return confirm('{{ __('admin.delete_confirm') }}')">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <span class="w-14 h-14 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center text-2xl text-gray-600">
                                    <i class="fa-solid fa-users-slash"></i>
                                </span>
                                <span class="text-sm font-bold text-gray-400">{!! __('admin.no_users_found') !!}</span>
                                <span class="text-xs text-gray-600">{!! __('admin.adjust_search') !!}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="p-4 border-t border-dark-border bg-white/[0.01]">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     نافذة منبثقة: تفعيل باقة ذكاء اصطناعي لمستخدم (Grant AI Package Modal)
     ═══════════════════════════════════════════════════════════════════ --}}
<div id="grant-ai-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4"
     onclick="if(event.target===this)closeGrantModal()">

    <div class="bg-slate-900 border border-white/10 shadow-2xl rounded-3xl w-full max-w-md p-6 sm:p-8 relative overflow-hidden backdrop-blur-2xl"
         id="grant-ai-modal-box">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-dark-border">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-bolt text-lg"></i>
                </div>
                <div>
                    <h2 class="font-black text-white text-base">تفعيل باقة ذكاء اصطناعي</h2>
                    <p class="text-gray-400 text-xs mt-0.5">للمستخدم: <span id="grant-user-name" class="font-bold text-teal-400"></span></p>
                </div>
            </div>
            <button onclick="closeGrantModal()" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <form id="grant-ai-form"
              method="POST"
              action=""
              class="space-y-4 text-right">
            @csrf

            {{-- اختيار الباقة --}}
            <div>
                <label class="block text-xs font-bold text-gray-300 mb-1.5">
                    <i class="fa-solid fa-box-open text-amber-400 me-1"></i> اختر الباقة
                </label>
                <select name="ai_package_id" required
                        class="w-full bg-dark-navy/60 border border-dark-border text-white rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}">
                            {{ $pkg->name }}
                            @if($pkg->billing_period === 'lifetime') (مدى الحياة)
                            @elseif($pkg->billing_period === 'monthly') (شهري)
                            @elseif($pkg->billing_period === 'yearly') (سنوي)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- مدة التفعيل --}}
            <div>
                <label class="block text-xs font-bold text-gray-300 mb-1.5">
                    <i class="fa-solid fa-calendar-days text-teal-400 me-1"></i> مدة التفعيل
                </label>
                <select name="duration_type" required
                        class="w-full bg-dark-navy/60 border border-dark-border text-white rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 transition">
                    <option value="1_month">شهر واحد (1 Month)</option>
                    <option value="3_months">3 أشهر (3 Months)</option>
                    <option value="6_months">6 أشهر (6 Months)</option>
                    <option value="1_year">سنة كاملة (1 Year)</option>
                    <option value="lifetime" class="font-bold text-amber-400">👑 مدى الحياة (Lifetime)</option>
                </select>
            </div>

            {{-- ملاحظات إدارية --}}
            <div>
                <label class="block text-xs font-bold text-gray-300 mb-1.5">
                    <i class="fa-solid fa-note-sticky text-gray-400 me-1"></i> ملاحظة إدارية (اختياري)
                </label>
                <textarea name="notes" rows="2"
                          placeholder="مثال: منحة تجريبية، عميل VIP..."
                          class="w-full bg-dark-navy/60 border border-dark-border text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 transition"></textarea>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 py-3 rounded-xl font-bold text-xs bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 transition shadow-lg shadow-amber-500/20 active:scale-95 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-bolt text-xs"></i>
                    تفعيل الباقة الآن
                </button>
                <button type="button"
                        onclick="closeGrantModal()"
                        class="py-3 px-5 rounded-xl font-bold text-xs bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 transition active:scale-95">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openGrantModal(userId, userName) {
        const modal = document.getElementById('grant-ai-modal');
        const form  = document.getElementById('grant-ai-form');
        const nameSpan = document.getElementById('grant-user-name');

        form.action = `/admin/users/${userId}/grant-ai-subscription`;
        nameSpan.textContent = userName;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeGrantModal() {
        const modal = document.getElementById('grant-ai-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush

@endsection
