@extends('layouts.admin')

@section('title', __('admin.services_board') ?? 'دليل الخدمات')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center text-lg shadow-glow flex-shrink-0">
                <i class="fa-solid fa-briefcase"></i>
            </span>
            <span>{!! __('admin.services_board') !!}</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">{!! __('admin.services_board_desc') ?? 'إدارة واعتماد خدمات الخبراء والقوائم النشطة وتفاصيل الأسعار بالساعة.' !!}</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    {{-- Total Services --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-teal-500/40 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-teal-500/5 rounded-full blur-xl pointer-events-none group-hover:bg-teal-500/10 transition"></div>
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-layer-group text-teal-400"></i> {!! __('admin.total_services') !!}
        </span>
        <span class="text-3xl font-black text-white font-mono mt-1">{{ number_format($stats['total']) }}</span>
        <span class="text-[11px] text-gray-500 font-medium">خدمة مسجلة بالنظام</span>
    </div>

    {{-- Active Services --}}
    <div class="bg-dark-card border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-emerald-500/50 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-emerald-500/15 transition"></div>
        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-circle-check"></i> {!! __('admin.active_services') !!}
        </span>
        <span class="text-3xl font-black text-emerald-400 font-mono mt-1">{{ number_format($stats['active']) }}</span>
        <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-400/80 font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> متاحة ومفعلة للطلب
        </span>
    </div>

    {{-- Inactive Services --}}
    <div class="bg-dark-card border border-rose-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-rose-500/50 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-rose-500/15 transition"></div>
        <span class="text-xs font-bold text-rose-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-pause"></i> {!! __('admin.inactive_services') !!}
        </span>
        <span class="text-3xl font-black text-rose-400 font-mono mt-1">{{ number_format($stats['inactive']) }}</span>
        <span class="text-[11px] text-rose-400/80 font-medium">متوقفة مؤقتاً أو معطلة</span>
    </div>

    {{-- Avg Hourly Rate --}}
    <div class="bg-dark-card border border-amber-500/30 rounded-2xl p-5 shadow-xl flex flex-col gap-1 relative overflow-hidden group hover:border-amber-500/50 transition">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-amber-500/15 transition"></div>
        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-coins"></i> {!! __('admin.avg_price') !!}
        </span>
        <div class="flex items-baseline gap-1 mt-1">
            <span class="text-3xl font-black text-amber-400 font-mono">{{ number_format($stats['avg_price'], 2) }}</span>
            <span class="text-xs font-bold text-amber-300/80 font-mono">ر.س / hr</span>
        </div>
        <span class="text-[11px] text-amber-400/80 font-medium">متوسط تسعير الساعة</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden mb-6">
    <div class="p-4 bg-white/[0.02]">
        <form action="{{ route('admin.services.index') }}" method="GET" class="flex flex-wrap gap-3 items-center">
            
            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{!! __('admin.search_services') ?? 'ابحث عن عنوان الخدمة، الوصف، أو اسم الخبير...' !!}"
                    class="w-full bg-dark-navy/60 border border-dark-border text-white text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 pl-9 rtl:pr-9 rtl:pl-3 py-2.5 transition outline-none">
            </div>

            {{-- Status Filter --}}
            <select name="status" class="bg-dark-navy/60 border border-dark-border text-gray-300 text-xs rounded-xl focus:ring-teal-500 focus:border-teal-500 py-2.5 px-3 transition min-w-[140px]" onchange="this.form.submit()">
                <option value="">{!! __('admin.all_statuses') ?? 'جميع الحالات' !!}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>✅ {!! __('admin.status_active') ?? 'نشطة فقط' !!}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>⏸️ {!! __('admin.status_inactive') ?? 'معطلة / غير نشطة' !!}</option>
            </select>

            <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-glow">
                <i class="fa-solid fa-filter"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.services.index') }}" class="bg-rose-500/10 text-rose-400 px-4 py-2.5 rounded-xl text-xs font-bold border border-rose-500/30 hover:bg-rose-500/20 transition whitespace-nowrap">
                    <i class="fa-solid fa-rotate-left"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="mr-auto rtl:mr-auto rtl:ml-0 text-xs text-gray-400 font-medium bg-white/[0.04] px-3 py-1.5 rounded-xl border border-white/5 font-mono">
                {{ $services->total() }} {{ __('admin.results_found') ?? 'خدمة' }}
            </span>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right text-sm">
            <thead class="bg-white/[0.02] border-b border-dark-border text-gray-400 text-xs uppercase font-bold tracking-wider">
                <tr>
                    <th class="p-4">{!! __('admin.service_title_col') ?? 'عنوان الخدمة' !!}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.expert_col') ?? 'الخبير المقدم' !!}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.hourly_rate_col') ?? 'السعر بالساعة' !!}</th>
                    <th class="p-4 whitespace-nowrap">{!! __('admin.status_col') !!}</th>
                    <th class="p-4 text-center whitespace-nowrap">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border text-gray-300 text-xs">
                @forelse($services as $service)
                    <tr class="hover:bg-white/[0.03] transition {{ $service->is_active ? '' : 'opacity-60 bg-red-500/[0.02]' }}">
                        
                        {{-- Title & Thumbnail --}}
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                @if($service->image_url)
                                    <img src="{{ $service->image_url }}" class="w-12 h-12 rounded-xl object-cover border border-white/10 shadow-sm flex-shrink-0" alt="{{ $service->title }}">
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500/20 to-indigo-500/20 border border-teal-500/30 flex items-center justify-center text-teal-400 text-base shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('services.show', $service->service_id) }}" target="_blank" class="font-bold text-white text-xs sm:text-sm hover:text-teal-400 transition block truncate max-w-xs md:max-w-md" title="{{ $service->title }}">
                                        {{ $service->title }}
                                    </a>
                                    @if($service->description)
                                        <p class="text-gray-400 text-[11px] line-clamp-1 max-w-xs md:max-w-md mt-0.5">
                                            {{ Str::limit(strip_tags($service->description), 90) }}
                                        </p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-1 text-[10px] text-gray-500 font-mono">
                                        <span>#{{ $service->service_id }}</span>
                                        <span>•</span>
                                        <span>{{ $service->created_at?->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Expert --}}
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                @if($service->expert && $service->expert->avatar_url)
                                    <img src="{{ $service->expert->avatar_url }}" class="w-9 h-9 rounded-full object-cover border border-white/10 shadow-sm flex-shrink-0" alt="{{ $service->expert->name }}">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500/30 to-purple-500/30 border border-indigo-500/40 text-indigo-300 flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                                        {{ mb_substr($service->expert->name ?? 'خ', 0, 2) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-white text-xs truncate max-w-[150px]">
                                        {{ $service->expert->name ?? 'غير محدد' }}
                                    </div>
                                    @if($service->expert && $service->expert->email)
                                        <div class="text-[11px] text-gray-400 font-mono truncate max-w-[150px]">
                                            {{ $service->expert->email }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Hourly Rate --}}
                        <td class="p-4 whitespace-nowrap">
                            <span class="inline-flex items-baseline gap-1 bg-amber-500/10 border border-amber-500/25 px-3 py-1.5 rounded-xl text-amber-300 font-mono font-bold text-xs shadow-sm">
                                <span class="text-sm font-black">{{ number_format($service->price, 2) }}</span>
                                <span class="text-[10px] text-amber-400/80 font-medium">ر.س / hr</span>
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="p-4 whitespace-nowrap">
                            @if($service->is_active)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    {!! __('admin.status_active') ?? 'نشط' !!}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                    {!! __('admin.status_suspended') ?? 'معطل' !!}
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="p-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Preview on Frontend --}}
                                <a href="{{ route('services.show', $service->service_id) }}" target="_blank"
                                   class="w-8 h-8 rounded-xl bg-white/[0.04] border border-white/10 hover:border-teal-500/50 hover:bg-teal-500/10 text-gray-400 hover:text-teal-400 flex items-center justify-center transition shadow-sm"
                                   title="{!! __('admin.preview') ?? 'معاينة في الموقع' !!}">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                </a>

                                {{-- Toggle Active Status --}}
                                <form action="{{ route('admin.services.toggle-status', $service->service_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-xl {{ $service->is_active ? 'bg-amber-500/10 border border-amber-500/30 text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20' }} flex items-center justify-center transition shadow-sm"
                                        title="{{ $service->is_active ? __('admin.suspend') ?? 'إيقاف الخدمة' : __('admin.activate') ?? 'تفعيل الخدمة' }}"
                                        onclick="return confirm('{{ $service->is_active ? 'هل تريد إيقاف وتعطيل هذه الخدمة مؤقتاً؟' : 'هل تريد إعادة تفعيل هذه الخدمة؟' }}')">
                                        <i class="fa-solid {{ $service->is_active ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                    </button>
                                </form>

                                {{-- Delete Service --}}
                                <form action="{{ route('admin.services.destroy', $service->service_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 flex items-center justify-center transition shadow-sm"
                                        title="{!! __('admin.delete') !!}"
                                        onclick="return confirm('{{ __('admin.confirm_delete_service') ?? 'هل أنت متأكد من حذف هذه الخدمة نهائياً من المنصة؟' }}')">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500">
                            <div class="w-16 h-16 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center text-2xl mx-auto mb-3 text-gray-600">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <p class="font-bold text-base text-gray-300">{!! __('admin.no_services_found') ?? 'لا توجد خدمات مطابقة للبحث' !!}</p>
                            <p class="text-xs text-gray-500 mt-1">{!! __('admin.adjust_search') ?? 'جرّب البحث بكلمات أخرى أو إعادة ضبط الفلاتر' !!}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($services->hasPages())
    <div class="p-4 border-t border-dark-border bg-white/[0.02] flex items-center justify-between gap-4">
        <span class="text-xs text-gray-400 font-mono">
            {{ __('admin.showing') ?? 'عرض' }} {{ $services->firstItem() }}–{{ $services->lastItem() }} {{ __('admin.of') ?? 'من' }} {{ $services->total() }}
        </span>
        {{ $services->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
