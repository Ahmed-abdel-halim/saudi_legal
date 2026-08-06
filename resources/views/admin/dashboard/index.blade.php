@extends('layouts.admin')

@section('title', __('admin.system_overview') ?? 'Dashboard')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-brand-primary/20 text-brand-primary border border-brand-primary/30 flex items-center justify-center text-lg shadow-glow">
                <i class="fa-solid fa-chart-pie"></i>
            </span>
            {!! __('admin.system_overview') !!}
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm mt-1">{!! __('admin.system_overview_desc') !!}</p>
    </div>
    <div class="flex items-center gap-2">
        <button class="bg-dark-card border border-dark-border text-slate-300 px-4 py-2 rounded-xl font-bold shadow-sm hover:text-white hover:border-brand-primary/50 transition flex items-center gap-2 text-xs">
            <i class="fa-regular fa-calendar text-brand-primary"></i> {!! __('admin.last_30_days') !!}
        </button>
        <button class="bg-gradient-to-r from-brand-primary to-brand-secondary text-white px-4 py-2 rounded-xl font-bold shadow-glow hover:scale-105 transition-all duration-200 flex items-center gap-2 text-xs">
            <i class="fa-solid fa-download"></i> {!! __('admin.generate_report') !!}
        </button>
    </div>
</div>

