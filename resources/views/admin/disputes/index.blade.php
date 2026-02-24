@extends('layouts.admin')

@section('title', __('admin.disputes_center') ?? 'Disputes Center')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.disputes_center') ?? 'Disputes Center' !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.disputes_center_desc') ?? 'Manage and resolve active contract and service disputes.' !!}</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.total_disputes') ?? 'Total Disputes' !!}</span>
        <span class="text-3xl font-black text-slate-800">{{ $disputes->count() }}</span>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    
    {{-- Header & Filters --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-scale-balanced text-primary rtl:ml-2"></i>
            <h3 class="text-lg font-bold text-slate-800">{!! __('admin.active_disputes') ?? 'Active Disputes' !!}</h3>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.dispute_title_col') ?? 'Contract Title' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.type_col') ?? 'Type' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.expert_col') ?? 'Expert' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.company_col') ?? 'Company / Client' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.date_col') ?? 'Created Date' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($disputes as $dispute)
                    <tr class="hover:bg-slate-50/60 transition">
                        
                        {{-- Title --}}
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800 truncate max-w-[200px]" title="{{ $dispute['title'] }}">
                                {{ $dispute['title'] }}
                            </div>
                        </td>

                        {{-- Type Badge --}}
                        <td class="px-5 py-4">
                            @if($dispute['type'] === 'offer')
                                <span class="px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-bold uppercase tracking-wider">Project</span>
                            @else
                                <span class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px] font-bold uppercase tracking-wider">Hourly</span>
                            @endif
                        </td>

                        {{-- Expert --}}
                        <td class="px-5 py-4 font-medium text-slate-700">
                            <i class="fa-solid fa-user-tie text-slate-400 mx-1"></i> {{ $dispute['expert'] }}
                        </td>

                        {{-- Company --}}
                        <td class="px-5 py-4 font-medium text-slate-700">
                            <i class="fa-solid fa-building text-slate-400 mx-1"></i> {{ $dispute['company'] }}
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 text-xs text-slate-500">
                            {{ $dispute['created_at']->format('M d, Y - h:i A') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right rtl:text-left">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.disputes.show', ['type' => $dispute['type'], 'id' => $dispute['id']]) }}" 
                                   class="text-primary bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-800 border border-indigo-100 font-medium rounded-lg text-sm px-3 py-1.5 transition">
                                    {!! __('admin.review_dispute') ?? 'Review' !!} <i class="fa-solid fa-arrow-right rtl:rotate-180 text-xs ml-1"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                            <i class="fa-solid fa-scale-unbalanced text-5xl mb-4 text-slate-200 block"></i>
                            <p class="font-bold text-lg text-slate-600">{!! __('admin.no_disputes_found') ?? 'No active disputes found' !!}</p>
                            <p class="text-sm mt-1">{!! __('admin.all_clear') ?? 'Everything is running smoothly.' !!}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
