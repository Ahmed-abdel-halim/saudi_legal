@extends('layouts.admin')

@section('title', __('admin.financials') ?? 'Financials')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.financials') ?? 'Financial Summary' !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.financials_desc') ?? 'Monitor platform revenue, escrow payments, and expert payouts.' !!}</p>
    </div>
    
    <div class="flex gap-2 text-sm">
        <button class="bg-white border border-slate-200 text-slate-700 font-bold px-4 py-2.5 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-download text-slate-400"></i> Export CSV
        </button>
        <button class="bg-primary text-white font-bold px-4 py-2.5 rounded-xl hover:bg-primary/90 transition shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-money-bill-transfer"></i> Process Payouts
        </button>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border text-white rounded-2xl p-5 shadow-sm flex flex-col gap-1 relative overflow-hidden group bg-gradient-to-br from-slate-800 to-slate-900 border-slate-700">
        <div class="absolute -right-4 -top-8 opacity-[0.05] group-hover:opacity-[0.08] transition">
            <i class="fa-solid fa-chart-line text-9xl"></i>
        </div>
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">{!! __('admin.total_volume') ?? 'Total Volume (All Time)' !!}</span>
        <span class="text-3xl font-black relative z-10">${{ number_format($stats['total_volume'], 2) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.platform_revenue') ?? 'Platform Revenue (~15%)' !!}</span>
        <span class="text-3xl font-black text-emerald-600">${{ number_format($stats['platform_revenue'], 2) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.pending_payouts') ?? 'Pending Escrow' !!}</span>
        <span class="text-3xl font-black text-amber-500">${{ number_format($stats['pending_payouts'], 2) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.completed_payouts') ?? 'Completed Payouts' !!}</span>
        <span class="text-3xl font-black text-slate-800">${{ number_format($stats['completed_payouts'], 2) }}</span>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    
    {{-- Header & Filters --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-receipt text-primary rtl:ml-2"></i>
            <h3 class="text-lg font-bold text-slate-800">{!! __('admin.recent_transactions') ?? 'Recent Transactions' !!}</h3>
        </div>
        
        <div class="flex flex-wrap gap-2 text-sm">
            <button class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-medium">All</button>
            <button class="px-3 py-1.5 rounded-lg border border-transparent text-slate-500 hover:text-slate-800 font-medium">Escrow</button>
            <button class="px-3 py-1.5 rounded-lg border border-transparent text-slate-500 hover:text-slate-800 font-medium">Paid</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.trans_id') ?? 'Trx ID' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.date_col') ?? 'Date' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.provider_client') ?? 'From / To' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.amount') ?? 'Amount' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.status_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50/60 transition">
                        
                        {{-- ID & Type --}}
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800">{{ $trx['id'] }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $trx['type'] }}</div>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 text-xs text-slate-500">
                            {{ $trx['date']->format('M d, Y') }}
                            <div class="text-[10px] mt-0.5">{{ $trx['date']->format('h:i A') }}</div>
                        </td>

                        {{-- From / To --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-400 w-8 inline-block rtl:text-left">From:</span>
                                <span class="font-bold text-slate-700 truncate max-w-[150px]">{{ $trx['from'] }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs mt-1">
                                <span class="text-slate-400 w-8 inline-block rtl:text-left">To:</span>
                                <span class="font-medium text-slate-600 truncate max-w-[150px]">{{ $trx['to'] }}</span>
                            </div>
                        </td>

                        {{-- Amount --}}
                        <td class="px-5 py-4">
                            <span class="font-black text-slate-800 text-base">${{ number_format($trx['amount'], 2) }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if($trx['status'] === 'paid')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold me-2 px-2 py-0.5 rounded uppercase tracking-wider border border-emerald-200">Paid Out</span>
                            @elseif($trx['status'] === 'escrow')
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold me-2 px-2 py-0.5 rounded uppercase tracking-wider border border-amber-200">In Escrow</span>
                            @else
                                <span class="bg-slate-100 text-slate-800 text-[10px] font-bold me-2 px-2 py-0.5 rounded uppercase tracking-wider border border-slate-200">{{ $trx['status'] }}</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right rtl:text-left">
                            <button class="text-primary hover:text-indigo-800 font-bold text-sm bg-indigo-50 hover:bg-indigo-100 rounded-lg px-3 py-1.5 transition">
                                View Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                            <i class="fa-solid fa-receipt text-5xl mb-4 text-slate-200 block"></i>
                            <p class="font-bold text-lg text-slate-600">{!! __('admin.no_transactions_found') ?? 'No financial records found' !!}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