{{-- KPI Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    {{-- Users Card --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-6 shadow-xl hover:border-brand-primary/40 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500 text-brand-primary">
            <i class="fa-solid fa-users text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-brand-primary/20 border border-brand-primary/30 text-brand-primary flex items-center justify-center text-xl shadow-glow">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3 class="text-gray-400 font-bold text-xs tracking-wide uppercase">{!! __('admin.total_users_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-3xl font-black text-white leading-none">{{ number_format($metrics['total_users']) }}</span>
            @php $uc = $metrics['users_change']; @endphp
            <span class="text-{{ $uc['up'] ? 'emerald' : 'red' }}-400 text-xs font-bold flex items-center gap-1 mb-0.5 bg-{{ $uc['up'] ? 'emerald' : 'red' }}-500/10 border border-{{ $uc['up'] ? 'emerald' : 'red' }}-500/30 px-2 py-0.5 rounded-md">
                <i class="fa-solid fa-arrow-trend-{{ $uc['up'] ? 'up' : 'down' }}"></i> {{ $uc['up'] ? '+' : '-' }}{{ $uc['value'] }}%
            </span>
        </div>
    </div>

    {{-- Companies Card --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-6 shadow-xl hover:border-brand-secondary/40 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500 text-brand-secondary">
            <i class="fa-solid fa-building text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-brand-secondary/20 border border-brand-secondary/30 text-brand-secondary flex items-center justify-center text-xl shadow-glow">
                <i class="fa-solid fa-building"></i>
            </div>
            <h3 class="text-gray-400 font-bold text-xs tracking-wide uppercase">{!! __('admin.companies_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-3xl font-black text-white leading-none">{{ number_format($metrics['total_companies']) }}</span>
            @php $cc = $metrics['companies_change']; @endphp
            <span class="text-{{ $cc['up'] ? 'emerald' : 'red' }}-400 text-xs font-bold flex items-center gap-1 mb-0.5 bg-{{ $cc['up'] ? 'emerald' : 'red' }}-500/10 border border-{{ $cc['up'] ? 'emerald' : 'red' }}-500/30 px-2 py-0.5 rounded-md">
                <i class="fa-solid fa-arrow-trend-{{ $cc['up'] ? 'up' : 'down' }}"></i> {{ $cc['up'] ? '+' : '-' }}{{ $cc['value'] }}%
            </span>
        </div>
    </div>

    {{-- Revenue Card --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-6 shadow-xl hover:border-emerald-500/40 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500 text-emerald-400">
            <i class="fa-solid fa-wallet text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xl shadow-green-glow">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <h3 class="text-gray-400 font-bold text-xs tracking-wide uppercase">{!! __('admin.revenue_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-3xl font-black text-white leading-none flex items-baseline gap-1">
                {{ number_format($metrics['total_revenue'], 2) }} <span class="text-sm text-gray-400 font-bold">{!! __('admin.currency_sar') !!}</span>
            </span>
            @php $rc = $metrics['revenue_change']; @endphp
            <span class="text-{{ $rc['up'] ? 'emerald' : 'red' }}-400 text-xs font-bold flex items-center gap-1 mb-0.5 bg-{{ $rc['up'] ? 'emerald' : 'red' }}-500/10 border border-{{ $rc['up'] ? 'emerald' : 'red' }}-500/30 px-2 py-0.5 rounded-md">
                <i class="fa-solid fa-arrow-trend-{{ $rc['up'] ? 'up' : 'down' }}"></i> {{ $rc['up'] ? '+' : '-' }}{{ $rc['value'] }}%
            </span>
        </div>
    </div>

    {{-- Disputes Card --}}
    <div class="bg-dark-card border border-dark-border rounded-2xl p-6 shadow-xl hover:border-red-500/40 transition-all duration-300 group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500 text-red-400">
            <i class="fa-solid fa-scale-balanced text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <h3 class="text-gray-400 font-bold text-xs tracking-wide uppercase">{!! __('admin.disputes_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-3xl font-black text-white leading-none">{{ $metrics['active_disputes'] }}</span>
            <span class="text-red-400 text-xs font-bold flex items-center gap-1 mb-0.5 bg-red-500/10 border border-red-500/30 px-2 py-0.5 rounded-md">
                {!! __('admin.requiring_attention') !!}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    {{-- Left Column: Revenue Chart & Registrations --}}
    <div class="xl:col-span-2 space-y-8">
        
        {{-- Revenue Chart Card --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-border flex items-center justify-between bg-dark-navy/50">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-brand-primary"></i> {!! __('admin.revenue_growth') !!}
                </h3>
                <div class="flex gap-2">
                    <span class="flex items-center gap-1.5 text-xs font-bold text-gray-300 bg-dark-navy border border-dark-border px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-brand-primary shadow-glow"></span> {!! __('admin.this_year') !!}
                    </span>
                </div>
            </div>
            <div class="p-6 h-80 flex flex-col items-center justify-center bg-dark-navy/30">
                <canvas id="revenueChart" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Recent Registrations Table --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-border flex items-center justify-between bg-dark-navy/50">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-400"></i> {!! __('admin.new_registrations') !!}
                </h3>
                <a href="{{ route('admin.users.index') }}" class="text-brand-primary font-bold text-xs hover:underline flex items-center gap-1">
                    {!! __('admin.view_all') !!} <i class="fa-solid fa-arrow-left rtl:rotate-180 text-[10px]"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left rtl:text-right border-collapse">
                    <thead>
                        <tr class="bg-dark-navy/60 text-gray-400 uppercase text-[11px] font-extrabold tracking-wider border-b border-dark-border">
                            <th class="px-6 py-3.5">{!! __('admin.user_col') !!}</th>
                            <th class="px-6 py-3.5">{!! __('admin.role_col') !!}</th>
                            <th class="px-6 py-3.5">{!! __('admin.status_col') !!}</th>
                            <th class="px-6 py-3.5 text-right rtl:text-left">{!! __('admin.date_col') !!}</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-dark-border/60 text-gray-300">
                        @forelse($recentUsers as $user)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-primary/20 text-brand-primary flex items-center justify-center font-bold text-xs ring-1 ring-brand-primary/30">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $user->name }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($user->role === 'expert')
                                    <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2.5 py-0.5 rounded-full text-[10px] font-bold">{!! __('admin.expert_role') !!}</span>
                                @elseif($user->role === 'company')
                                    <span class="bg-brand-primary/10 text-brand-primary border border-brand-primary/30 px-2.5 py-0.5 rounded-full text-[10px] font-bold">{!! __('admin.company_role') !!}</span>
                                @else
                                    <span class="bg-slate-800 text-gray-300 border border-slate-700 px-2.5 py-0.5 rounded-full text-[10px] font-bold">{{ strtoupper($user->role ?? 'User') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $user->is_active || $user->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/10 text-red-400 border border-red-500/30' }}">
                                    <span class="w-1.5 h-1.5 {{ $user->is_active || $user->status === 'active' ? 'bg-emerald-400 shadow-green-glow' : 'bg-red-400' }} rounded-full"></span> 
                                    {{ $user->is_active || $user->status === 'active' ? __('admin.active') : __('admin.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right rtl:text-left text-gray-400 font-medium">{{ $user->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">{!! __('admin.no_users_found') ?? 'No users found' !!}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Column: Quick Actions & Activity Log --}}
    <div class="space-y-8">
        {{-- Quick Actions --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl p-6 shadow-xl relative overflow-hidden">
            <h3 class="text-base font-bold mb-1 text-white flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-400"></i> {!! __('admin.quick_actions') !!}
            </h3>
            <p class="text-xs text-gray-400 mb-5">{!! __('admin.quick_actions_desc') !!}</p>
            
            <div class="space-y-3 relative z-10">
                <a href="{{ route('admin.users.index') }}" class="w-full bg-dark-navy/60 hover:bg-dark-navy border border-dark-border hover:border-brand-primary/40 transition-all px-4 py-3 rounded-xl flex items-center justify-between group">
                    <div class="flex items-center gap-3 font-bold text-xs text-gray-200 group-hover:text-white">
                        <i class="fa-solid fa-bullhorn w-5 text-center text-brand-primary"></i> {!! __('admin.announce_users') !!}
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-500 group-hover:text-brand-primary transition-transform transform group-hover:translate-x-1 rtl:rotate-180"></i>
                </a>
                <a href="{{ route('admin.financials.index') }}" class="w-full bg-dark-navy/60 hover:bg-dark-navy border border-dark-border hover:border-emerald-500/40 transition-all px-4 py-3 rounded-xl flex items-center justify-between group">
                    <div class="flex items-center gap-3 font-bold text-xs text-gray-200 group-hover:text-white">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-emerald-400"></i> {!! __('admin.process_payouts') !!}
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-500 group-hover:text-emerald-400 transition-transform transform group-hover:translate-x-1 rtl:rotate-180"></i>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="w-full {{ $maintenanceMode ? 'bg-amber-500/10 border-amber-500/30 text-amber-300' : 'bg-red-500/10 border-red-500/30 text-red-300' }} hover:opacity-90 border transition px-4 py-3 rounded-xl flex items-center justify-between group text-xs">
                    <div class="flex items-center gap-3 font-bold">
                        <i class="fa-solid fa-triangle-exclamation w-5 text-center {{ $maintenanceMode ? 'text-amber-400' : 'text-red-400' }}"></i>
                        {!! __('admin.system_maintenance') !!}
                        @if($maintenanceMode)
                            <span class="text-[10px] font-black bg-amber-500 text-white px-2 py-0.5 rounded-full">{!! __('admin.active') !!}</span>
                        @endif
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs {{ $maintenanceMode ? 'text-amber-400' : 'text-red-400' }} group-hover:opacity-80 transition-transform transform group-hover:translate-x-1 rtl:rotate-180"></i>
                </a>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="bg-dark-card border border-dark-border rounded-2xl shadow-xl overflow-hidden pb-4">
            <div class="px-6 py-4 border-b border-dark-border flex items-center justify-between bg-dark-navy/50">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-sky-400"></i> {!! __('admin.recent_activity') !!}
                </h3>
            </div>
            <div class="p-6">
                <ol class="relative border-l border-dark-border rtl:border-r rtl:border-l-0 ml-3 rtl:mr-3 rtl:ml-0">
                    @forelse($recentActivities as $activity)                  
                    <li class="mb-6 pl-6 rtl:pr-6 rtl:pl-0">            
                        <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 rtl:-right-3 ring-4 ring-dark-card bg-brand-primary/20 border border-brand-primary/40 text-brand-primary text-[10px]">
                            <i class="{{ $activity['icon'] }}"></i>
                        </span>
                        <h3 class="flex items-center mb-1 text-xs font-bold text-white tracking-tight">{{ $activity['title'] }}</h3>
                        <time class="block mb-1.5 text-[10px] font-medium text-gray-500">{{ $activity['time_diff'] }}</time>
                        <p class="text-xs text-gray-400 leading-relaxed">{{ $activity['description'] }}</p>
                    </li>
                    @empty
                    <li class="pl-6 rtl:pr-6 rtl:pl-0">
                        <p class="text-xs text-gray-500">No recent activities found.</p>
                    </li>
                    @endforelse
                </ol>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Revenue (SAR)',
                    data: {!! json_encode($chartData) !!},
                    borderColor: '#4F46E5', // Primary brand color
                    backgroundColor: 'rgba(79, 70, 229, 0.15)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#8B5CF6',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.06)' },
                        ticks: { color: '#94a3b8', font: { family: 'Tajawal' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { family: 'Tajawal' } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
