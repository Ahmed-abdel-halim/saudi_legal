@extends('layouts.admin')

@section('title', __('admin.experts') ?? 'Experts')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.experts') !!}</h1>
        <p class="text-slate-500 mt-1">{{ __('admin.experts_desc') ?? 'Manage all registered experts and their performance metrics.' }}</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('admin.total') ?? 'Total' }}</p>
        <p class="text-3xl font-black text-slate-800">{{ number_format($totalExperts) }}</p>
    </div>
    <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1">{!! __('admin.active') !!}</p>
        <p class="text-3xl font-black text-emerald-600">{{ number_format($activeExperts) }}</p>
    </div>
    <div class="bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">{{ __('admin.for_hire') ?? 'For Hire' }}</p>
        <p class="text-3xl font-black text-indigo-600">{{ number_format($forHireExperts) }}</p>
    </div>
</div>

{{-- Main Card --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-slate-100 bg-slate-50">
        <form method="GET" action="{{ route('admin.experts.index') }}" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_experts') ?? 'Search by name, email, or domain...' }}"
                    class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-primary focus:border-primary pl-9 rtl:pr-9 rtl:pl-3 p-2.5 outline-none transition">
            </div>

            {{-- Domain Filter --}}
            @if($domains->isNotEmpty())
            <select name="domain" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 min-w-[160px]">
                <option value="">{{ __('admin.all_domains') ?? 'All Domains' }}</option>
                @foreach($domains as $domain)
                    <option value="{{ $domain }}" {{ request('domain') == $domain ? 'selected' : '' }}>{{ $domain }}</option>
                @endforeach
            </select>
            @endif

            {{-- Hire Status --}}
            <select name="hire" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 min-w-[150px]">
                <option value="">{{ __('admin.all_hire_status') ?? 'All (Hire Status)' }}</option>
                <option value="yes" {{ request('hire') == 'yes' ? 'selected' : '' }}>{{ __('admin.for_hire') ?? 'For Hire' }}</option>
                <option value="no"  {{ request('hire') == 'no'  ? 'selected' : '' }}>{{ __('admin.not_for_hire') ?? 'Not for Hire' }}</option>
            </select>

            {{-- Status --}}
            <select name="status" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 min-w-[140px]">
                <option value="">{!! __('admin.all_statuses') ?? 'All Statuses' !!}</option>
                <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>{!! __('admin.active') !!}</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{!! __('admin.suspended') !!}</option>
            </select>

            <button type="submit" class="bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-primary/90 transition">
                <i class="fa-solid fa-filter me-1"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'domain', 'hire', 'status']))
                <a href="{{ route('admin.experts.index') }}" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-lg text-sm font-bold border border-red-100 hover:bg-red-100 transition">
                    <i class="fa-solid fa-xmark me-1"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="ml-auto text-xs text-slate-400 font-medium">
                {{ $experts->total() }} {{ __('admin.results_found') ?? 'results' }}
            </span>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.user_details_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.domain_col') ?? 'Domain' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.trust_score_col') ?? 'Trust Score' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.hire_col') ?? 'For Hire' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.status_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($experts as $expert)
                <tr class="hover:bg-slate-50/60 transition {{ $expert->is_active ? '' : 'opacity-60' }}">

                    {{-- Expert Info --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <img class="w-10 h-10 rounded-full object-cover border-2 border-slate-100 shadow-sm"
                                     src="{{ $expert->avatar_url }}" alt="{{ $expert->name }}">
                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white {{ $expert->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-800 truncate max-w-[160px]">{{ $expert->name }}</div>
                                <div class="text-xs text-slate-500 truncate max-w-[160px]">{{ $expert->email }}</div>
                                @if($expert->job_title)
                                    <div class="text-[11px] text-slate-400 mt-0.5 italic">{{ $expert->job_title }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Domain / Specialization --}}
                    <td class="px-5 py-4">
                        @if($expert->expert_domain || $expert->expert_specialization)
                            <div class="text-xs font-bold text-slate-700">{{ $expert->expert_domain ?? '—' }}</div>
                            @if($expert->expert_specialization)
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $expert->expert_specialization }}</div>
                            @endif
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Trust Score --}}
                    <td class="px-5 py-4">
                        @php $trust = $expert->trust_score ?? 0; @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-slate-100 rounded-full h-1.5">
                                <div class="bg-{{ $trust >= 70 ? 'emerald' : ($trust >= 40 ? 'amber' : 'red') }}-500 h-1.5 rounded-full" style="width: {{ min($trust, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-600">{{ $trust }}</span>
                        </div>
                    </td>

                    {{-- For Hire --}}
                    <td class="px-5 py-4">
                        @if($expert->is_active_for_hire)
                            <span class="text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-1 rounded-full">
                                <i class="fa-solid fa-circle-check me-1"></i>{{ __('admin.for_hire') ?? 'For Hire' }}
                            </span>
                        @else
                            <span class="text-[11px] font-bold bg-slate-50 text-slate-500 border border-slate-200 px-2 py-1 rounded-full">
                                {{ __('admin.not_for_hire') ?? 'Unavailable' }}
                            </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-4">
                        @if($expert->is_active)
                            <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-xs bg-emerald-50 w-fit px-2.5 py-1 rounded-full border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {!! __('admin.active') !!}
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 text-red-600 font-bold text-xs bg-red-50 w-fit px-2.5 py-1 rounded-full border border-red-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                {!! __('admin.suspended') !!}
                            </div>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-4 text-right rtl:text-left">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Impersonate Expert --}}
                            @if(auth()->user()->role === 'superadmin' && $expert->is_active)
                                <form action="{{ route('admin.impersonate.start', $expert->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                        class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-brand-primary hover:text-white hover:border-brand-primary hover:bg-brand-primary transition shadow-sm"
                                        title="Login as Expert">
                                        <i class="fa-solid fa-user-secret text-xs"></i>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.experts.toggle-status', $expert->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-{{ $expert->is_active ? 'amber' : 'emerald' }}-500 hover:border-{{ $expert->is_active ? 'amber' : 'emerald' }}-400 hover:bg-{{ $expert->is_active ? 'amber' : 'emerald' }}-50 transition shadow-sm"
                                    title="{{ $expert->is_active ? __('admin.suspend_user') : __('admin.reactivate_user') }}"
                                    onclick="return confirm('{{ $expert->is_active ? __('admin.suspend_confirm') : __('admin.reactivate_confirm') }}')">
                                    <i class="fa-solid fa-{{ $expert->is_active ? 'ban' : 'check' }} text-xs"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.experts.destroy', $expert->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-red-500 hover:border-red-400 hover:bg-red-50 transition shadow-sm"
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
                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                        <i class="fa-solid fa-user-tie text-5xl mb-4 text-slate-200 block"></i>
                        <p class="font-bold text-lg text-slate-600">{{ __('admin.no_experts_found') ?? 'No experts found' }}</p>
                        <p class="text-sm mt-1">{!! __('admin.adjust_search') !!}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($experts->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
            <span class="text-xs text-slate-500 font-medium">
                {{ __('admin.showing') ?? 'Showing' }} {{ $experts->firstItem() }}–{{ $experts->lastItem() }} {{ __('admin.of') ?? 'of' }} {{ $experts->total() }}
            </span>
            {{ $experts->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
