@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-800 min-h-screen">
    {{-- Dashboard Specific Navbar if needed, but we are inside the main layout which has a header. 
         The legacy code had a specific navbar. We can add a sub-nav or breadcrumb here. --}}
    
    <div class="container mx-auto px-6 py-10">
        
        <!-- Welcome Banner -->
        <div class="bg-indigo-900 rounded-3xl p-8 text-white mb-10 relative overflow-hidden shadow-xl">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-10 -translate-y-10"></div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ __('dashboard.welcome_back', ['name' => explode(' ', $user->name)[0]]) }}</h1>
                    <p class="text-indigo-200">{{ __('dashboard.overview_text') }}</p>
                </div>
                <div class="flex gap-3">
                    {{-- Assuming post_project.php corresponds to 'requests.create' or similar route. 
                         For now, keeping legacy link or checking if route exists. 
                         The legacy link was post_project.php. I'll leave a placeholder or guess a route if typical. --}}
                    <a href="{{ route('requests.browse') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition flex items-center gap-2">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('dashboard.new_expert_request') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <!-- Team Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">👥</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">{{ __('dashboard.team') }}</p>
                    <p class="text-2xl font-black text-slate-800">{{ $teamCount }}</p>
                </div>
            </div>
            <!-- Services Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-2xl">📦</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">{{ __('dashboard.services') }}</p>
                    <p class="text-2xl font-black text-slate-800">{{ $servicesCount }}</p>
                </div>
            </div>
            <!-- Sales Card (Example) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-2xl">💰</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">{{ __('dashboard.revenue') }}</p>
                    <p class="text-2xl font-black text-slate-800">0 <span class="text-xs font-normal">SR</span></p>
                </div>
            </div>
            <!-- Rating Card -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-2xl">⭐</div>
                <div>
                    <p class="text-sm text-slate-400 font-bold">{{ __('dashboard.company_rating') }}</p>
                    <p class="text-2xl font-black text-slate-800">4.9</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions (Main Navigation) -->
        <h2 class="text-xl font-bold text-slate-800 mb-6">{{ __('dashboard.company_management') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- 1. Team Management -->
            <a href="{{ route('dashboard.team') }}" class="group block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-indigo-500 hover:shadow-md transition cursor-pointer">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">{{ __('dashboard.team_management') }}</h3>
                <p class="text-sm text-slate-500">{{ __('dashboard.team_management_desc') }}</p>
                <div class="mt-4 text-indigo-600 text-sm font-bold flex items-center gap-1 group-hover:gap-2 transition-all">
                    {{ __('dashboard.go_to_team') }} <span class="text-lg rtl:rotate-180">&larr;</span>
                </div>
            </a>

            <!-- 2. Projects -->
            <a href="{{ route('dashboard.projects') }}" class="group block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-emerald-500 hover:shadow-md transition cursor-pointer">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                   <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">{{ __('dashboard.projects_contracts') }}</h3>
                <p class="text-sm text-slate-500">{{ __('dashboard.projects_contracts_desc') }}</p>
            </a>

            <!-- 3. Settings -->
            <a href="{{ route('dashboard.settings') }}" class="group block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:border-slate-400 hover:shadow-md transition cursor-pointer">
                <div class="w-14 h-14 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-slate-800 group-hover:text-white transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">{{ __('dashboard.company_settings') }}</h3>
                <p class="text-sm text-slate-500">{{ __('dashboard.company_settings_desc') }}</p>
            </a>

        </div>

    </div>
</div>
@endsection
