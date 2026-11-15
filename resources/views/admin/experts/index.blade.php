@extends('layouts.admin')

@section('title', __('admin.experts') ?? 'الخبراء المعتمدون')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center text-lg shadow-glow">
                <i class="fa-solid fa-user-tie"></i>
            </span>
            <span>{!! __('admin.experts') !!}</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">{{ __('admin.experts_desc') ?? 'إدارة الخبراء المسجلين ومتابعة دقة التدقيق والأداء والإنتاجية.' }}</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    {{-- Total Experts --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-users text-teal-400"></i> {{ __('admin.total') ?? 'إجمالي الخبراء' }}
        </span>
        <span class="text-3xl font-black text-white">{{ number_format($totalExperts) }}</span>
    </div>

    {{-- Active Experts --}}
    <div class="bg-dark-card border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-circle-check"></i> {!! __('admin.active') !!}
        </span>
        <span class="text-3xl font-black text-emerald-400">{{ number_format($activeExperts) }}</span>
    </div>

    {{-- For Hire --}}
    <div class="bg-dark-card border border-indigo-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-briefcase"></i> {{ __('admin.for_hire') ?? 'متاحون للتعاقد' }}
        </span>
        <span class="text-3xl font-black text-indigo-400">{{ number_format($forHireExperts) }}</span>
    </div>

    {{-- Audited Tasks --}}
    <div class="bg-dark-card border border-purple-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group">
        <span class="text-xs font-bold text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-scale-balanced"></i> {{ __('admin.audited_tasks') ?? 'المهام المدققة' }}
        </span>
        <span class="text-3xl font-black text-purple-400">{{ number_format($totalAuditedTasks) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden mb-8">
    <div class="p-4 bg-white/[0.02]">
        <form method="GET" action="{{ route('admin.experts.index') }}" class="flex flex-wrap gap-3 items-center">
            
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_experts') ?? 'البحث بالاسم، البريد الإلكتروني، أو المجال...' }}"
                    class="w-full bg-dark-navy/60 border border-dark-border text-white text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 pl-9 rtl:pr-9 rtl:pl-3 py-2.5 transition outline-none">
            </div>

            {{-- Domain Filter --}}
            @if($domains->isNotEmpty())
            <select name="domain" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[150px]">
                <option value="">{{ __('admin.all_domains') ?? 'جميع المجالات' }}</option>
                @foreach($domains as $domain)
                    <option value="{{ $domain }}" {{ request('domain') == $domain ? 'selected' : '' }}>
                        @if($domain === 'law') ⚖️ قانوني
                        @elseif($domain === 'tech') 💻 تقني
                        @elseif($domain === 'linguistic') ✍️ لغوي
                        @elseif($domain === 'sharia') 🕌 شرعي
                        @else {{ $domain }}
                        @endif
                    </option>
                @endforeach
            </select>
            @endif

            {{-- Hire Status --}}
            <select name="hire" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[150px]">
                <option value="">{{ __('admin.all_hire_status') ?? 'الكل (حالة التوظيف)' }}</option>
                <option value="yes" {{ request('hire') == 'yes' ? 'selected' : '' }}>{{ __('admin.for_hire') ?? 'متاح للعمل' }}</option>
                <option value="no"  {{ request('hire') == 'no'  ? 'selected' : '' }}>{{ __('admin.not_for_hire') ?? 'غير متاح' }}</option>
            </select>

            {{-- Status --}}
            <select name="status" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[130px]">
                <option value="">{!! __('admin.all_statuses') ?? 'جميع الحالات' !!}</option>
                <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>{!! __('admin.active') !!}</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{!! __('admin.suspended') !!}</option>
            </select>

            <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                <i class="fa-solid fa-filter"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'domain', 'hire', 'status']))
                <a href="{{ route('admin.experts.index') }}" class="bg-red-500/10 text-red-400 px-4 py-2.5 rounded-xl text-xs font-bold border border-red-500/30 hover:bg-red-500/20 transition">
                    <i class="fa-solid fa-rotate-left"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="mr-auto rtl:mr-auto rtl:ml-0 text-xs text-gray-400 font-medium bg-white/[0.04] px-3 py-1.5 rounded-xl border border-white/5">
                {{ $experts->total() }} {{ __('admin.results_found') ?? 'خبير' }}
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
                    <th class="p-4">{!! __('admin.user_details_col') ?? 'بيانات الخبير' !!}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.domain_col') ?? 'المجال / التخصص' !!}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.trust_score_col') ?? 'درجة الثقة' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.performance') ?? 'الأداء والتدقيق' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.hire_col') ?? 'حالة التوظيف' }}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.status_col') !!}</th>
                    <th class="p-4 text-center whitespace-nowrap">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border text-gray-300 text-xs">
                @forelse($experts as $expert)
                <tr class="hover:bg-white/[0.03] transition {{ $expert->is_active ? '' : 'opacity-60 bg-red-500/[0.02]' }}">

                    {{-- Expert Info --}}
                    <td class="p-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                @if($expert->avatar_url)
                                    <img class="w-10 h-10 rounded-full object-cover border border-white/10 shadow-sm"
                                         src="{{ $expert->avatar_url }}" alt="{{ $expert->name }}">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500/30 to-indigo-500/30 border border-teal-500/40 text-teal-300 flex items-center justify-center font-bold text-xs shadow-sm">
                                        {{ mb_substr($expert->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-dark-card {{ $expert->is_active ? 'bg-emerald-500 shadow-green-glow' : 'bg-gray-500' }}" title="{{ $expert->is_active ? 'نشط' : 'غير نشط' }}"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-white text-xs truncate max-w-[180px]">{{ $expert->name }}</div>
                                <div class="text-[11px] text-gray-400 truncate max-w-[180px] mt-0.5">
                                    <a href="mailto:{{ $expert->email }}" class="text-teal-400 hover:underline flex items-center gap-1">
                                        <i class="fa-regular fa-envelope text-[10px]"></i> {{ $expert->email }}
                                    </a>
                                </div>
                                @if($expert->job_title)
                                    <div class="text-[10px] text-gray-500 mt-0.5 italic truncate max-w-[180px]">{{ $expert->job_title }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Domain / Specialization --}}
                    <td class="p-4 whitespace-nowrap">
                        @php
                            $domain = strtolower($expert->expert_domain ?? '');
                        @endphp
                        <div>
                            @if($domain === 'law')
                                <span class="bg-blue-500/15 text-blue-400 border border-blue-500/30 px-2.5 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-scale-balanced text-[10px]"></i> قانوني
                                </span>
                            @elseif($domain === 'tech')
                                <span class="bg-cyan-500/15 text-cyan-400 border border-cyan-500/30 px-2.5 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-code text-[10px]"></i> تقني
                                </span>
                            @elseif($domain === 'linguistic')
                                <span class="bg-purple-500/15 text-purple-400 border border-purple-500/30 px-2.5 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-pen-nib text-[10px]"></i> لغوي
                                </span>
                            @elseif($domain === 'sharia')
                                <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-2.5 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-kaaba text-[10px]"></i> شرعي
                                </span>
                            @elseif($expert->expert_domain)
                                <span class="bg-gray-500/15 text-gray-300 border border-gray-500/30 px-2.5 py-1 rounded-lg text-xs font-bold inline-block">
                                    {{ $expert->expert_domain }}
                                </span>
                            @else
                                <span class="text-gray-500 text-xs">—</span>
                            @endif

                            @if($expert->expert_specialization)
                                <div class="text-[11px] text-gray-400 mt-1 max-w-[140px] truncate" title="{{ $expert->expert_specialization }}">
                                    {{ $expert->expert_specialization }}
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- Trust Score --}}
                    <td class="p-4 whitespace-nowrap">
                        @php 
                            $trust = floatval($expert->trust_score ?? 0); 
                            $isHigh = $trust >= 70;
                            $isMed = $trust >= 40 && $trust < 70;
                            $colorClass = $isHigh ? 'emerald' : ($isMed ? 'amber' : 'rose');
                        @endphp
                        <div class="flex items-center gap-2.5">
                            <div class="w-16 bg-white/5 border border-white/10 rounded-full h-2 overflow-hidden">
                                <div class="bg-{{ $colorClass }}-500 h-2 rounded-full transition-all" style="width: {{ min($trust, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-mono font-bold text-{{ $colorClass }}-400">
                                {{ number_format($trust, 1) }}%
                            </span>
                        </div>
                    </td>

                    {{-- Auditing & Performance (Modern 2x2 Mini Grid) --}}
                    <td class="p-4 whitespace-nowrap">
                        <div class="grid grid-cols-2 gap-1.5 max-w-[210px]">
                            {{-- Audited Tasks --}}
                            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg px-2 py-1 flex items-center justify-between" title="إجمالي الأسئلة المدققة">
                                <span class="text-[10px] text-blue-400 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-scale-balanced text-[9px]"></i> المدققة
                                </span>
                                <span class="text-xs font-black text-white ms-1">{{ number_format($expert->legal_tasks_count) }}</span>
                            </div>

                            {{-- Correct Tasks --}}
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-2 py-1 flex items-center justify-between" title="اختبارات صائبة">
                                <span class="text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> صائبة
                                </span>
                                <span class="text-xs font-black text-white ms-1">{{ number_format($expert->gold_tasks_completed) }}</span>
                            </div>

                            {{-- Failed Tasks --}}
                            <div class="bg-rose-500/10 border border-rose-500/20 rounded-lg px-2 py-1 flex items-center justify-between" title="اختبارات خاطئة">
                                <span class="text-[10px] text-rose-400 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> خاطئة
                                </span>
                                <span class="text-xs font-black text-white ms-1">{{ number_format($expert->gold_tasks_failed) }}</span>
                            </div>

                            {{-- Time Spent --}}
                            <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg px-2 py-1 flex items-center justify-between" title="الوقت المستغرق">
                                <span class="text-[10px] text-purple-400 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-clock text-[9px]"></i> الوقت
                                </span>
                                <span class="text-[10px] font-bold text-gray-300 font-mono ms-1">{{ $expert->total_time_spent ?: '0 ث' }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Hire Availability --}}
                    <td class="p-4 whitespace-nowrap">
                        @if($expert->is_active_for_hire)
                            <span class="inline-flex items-center gap-1.5 bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 px-2.5 py-1 rounded-xl text-xs font-bold shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                                {{ __('admin.for_hire') ?? 'متاح للعمل' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 bg-gray-500/10 text-gray-400 border border-gray-500/20 px-2.5 py-1 rounded-xl text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                {{ __('admin.not_for_hire') ?? 'غير متاح' }}
                            </span>
                        @endif
                    </td>

                    {{-- Account Status --}}
                    <td class="p-4 whitespace-nowrap">
                        @if($expert->is_active)
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

                    {{-- Actions --}}
                    <td class="p-4 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            
                            {{-- View Tasks History --}}
                            <a href="{{ route('admin.experts.tasks', $expert->id) }}" 
                                class="p-2 bg-blue-500/10 text-blue-400 border border-blue-500/20 hover:bg-blue-500/20 rounded-lg transition"
                                title="عرض تفاصيل وسجل إنجاز الخبير">
                                <i class="fa-solid fa-list-check text-xs"></i>
                            </a>

                            {{-- Impersonate Expert --}}
                            @if(auth()->user()->role === 'superadmin' && $expert->is_active)
                                <form action="{{ route('admin.impersonate.start', $expert->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="p-2 bg-purple-500/10 text-purple-400 border border-purple-500/20 hover:bg-purple-500/20 rounded-lg transition"
                                        title="تسجيل الدخول بحساب الخبير">
                                        <i class="fa-solid fa-user-secret text-xs"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- Toggle Status --}}
                            <form action="{{ route('admin.experts.toggle-status', $expert->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="p-2 {{ $expert->is_active ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' }} border rounded-lg transition"
                                    title="{{ $expert->is_active ? __('admin.suspend_user') : __('admin.reactivate_user') }}"
                                    onclick="return confirm('{{ $expert->is_active ? __('admin.suspend_confirm') : __('admin.reactivate_confirm') }}')">
                                    <i class="fa-solid fa-{{ $expert->is_active ? 'ban' : 'check' }} text-xs"></i>
                                </button>
                            </form>

                            {{-- Delete Expert --}}
                            <form action="{{ route('admin.experts.destroy', $expert->id) }}" method="POST" class="inline">
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
                    <td colspan="7" class="p-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <span class="w-14 h-14 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center text-2xl text-gray-600">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <span class="text-sm font-bold text-gray-400">{{ __('admin.no_experts_found') ?? 'لا يوجد خبراء مطابقين للبحث حالياً' }}</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($experts->hasPages())
    <div class="p-4 border-t border-dark-border bg-white/[0.01]">
        {{ $experts->links() }}
    </div>
    @endif
</div>

@endsection
