@extends('layouts.admin')

@section('title', __('admin.companies') ?? 'الشركات')

@section('content')

@php
    $industryMap = [
        'tech' => ['name' => 'تقنية المعلومات', 'icon' => 'fa-laptop-code', 'badge' => 'bg-teal-500/10 text-teal-400 border-teal-500/25'],
        'technology' => ['name' => 'تقنية المعلومات', 'icon' => 'fa-laptop-code', 'badge' => 'bg-teal-500/10 text-teal-400 border-teal-500/25'],
        'law' => ['name' => 'القانون والمحاماة', 'icon' => 'fa-scale-balanced', 'badge' => 'bg-amber-500/10 text-amber-400 border-amber-500/25'],
        'insurance' => ['name' => 'التأمين', 'icon' => 'fa-shield-halved', 'badge' => 'bg-blue-500/10 text-blue-400 border-blue-500/25'],
        'finance' => ['name' => 'المالية والاستثمار', 'icon' => 'fa-coins', 'badge' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25'],
        'healthcare' => ['name' => 'الرعاية الصحية', 'icon' => 'fa-heart-pulse', 'badge' => 'bg-rose-500/10 text-rose-400 border-rose-500/25'],
        'marketing' => ['name' => 'التسويق الرقمي', 'icon' => 'fa-bullhorn', 'badge' => 'bg-purple-500/10 text-purple-400 border-purple-500/25'],
        'services' => ['name' => 'خدمات الأعمال', 'icon' => 'fa-briefcase', 'badge' => 'bg-sky-500/10 text-sky-400 border-sky-500/25'],
        'other' => ['name' => 'مجالات متنوعة', 'icon' => 'fa-shapes', 'badge' => 'bg-gray-500/10 text-gray-400 border-gray-500/25'],
    ];

    $sizeMap = [
        'large' => 'كبيرة (100+ موظف)',
        'medium' => 'متوسطة (10-50 موظف)',
        'small' => 'صغيرة (1-10 موظفين)',
    ];
@endphp

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center text-lg shadow-glow flex-shrink-0">
                <i class="fa-solid fa-building"></i>
            </span>
            <span>{!! __('admin.companies') !!}</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">{!! __('admin.companies_desc') ?? 'إدارة وتوثيق جميع الشركات المسجلة على المنصة والتحقق من السجلات والأنشطة.' !!}</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    {{-- Total Companies --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-blue-500/40 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-500/5 rounded-full blur-xl pointer-events-none group-hover:bg-blue-500/10 transition"></div>
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-building text-blue-400"></i> {{ __('admin.total') ?? 'إجمالي الشركات' }}
        </span>
        <span class="text-3xl font-black text-white font-mono mt-1">{{ number_format($totalCompanies) }}</span>
        <span class="text-[11px] text-gray-500 font-medium">شركة مسجلة بالنظام</span>
    </div>

    {{-- Verified Companies --}}
    <div class="bg-dark-card border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-emerald-500/50 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-emerald-500/15 transition"></div>
        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-certificate text-emerald-400"></i> {{ __('admin.verified') ?? 'شركات موثقة' }}
        </span>
        <span class="text-3xl font-black text-emerald-400 font-mono mt-1">{{ number_format($verifiedCount) }}</span>
        <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-400/80 font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> موثقة ومؤكدة السجل
        </span>
    </div>

    {{-- Requesters --}}
    <div class="bg-dark-card border border-cyan-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-cyan-500/50 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-cyan-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-cyan-500/15 transition"></div>
        <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-user-tie text-cyan-400"></i> {{ __('admin.requesters') ?? 'طالبي الخدمات' }}
        </span>
        <span class="text-3xl font-black text-cyan-400 font-mono mt-1">{{ number_format($requesterCount) }}</span>
        <span class="text-[11px] text-cyan-400/80 font-medium">يبحثون عن خدمات وخبراء</span>
    </div>

    {{-- Suppliers --}}
    <div class="bg-dark-card border border-indigo-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-indigo-500/50 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-indigo-500/15 transition"></div>
        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-store text-indigo-400"></i> {{ __('admin.suppliers') ?? 'مقدمي الخدمات' }}
        </span>
        <span class="text-3xl font-black text-indigo-400 font-mono mt-1">{{ number_format($supplierCount) }}</span>
        <span class="text-[11px] text-indigo-400/80 font-medium">موردي خدمات وحلول</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden mb-6">
    <div class="p-4 bg-white/[0.02]">
        <form method="GET" action="{{ route('admin.companies.index') }}" class="flex flex-wrap gap-3 items-center">
            
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_companies') ?? 'البحث بالاسم، السجل التجاري، أو الصناعة...' }}"
                    class="w-full bg-dark-navy/60 border border-dark-border text-white text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 pl-9 rtl:pr-9 rtl:pl-3 py-2.5 transition outline-none">
            </div>

            {{-- Type Filter --}}
            <select name="type" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[150px]">
                <option value="">{{ __('admin.all_types') ?? 'جميع الأنواع' }}</option>
                <option value="requester" {{ request('type') == 'requester' ? 'selected' : '' }}>👤 {{ __('admin.requesters') ?? 'طالبي الخدمات' }}</option>
                <option value="supplier"  {{ request('type') == 'supplier'  ? 'selected' : '' }}>🏬 {{ __('admin.suppliers')  ?? 'مقدمي الخدمات'  }}</option>
            </select>

            <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                <i class="fa-solid fa-filter"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'type', 'status']))
                <a href="{{ route('admin.companies.index') }}" class="bg-rose-500/10 text-rose-400 px-4 py-2.5 rounded-xl text-xs font-bold border border-rose-500/30 hover:bg-rose-500/20 transition whitespace-nowrap">
                    <i class="fa-solid fa-rotate-left"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="mr-auto rtl:mr-auto rtl:ml-0 text-xs text-gray-400 font-medium bg-white/[0.04] px-3 py-1.5 rounded-xl border border-white/5 font-mono">
                {{ $companies->total() }} {{ __('admin.results_found') ?? 'شركة' }}
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
                    <th class="p-4">{{ __('admin.company_col') ?? 'الشركة' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.industry_col') ?? 'الصناعة / المجال' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.cr_col') ?? 'رقم السجل التجاري' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.type_col') ?? 'نوع الشركة' }}</th>
                    <th class="p-4 whitespace-nowrap">{{ __('admin.verified_col') ?? 'حالة التوثيق' }}</th>
                    <th class="p-4 text-center whitespace-nowrap">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border text-gray-300 text-xs">
                @forelse($companies as $company)
                <tr class="hover:bg-white/[0.03] transition">
                    
                    {{-- Company Info --}}
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-white/[0.04] border border-white/10 flex items-center justify-center text-teal-400 flex-shrink-0 overflow-hidden shadow-sm">
                                @if($company->company_logo)
                                    <img src="{{ asset('uploads/' . $company->company_logo) }}" alt="{{ $company->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-building text-base text-gray-400"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-white text-xs sm:text-sm truncate max-w-[200px]" title="{{ $company->name }}">
                                    {{ $company->name }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1.5 font-mono">
                                    <span>
                                        @if($company->size)
                                            {{ $sizeMap[strtolower($company->size)] ?? ($company->size . ' موظف') }}
                                        @else
                                            الحجم: غير محدد
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Industry --}}
                    <td class="p-4 whitespace-nowrap">
                        @php
                            $indKey = strtolower(trim($company->industry ?? ''));
                            $indInfo = $industryMap[$indKey] ?? null;
                        @endphp
                        @if($indInfo)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $indInfo['badge'] }}">
                                <i class="fa-solid {{ $indInfo['icon'] }} text-[11px]"></i>
                                {{ $indInfo['name'] }}
                            </span>
                        @elseif($company->industry)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-white/[0.04] text-gray-300 border border-white/10">
                                <i class="fa-solid fa-briefcase text-[11px] text-gray-400"></i>
                                {{ $company->industry }}
                            </span>
                        @else
                            <span class="text-gray-500 font-mono">—</span>
                        @endif
                    </td>

                    {{-- CR Number --}}
                    <td class="p-4 whitespace-nowrap">
                        @if($company->cr_number)
                            <span class="inline-flex items-center gap-1.5 font-mono text-xs text-cyan-300 bg-cyan-500/10 border border-cyan-500/20 px-2.5 py-1 rounded-lg shadow-sm">
                                <i class="fa-regular fa-file-lines text-[10px] text-cyan-400/70"></i>
                                {{ $company->cr_number }}
                            </span>
                        @else
                            <span class="text-gray-500 font-mono">—</span>
                        @endif
                    </td>

                    {{-- Type Tags --}}
                    <td class="p-4 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            @if($company->is_requester)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 px-2.5 py-1 rounded-lg">
                                    <i class="fa-solid fa-user-tie text-[10px]"></i>
                                    {{ __('admin.requester') ?? 'طالب خدمة' }}
                                </span>
                            @endif
                            @if($company->is_supplier)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2.5 py-1 rounded-lg">
                                    <i class="fa-solid fa-store text-[10px]"></i>
                                    {{ __('admin.supplier') ?? 'مقدم خدمة' }}
                                </span>
                            @endif
                            @if(!$company->is_requester && !$company->is_supplier)
                                <span class="text-gray-500 font-mono">—</span>
                            @endif
                        </div>
                    </td>

                    {{-- Verified Badge --}}
                    <td class="p-4 whitespace-nowrap">
                        @if($company->is_verified_provider)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <i class="fa-solid fa-certificate text-[11px]"></i> {{ __('admin.verified') ?? 'موثق' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-500/15 text-gray-400 border border-gray-500/30 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                <i class="fa-regular fa-circle text-[11px]"></i> {{ __('admin.unverified') ?? 'غير موثق' }}
                            </span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="p-4 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1.5">
                            
                            {{-- Impersonate First User --}}
                            @if(auth()->user()?->role === 'superadmin' && $company->users->count() > 0)
                                @php $companyUser = $company->users->first(); @endphp
                                @if($companyUser && $companyUser->is_active)
                                    <form action="{{ route('admin.impersonate.start', $companyUser->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 hover:bg-amber-500/20 flex items-center justify-center transition shadow-sm"
                                            title="تسجيل الدخول بحساب الشركة (Impersonate)">
                                            <i class="fa-solid fa-user-secret text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- Toggle Verify --}}
                            <form action="{{ route('admin.companies.toggle-verified', $company->company_id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-8 h-8 rounded-xl {{ $company->is_verified_provider ? 'bg-amber-500/10 border border-amber-500/30 text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20' }} flex items-center justify-center transition shadow-sm"
                                    title="{{ $company->is_verified_provider ? 'إلغاء التوثيق' : 'توثيق الشركة' }}"
                                    onclick="return confirm('{{ $company->is_verified_provider ? 'هل تريد إلغاء توثيق شركة ' . addslashes($company->name) . '؟' : 'هل تريد توثيق شركة ' . addslashes($company->name) . ' رسمياً؟' }}')">
                                    <i class="fa-solid fa-{{ $company->is_verified_provider ? 'ban' : 'check' }} text-xs"></i>
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('admin.companies.destroy', $company->company_id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 flex items-center justify-center transition shadow-sm"
                                    title="{!! __('admin.delete') !!}"
                                    onclick="return confirm('هل أنت متأكد من حذف شركة {{ addslashes($company->name) }} نهائياً من المنصة؟')">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-12 text-center text-gray-500">
                        <div class="w-16 h-16 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center text-2xl mx-auto mb-3 text-gray-600">
                            <i class="fa-solid fa-building-circle-xmark"></i>
                        </div>
                        <p class="font-bold text-base text-gray-300">{{ __('admin.no_companies_found') ?? 'لا توجد شركات مطابقة للبحث' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{!! __('admin.adjust_search') ?? 'جرّب البحث بكلمات أخرى أو إعادة ضبط الفلاتر' !!}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($companies->hasPages())
        <div class="p-4 border-t border-dark-border bg-white/[0.02] flex items-center justify-between gap-4">
            <span class="text-xs text-gray-400 font-mono">
                {{ __('admin.showing') ?? 'عرض' }} {{ $companies->firstItem() }}–{{ $companies->lastItem() }} {{ __('admin.of') ?? 'من' }} {{ $companies->total() }}
            </span>
            {{ $companies->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
