@extends('layouts.admin')

@section('title', __('admin.system_overview') ?? 'Dashboard')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.system_overview') !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.system_overview_desc') !!}</p>
    </div>
    <div class="hidden sm:flex items-center gap-2">
        <button class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-lg font-bold shadow-sm hover:bg-slate-50 transition flex items-center gap-2 text-sm">
            <i class="fa-regular fa-calendar"></i> {!! __('admin.last_30_days') !!}
        </button>
        <button class="bg-primary text-white px-4 py-2 rounded-lg font-bold shadow-md shadow-primary/30 hover:bg-primary/90 transition flex items-center gap-2 text-sm">
            <i class="fa-solid fa-download"></i> {!! __('admin.generate_report') !!}
        </button>
    </div>
</div>

{{-- KPI Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
            <i class="fa-solid fa-users text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-sm tracking-wide uppercase">{!! __('admin.total_users_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-4xl font-black text-slate-800 leading-none">{{ number_format($metrics['total_users']) }}</span>
            @php $uc = $metrics['users_change']; @endphp
            <span class="text-{{ $uc['up'] ? 'emerald' : 'red' }}-500 text-sm font-bold flex items-center gap-1 mb-1 bg-{{ $uc['up'] ? 'emerald' : 'red' }}-50 px-2 py-0.5 rounded-md">
                <i class="fa-solid fa-arrow-trend-{{ $uc['up'] ? 'up' : 'down' }}"></i> {{ $uc['up'] ? '+' : '-' }}{{ $uc['value'] }}%
            </span>
        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
            <i class="fa-solid fa-building text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-building"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-sm tracking-wide uppercase">{!! __('admin.companies_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-4xl font-black text-slate-800 leading-none">{{ number_format($metrics['total_companies']) }}</span>
            @php $cc = $metrics['companies_change']; @endphp
            <span class="text-{{ $cc['up'] ? 'emerald' : 'red' }}-500 text-sm font-bold flex items-center gap-1 mb-1 bg-{{ $cc['up'] ? 'emerald' : 'red' }}-50 px-2 py-0.5 rounded-md">
                <i class="fa-solid fa-arrow-trend-{{ $cc['up'] ? 'up' : 'down' }}"></i> {{ $cc['up'] ? '+' : '-' }}{{ $cc['value'] }}%
            </span>
        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition group relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
            <i class="fa-solid fa-wallet text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-sm tracking-wide uppercase">{!! __('admin.revenue_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-4xl font-black text-slate-800 leading-none flex items-baseline gap-1">
                {{ number_format($metrics['total_revenue'], 2) }} <span class="text-xl text-slate-500 font-black">{!! __('admin.currency_sar') !!}</span>
            </span>
            @php $rc = $metrics['revenue_change']; @endphp
            <span class="text-{{ $rc['up'] ? 'emerald' : 'red' }}-500 text-sm font-bold flex items-center gap-1 mb-1 bg-{{ $rc['up'] ? 'emerald' : 'red' }}-50 px-2 py-0.5 rounded-md">
                <i class="fa-solid fa-arrow-trend-{{ $rc['up'] ? 'up' : 'down' }}"></i> {{ $rc['up'] ? '+' : '-' }}{{ $rc['value'] }}%
            </span>
        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition group relative overflow-hidden border-l-4 border-l-red-500">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
            <i class="fa-solid fa-scale-balanced text-8xl"></i>
        </div>
        <div class="flex items-center gap-4 mb-4 relative z-10">
            <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <h3 class="text-slate-500 font-bold text-sm tracking-wide uppercase">{!! __('admin.disputes_kpi') !!}</h3>
        </div>
        <div class="flex items-end gap-3 relative z-10">
            <span class="text-4xl font-black text-slate-800 leading-none">{{ $metrics['active_disputes'] }}</span>
            <span class="text-slate-400 text-sm font-bold flex items-center gap-1 mb-1">
                {!! __('admin.requiring_attention') !!}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    {{-- Left Column: Main Chart Placeholder & Fast Actions --}}
    <div class="xl:col-span-2 space-y-8">
        
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">{!! __('admin.revenue_growth') !!}</h3>
                <div class="flex gap-2">
                    <span class="flex items-center gap-1 text-xs font-bold text-slate-500 bg-white border border-slate-200 px-2 py-1 rounded shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-primary"></span> {!! __('admin.this_year') !!}
                    </span>
                </div>
            </div>
            <div class="p-6 h-80 flex flex-col items-center justify-center bg-slate-50/30">
                <canvas id="revenueChart" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Recent Users Table --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">{!! __('admin.new_registrations') !!}</h3>
                <a href="{{ route('admin.users.index') }}" class="text-primary font-bold text-sm hover:underline">{!! __('admin.view_all') !!}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left rtl:text-right border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase text-xs font-bold tracking-wider">
                            <th class="px-6 py-4 border-b border-slate-100">{!! __('admin.user_col') !!}</th>
                            <th class="px-6 py-4 border-b border-slate-100">{!! __('admin.role_col') !!}</th>
                            <th class="px-6 py-4 border-b border-slate-100">{!! __('admin.status_col') !!}</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-right rtl:text-left">{!! __('admin.date_col') !!}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($recentUsers as $user)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img class="w-8 h-8 rounded-full bg-slate-200" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" alt="">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->role === 'expert')
                                    <span class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded uppercase text-[10px] font-bold border border-indigo-100">{!! __('admin.expert_role') !!}</span>
                                @elseif($user->role === 'company')
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded uppercase text-[10px] font-bold border border-slate-200">{!! __('admin.company_role') !!}</span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded uppercase text-[10px] font-bold border border-slate-200">{{ strtoupper($user->role ?? 'User') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-1.5 text-xs font-bold {{ $user->is_active || $user->status === 'active' ? 'text-emerald-600' : 'text-red-600' }}">
                                    <span class="w-1.5 h-1.5 {{ $user->is_active || $user->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' }} rounded-full"></span> 
                                    {{ $user->is_active || $user->status === 'active' ? __('admin.active') : __('admin.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right rtl:text-left text-slate-500 font-medium">{{ $user->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-slate-500">{!! __('admin.no_users_found') ?? 'No users found' !!}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Column: System Logs & Actions --}}
    <div class="space-y-8">
        <div class="bg-white rounded-2xl p-6 shadow-sm relative overflow-hidden border border-slate-200">
            <div class="absolute -right-4 -top-8 opacity-[0.03]">
                <i class="fa-solid fa-bolt text-9xl"></i>
            </div>
            <h3 class="text-lg font-bold mb-2 relative z-10 text-slate-800">{!! __('admin.quick_actions') !!}</h3>
            <p class="text-sm text-slate-500 mb-6 relative z-10">{!! __('admin.quick_actions_desc') !!}</p>
            
            <div class="space-y-3 relative z-10">
                <a href="{{ route('admin.users.index') }}" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-100 transition px-4 py-3 rounded-xl flex items-center justify-between group">
                    <div class="flex items-center gap-3 font-bold text-slate-700">
                        <i class="fa-solid fa-bullhorn w-5 text-center text-primary"></i> {!! __('admin.announce_users') !!}
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-primary transition-transform transform group-hover:translate-x-1 rtl:rotate-180"></i>
                </a>
                <a href="{{ route('admin.financials.index') }}" class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-100 transition px-4 py-3 rounded-xl flex items-center justify-between group">
                    <div class="flex items-center gap-3 font-bold text-slate-700">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-emerald-500"></i> {!! __('admin.process_payouts') !!}
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-emerald-500 transition-transform transform group-hover:translate-x-1 rtl:rotate-180"></i>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="w-full {{ $maintenanceMode ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-red-50 border-red-100 text-red-600' }} hover:opacity-90 border transition px-4 py-3 rounded-xl flex items-center justify-between group">
                    <div class="flex items-center gap-3 font-bold {{ $maintenanceMode ? 'text-amber-700' : 'text-red-600' }}">
                        <i class="fa-solid fa-triangle-exclamation w-5 text-center {{ $maintenanceMode ? 'text-amber-500' : 'text-red-500' }}"></i>
                        {!! __('admin.system_maintenance') !!}
                        @if($maintenanceMode)
                            <span class="text-[10px] font-black bg-amber-500 text-white px-2 py-0.5 rounded-full">{!! __('admin.active') !!}</span>
                        @endif
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs {{ $maintenanceMode ? 'text-amber-400' : 'text-red-400' }} group-hover:opacity-80 transition-transform transform group-hover:translate-x-1 rtl:rotate-180"></i>
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden pb-4">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">{!! __('admin.recent_activity') !!}</h3>
            </div>
            <div class="p-6">
                <ol class="relative border-l border-slate-200 rtl:border-r rtl:border-l-0 ml-3 rtl:mr-3 rtl:ml-0">
                    @forelse($recentActivities as $activity)                  
                    <li class="mb-6 pl-6 rtl:pr-6 rtl:pl-0">            
                        <span class="absolute flex items-center justify-center w-6 h-6 {{ $activity['icon_bg'] }} rounded-full -left-3 rtl:-right-3 ring-4 ring-white">
                            <i class="{{ $activity['icon'] }} text-[10px] {{ $activity['icon_color'] }}"></i>
                        </span>
                        <h3 class="flex items-center mb-1 text-sm font-bold text-slate-800 tracking-tight">{{ $activity['title'] }}</h3>
                        <time class="block mb-2 text-xs font-normal leading-none text-slate-400">{{ $activity['time_diff'] }}</time>
                        <p class="text-sm text-slate-500">{{ $activity['description'] }}</p>
                    </li>
                    @empty
                    <li class="pl-6 rtl:pr-6 rtl:pl-0">
                        <p class="text-sm text-slate-500">No recent activities found.</p>
                    </li>
                    @endforelse
                </ol>
            </div>
            <div class="px-6 text-center">
                <a href="#" class="text-sm font-bold text-primary hover:text-secondary transition bg-slate-50 w-full block py-2 rounded-lg border border-slate-100">{!! __('admin.view_activity_log') !!}</a>
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
                    borderColor: '#4f46e5', // Primary color
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
