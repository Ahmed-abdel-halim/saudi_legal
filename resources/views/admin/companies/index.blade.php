@extends('layouts.admin')

@section('title', __('admin.companies') ?? 'Companies')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.companies') !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.companies_desc') ?? 'Manage and verify all registered companies on the platform.' !!}</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('admin.total') ?? 'Total' }}</p>
        <p class="text-3xl font-black text-slate-800">{{ number_format($totalCompanies) }}</p>
    </div>
    <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1">{{ __('admin.verified') ?? 'Verified' }}</p>
        <p class="text-3xl font-black text-emerald-600">{{ number_format($verifiedCount) }}</p>
    </div>
    <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">{{ __('admin.requesters') ?? 'Requesters' }}</p>
        <p class="text-3xl font-black text-blue-600">{{ number_format($requesterCount) }}</p>
    </div>
    <div class="bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm">
        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">{{ __('admin.suppliers') ?? 'Suppliers' }}</p>
        <p class="text-3xl font-black text-indigo-600">{{ number_format($supplierCount) }}</p>
    </div>
</div>

{{-- Main Card --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

    {{-- Filter Bar --}}
    <div class="p-4 border-b border-slate-100 bg-slate-50">
        <form method="GET" action="{{ route('admin.companies.index') }}" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_companies') ?? 'Search by name, CR, or industry...' }}"
                    class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-primary focus:border-primary pl-9 rtl:pr-9 rtl:pl-3 p-2.5 outline-none transition">
            </div>

            <select name="type" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 min-w-[150px]">
                <option value="">{{ __('admin.all_types') ?? 'All Types' }}</option>
                <option value="requester" {{ request('type') == 'requester' ? 'selected' : '' }}>{{ __('admin.requesters') ?? 'Requesters' }}</option>
                <option value="supplier"  {{ request('type') == 'supplier'  ? 'selected' : '' }}>{{ __('admin.suppliers')  ?? 'Suppliers'  }}</option>
            </select>

            <button type="submit" class="bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-primary/90 transition">
                <i class="fa-solid fa-filter me-1"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'type', 'status']))
                <a href="{{ route('admin.companies.index') }}" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-lg text-sm font-bold border border-red-100 hover:bg-red-100 transition">
                    <i class="fa-solid fa-xmark me-1"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="ml-auto text-xs text-slate-400 font-medium">
                {{ $companies->total() }} {{ __('admin.results_found') ?? 'results' }}
            </span>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.company_col') ?? 'Company' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.industry_col') ?? 'Industry' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.cr_col') ?? 'CR Number' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.type_col') ?? 'Type' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.verified_col') ?? 'Verified' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($companies as $company)
                <tr class="hover:bg-slate-50/60 transition">
                    {{-- Company Info --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 flex-shrink-0 overflow-hidden">
                                @if($company->company_logo)
                                    <img src="{{ asset('uploads/' . $company->company_logo) }}" alt="{{ $company->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-building text-lg"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-800 truncate max-w-[180px]">{{ $company->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $company->size ? 'Size: ' . $company->size : 'Size unknown' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Industry --}}
                    <td class="px-5 py-4 text-slate-600 text-xs font-medium">
                        {{ $company->industry ?? '—' }}
                    </td>

                    {{-- CR Number --}}
                    <td class="px-5 py-4">
                        @if($company->cr_number)
                            <span class="font-mono text-xs text-slate-600 bg-slate-50 px-2 py-1 rounded border border-slate-100">{{ $company->cr_number }}</span>
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Type Tags --}}
                    <td class="px-5 py-4">
                        <div class="flex flex-col gap-1">
                            @if($company->is_requester)
                                <span class="text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100 px-2 py-0.5 rounded w-fit">
                                    {{ __('admin.requester') ?? 'Requester' }}
                                </span>
                            @endif
                            @if($company->is_supplier)
                                <span class="text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-0.5 rounded w-fit">
                                    {{ __('admin.supplier') ?? 'Supplier' }}
                                </span>
                            @endif
                            @if(!$company->is_requester && !$company->is_supplier)
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </div>
                    </td>

                    {{-- Verified Badge --}}
                    <td class="px-5 py-4">
                        @if($company->is_verified_provider)
                            <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-xs bg-emerald-50 w-fit px-2.5 py-1 rounded-full border border-emerald-100">
                                <i class="fa-solid fa-circle-check"></i> {{ __('admin.verified') ?? 'Verified' }}
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 text-slate-500 font-bold text-xs bg-slate-50 w-fit px-2.5 py-1 rounded-full border border-slate-200">
                                <i class="fa-regular fa-circle"></i> {{ __('admin.unverified') ?? 'Unverified' }}
                            </div>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-4 text-right rtl:text-left">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Toggle Verify --}}
                            <form action="{{ route('admin.companies.toggle-verified', $company->company_id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:border-emerald-400 hover:bg-emerald-50 transition shadow-sm"
                                    title="{{ $company->is_verified_provider ? 'Remove Verification' : 'Verify Company' }}"
                                    onclick="return confirm('Toggle verification status for {{ addslashes($company->name) }}?')">
                                    <i class="fa-solid fa-{{ $company->is_verified_provider ? 'xmark' : 'check' }} text-xs"></i>
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('admin.companies.destroy', $company->company_id) }}" method="POST" class="inline-block">
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
                        <i class="fa-solid fa-building-circle-xmark text-5xl mb-4 text-slate-200 block"></i>
                        <p class="font-bold text-lg text-slate-600">{{ __('admin.no_companies_found') ?? 'No companies found' }}</p>
                        <p class="text-sm mt-1">{!! __('admin.adjust_search') !!}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($companies->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
            <span class="text-xs text-slate-500 font-medium">
                {{ __('admin.showing') ?? 'Showing' }} {{ $companies->firstItem() }}–{{ $companies->lastItem() }} {{ __('admin.of') ?? 'of' }} {{ $companies->total() }}
            </span>
            {{ $companies->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
