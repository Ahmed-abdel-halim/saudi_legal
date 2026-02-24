@extends('layouts.admin')

@section('title', __('admin.services_board') ?? 'Services Board')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.services_board') ?? 'Services Board' !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.services_board_desc') ?? 'Manage all expert services and gig listings across the platform.' !!}</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.total_services') ?? 'Total Services' !!}</span>
        <span class="text-3xl font-black text-slate-800">{{ number_format($stats['total']) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.active_services') ?? 'Active Listings' !!}</span>
        <span class="text-3xl font-black text-emerald-600">{{ number_format($stats['active']) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.inactive_services') ?? 'Inactive' !!}</span>
        <span class="text-3xl font-black text-red-500">{{ number_format($stats['inactive']) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.avg_price') ?? 'Avg Hourly Rate' !!}</span>
        <span class="text-3xl font-black text-indigo-600">{{ number_format($stats['avg_price'], 2) }} <span class="text-lg font-bold text-indigo-400">ريال</span></span>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    
    {{-- Header & Filters --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-briefcase text-primary rtl:ml-2"></i>
            <h3 class="text-lg font-bold text-slate-800">{!! __('admin.all_services') ?? 'All Services' !!}</h3>
        </div>
        
        <form action="{{ route('admin.services.index') }}" method="GET" class="flex flex-wrap gap-3">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{!! __('admin.search_services') ?? 'Search title, expert...' !!}" class="w-64 bg-white border border-slate-200 text-sm rounded-lg focus:ring-primary focus:border-primary block pl-4 rtl:pr-4 rtl:pl-10 p-2.5 outline-none transition shadow-sm">
                <button type="submit" class="absolute inset-y-0 right-0 max-w-full flex items-center pr-3 rtl:pl-3 rtl:pr-0">
                    <i class="fa-solid fa-search text-slate-400 hover:text-primary transition"></i>
                </button>
            </div>
            
            <select name="status" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 outline-none transition shadow-sm" onchange="this.form.submit()">
                <option value="">{!! __('admin.all_statuses') ?? 'All Statuses' !!}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{!! __('admin.status_active') ?? 'Active' !!}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{!! __('admin.status_inactive') ?? 'Inactive' !!}</option>
            </select>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-b border-emerald-100 text-emerald-600 px-6 py-3 text-sm font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.service_title_col') ?? 'Service Title' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.expert_col') ?? 'Expert' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.hourly_rate_col') ?? 'Hourly Rate' !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.status_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($services as $service)
                    <tr class="hover:bg-slate-50/60 transition {{ $service->is_active ? '' : 'opacity-60' }}">
                        
                        {{-- Title --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($service->image_url)
                                    <img src="{{ $service->image_url }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-sm" alt="Service Cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-primary">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif
                                <div class="font-bold text-slate-800 truncate max-w-[250px]" title="{{ $service->title }}">
                                    {{ $service->title }}
                                </div>
                            </div>
                        </td>

                        {{-- Expert --}}
                        <td class="px-5 py-4 font-medium text-slate-700">
                            <div class="flex items-center gap-2">
                                <img src="{{ $service->expert->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($service->expert->name ?? 'User').'&background=f8fafc&color=475569' }}" class="w-6 h-6 rounded-full border border-slate-200">
                                <span>{{ $service->expert->name ?? 'Unknown' }}</span>
                            </div>
                        </td>

                        {{-- Hourly Rate --}}
                        <td class="px-5 py-4">
                            <span class="font-black text-slate-800">{{ number_format($service->price, 2) }}</span><span class="text-xs text-slate-400"> {!! __('admin.currency_sar') !!}/hr</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if($service->is_active)
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold me-2 px-2.5 py-0.5 rounded border border-emerald-200">{!! __('admin.status_active') ?? 'Active' !!}</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-bold me-2 px-2.5 py-0.5 rounded border border-red-200">{!! __('admin.status_suspended') ?? 'Disabled' !!}</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right rtl:text-left">
                            <div class="flex items-center justify-end gap-2">
                                {{-- View on Frontend (if applicable) --}}
                                <a href="{{ route('services.show', $service->service_id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition" title="{{ __('admin.preview') ?? 'Preview' }}">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>

                                {{-- Toggle Status --}}
                                <form action="{{ route('admin.services.toggle-status', $service->service_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-8 h-8 rounded-lg {{ $service->is_active ? 'bg-amber-100 hover:bg-amber-200 text-amber-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700' }} flex items-center justify-center transition" title="{{ $service->is_active ? __('admin.suspend') ?? 'Disable' : __('admin.activate') ?? 'Enable' }}">
                                        <i class="fa-solid {{ $service->is_active ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('admin.services.destroy', $service->service_id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete_service') ?? 'Are you sure you want to delete this service?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 border border-red-100 flex items-center justify-center transition" title="{!! __('admin.delete') !!}">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                            <i class="fa-solid fa-briefcase text-5xl mb-4 text-slate-200 block"></i>
                            <p class="font-bold text-lg text-slate-600">{!! __('admin.no_services_found') ?? 'No services found' !!}</p>
                            <p class="text-sm mt-1">{!! __('admin.adjust_search') !!}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($services->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $services->links('pagination::tailwind') }}
    </div>
    @endif
</div>

@endsection
