@extends('layouts.admin')

@section('title', __('admin.users_management') ?? 'Users Management')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.user_management') !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.user_management_desc') !!}</p>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{!! __('admin.total_users_kpi') !!}</span>
        <span class="text-3xl font-black text-slate-800">{{ number_format($totalUsers) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-emerald-500 uppercase tracking-wider">{!! __('admin.active') !!}</span>
        <span class="text-3xl font-black text-emerald-600">{{ number_format($activeUsers) }}</span>
    </div>
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-red-400 uppercase tracking-wider">{!! __('admin.suspended') !!}</span>
        <span class="text-3xl font-black text-red-500">{{ number_format($suspendedUsers) }}</span>
    </div>
    <div class="bg-white border border-indigo-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider">{!! __('admin.expert_role') !!}</span>
        <span class="text-3xl font-black text-indigo-600">{{ number_format($expertCount) }}</span>
    </div>
    <div class="bg-white border border-amber-100 rounded-2xl p-5 shadow-sm flex flex-col gap-1">
        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">{!! __('admin.company_role') !!}</span>
        <span class="text-3xl font-black text-amber-600">{{ number_format($companyCount) }}</span>
    </div>
</div>

{{-- Filter Bar --}}
<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 bg-slate-50">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute top-1/2 left-3 rtl:right-3 rtl:left-auto -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('admin.search_users') }}"
                    class="w-full bg-white border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-primary focus:border-primary pl-9 rtl:pr-9 rtl:pl-3 p-2.5 transition outline-none">
            </div>

            {{-- Role Filter --}}
            <select name="role" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 transition min-w-[140px]">
                <option value="">{!! __('admin.all_roles') !!}</option>
                <option value="expert"    {{ request('role') == 'expert'     ? 'selected' : '' }}>{!! __('admin.expert_role') !!}</option>
                <option value="company"   {{ request('role') == 'company'    ? 'selected' : '' }}>{!! __('admin.company_role') !!}</option>
                <option value="freelancer"{{ request('role') == 'freelancer' ? 'selected' : '' }}>{!! __('admin.freelancer_role') !!}</option>
                <option value="student"   {{ request('role') == 'student'    ? 'selected' : '' }}>{!! __('admin.student_role') !!}</option>
            </select>

            {{-- Status Filter --}}
            <select name="status" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 transition min-w-[140px]">
                <option value="">{!! __('admin.all_statuses') ?? 'All Statuses' !!}</option>
                <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>{!! __('admin.active') !!}</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{!! __('admin.suspended') !!}</option>
            </select>

            <button type="submit" class="bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-primary/90 transition shadow-sm shadow-primary/20">
                <i class="fa-solid fa-filter me-1"></i> {!! __('admin.filter_btn') !!}
            </button>

            @if(request()->anyFilled(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-lg text-sm font-bold border border-red-100 hover:bg-red-100 transition whitespace-nowrap">
                    <i class="fa-solid fa-xmark me-1"></i> {!! __('admin.clear_btn') !!}
                </a>
            @endif

            <span class="ml-auto text-xs text-slate-400 font-medium">
                {{ $users->total() }} {{ __('admin.results_found') ?? 'results found' }}
            </span>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left rtl:text-right text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.user_details_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.role_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.phone_col') ?? 'Phone' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{{ __('admin.domain_col') ?? 'Domain / Spec.' }}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.status_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider">{!! __('admin.joined_at_col') !!}</th>
                    <th class="px-5 py-4 font-bold tracking-wider text-right rtl:text-left">{!! __('admin.actions_col') !!}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users  $user)
                    <tr class="hover:bg-slate-50/60 transition {{ $user->is_active ? '' : 'opacity-60' }}">

                        {{-- User Details --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img class="w-10 h-10 rounded-full object-cover border-2 border-slate-100 shadow-sm flex-shrink-0"
                                         src="{{ $user->avatar_url }}"
                                         alt="{{ $user->name }}">
                                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 truncate max-w-[160px]">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-500 truncate max-w-[160px]">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Role Badge --}}
                        <td class="px-5 py-4">
                            @php
                                $roleColors = [
                                    'expert'     => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                    'company'    => 'bg-amber-50 text-amber-700 border-amber-100',
                                    'freelancer' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'student'    => 'bg-teal-50 text-teal-700 border-teal-100',
                                ];
                                $roleClass = $roleColors[$user->role] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                $roleLabels = [
                                    'expert'     => __('admin.expert_role'),
                                    'company'    => __('admin.company_role'),
                                    'freelancer' => __('admin.freelancer_role'),
                                    'student'    => __('admin.student_role'),
                                ];
                                $roleLabel = $roleLabels[$user->role] ?? strtoupper($user->role ?? 'user');
                            @endphp
                            <span class="px-2.5 py-1 rounded border text-[11px] font-bold uppercase tracking-wider {{ $roleClass }}">{!! $roleLabel !!}</span>
                        </td>

                        {{-- Phone --}}
                        <td class="px-5 py-4 text-slate-600 text-xs font-medium">
                            @if($user->phone)
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-phone text-slate-300"></i>
                                    {{ $user->phone }}
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>

                        {{-- Domain / Specialization --}}
                        <td class="px-5 py-4">
                            @if($user->expert_domain || $user->expert_specialization)
                                <div class="text-xs font-bold text-slate-700">{{ $user->expert_domain ?? '—' }}</div>
                                @if($user->expert_specialization)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $user->expert_specialization }}</div>
                                @endif
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if($user->is_active)
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

                        {{-- Joined At --}}
                        <td class="px-5 py-4 text-slate-500 font-medium text-xs">
                            <div>{{ $user->created_at->format('M d, Y') }}</div>
                            <div class="text-slate-400 mt-0.5">{{ $user->created_at->diffForHumans() }}</div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right rtl:text-left">
                            <div class="flex items-center justify-end gap-2">

                                {{-- Toggle Suspend/Activate --}}
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    @if($user->is_active)
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:border-amber-400 hover:bg-amber-50 transition shadow-sm"
                                            title="{{ __('admin.suspend_user') }}"
                                            onclick="return confirm('{{ __('admin.suspend_confirm') }}')">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:border-emerald-400 hover:bg-emerald-50 transition shadow-sm"
                                            title="{{ __('admin.reactivate_user') }}"
                                            onclick="return confirm('{{ __('admin.reactivate_confirm') }}')">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    @endif
                                </form>

                                {{-- Impersonate --}}
                                @if(auth()->user()->role === 'superadmin' && $user->role !== 'superadmin' && $user->is_active)
                                    <form action="{{ route('admin.impersonate.start', $user->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-brand-primary hover:text-white hover:border-brand-primary hover:bg-brand-primary transition shadow-sm"
                                            title="Login as User">
                                            <i class="fa-solid fa-user-secret text-xs"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block">
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
                        <td colspan="7" class="px-6 py-16 text-center text-slate-500">
                            <i class="fa-solid fa-users-slash text-5xl mb-4 text-slate-200 block"></i>
                            <p class="font-bold text-lg text-slate-600">{!! __('admin.no_users_found') !!}</p>
                            <p class="text-sm mt-1">{!! __('admin.adjust_search') !!}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
            <span class="text-xs text-slate-500 font-medium">
                {{ __('admin.showing') ?? 'Showing' }} {{ $users->firstItem() }}–{{ $users->lastItem() }} {{ __('admin.of') ?? 'of' }} {{ $users->total() }}
            </span>
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
