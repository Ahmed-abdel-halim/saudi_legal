@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap');

    :root {
        --primary: #4f46e5;
        --secondary: #0ea5e9;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --glass: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    body {
        font-family: 'Cairo', sans-serif;
    }

    .dashboard-bg {
        background: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%),
            radial-gradient(at 0% 100%, rgba(239, 68, 68, 0.05) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .glass-card {
        background: var(--glass);
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid var(--glass-border);
        border-radius: 2rem;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.8);
    }

    .metric-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.25rem;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-premium {
        background: #1e293b;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 1.25rem;
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-premium:hover {
        background: #0f172a;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .svg-icon {
        width: 1.5rem;
        height: 1.5rem;
        fill: currentColor;
    }

    .banner-glass {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(129, 140, 248, 0.8));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 2.5rem;
    }
</style>

<div class="dashboard-bg min-h-screen text-slate-900 pb-20 pt-6">
    <div class="container mx-auto px-6 py-10">
        
        <!-- Premium Welcome Banner -->
        <div class="banner-glass p-12 text-white mb-12 relative overflow-hidden shadow-2xl group">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-white opacity-5 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000"></div>
            <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center gap-10">
                <div class="text-center lg:text-right">
                    <h1 class="text-4xl font-black mb-4 tracking-tight">
                        {{ __('dashboard.welcome_back', ['name' => explode(' ', $user->name)[0]]) }}
                    </h1>
                    <p class="text-indigo-100 text-lg font-medium opacity-90 max-w-xl">
                        {{ __('dashboard.overview_text') }}
                    </p>
                </div>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('client.governance.dashboard') }}" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-2xl font-black transition-all flex items-center gap-3 backdrop-blur-md">
                        <svg class="w-6 h-6 fill-currentColor" viewBox="0 0 24 24"><path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"></path></svg>
                        {{ app()->getLocale() == 'ar' ? 'تنقيح البيانات' : 'Data Annotation' }}
                    </a>
                    <a href="{{ route('requests.browse') }}" class="px-8 py-4 bg-white text-indigo-900 hover:bg-indigo-50 rounded-2xl font-black transition-all flex items-center gap-3 shadow-xl">
                        <svg class="w-6 h-6 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                        {{ __('dashboard.new_expert_request') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Premium KPIs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Team Card -->
            <div class="glass-card p-8 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('dashboard.team') }}</p>
                    <h4 class="text-4xl font-black text-slate-800">{{ number_format($teamCount) }}</h4>
                </div>
                <div class="metric-icon bg-blue-50 text-blue-600">
                    <svg class="w-7 h-7 fill-currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
            </div>
            <!-- Services Card -->
            <div class="glass-card p-8 flex items-center justify-between border-emerald-100">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">{{ __('dashboard.services') }}</p>
                    <h4 class="text-4xl font-black text-emerald-700">{{ number_format($servicesCount) }}</h4>
                </div>
                <div class="metric-icon bg-emerald-50 text-emerald-600">
                    <svg class="w-7 h-7 fill-currentColor" viewBox="0 0 24 24"><path d="M20 8l-8 5-8-5V6l8 5 8-5v2zm0-4c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4z"/></svg>
                </div>
            </div>
            <!-- Revenue Card -->
            <div class="glass-card p-8 flex items-center justify-between border-purple-100">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-purple-600 mb-1">{{ __('dashboard.revenue') }}</p>
                    <h4 class="text-4xl font-black text-purple-700">0 <span class="text-sm font-bold opacity-60">SR</span></h4>
                </div>
                <div class="metric-icon bg-purple-50 text-purple-600">
                    <svg class="w-7 h-7 fill-currentColor" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.41z"/></svg>
                </div>
            </div>
            <!-- Rating Card -->
            <div class="glass-card p-8 flex items-center justify-between border-yellow-100">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-yellow-600 mb-1">{{ __('dashboard.company_rating') }}</p>
                    <h4 class="text-4xl font-black text-yellow-700">{{ isset($company->rating) ? number_format($company->rating, 2) : '0.0' }}</h4>
                </div>
                <div class="metric-icon bg-yellow-50 text-yellow-600">
                    <svg class="w-7 h-7 fill-currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </div>
            </div>
        </div>

        <!-- Premium Management Sections -->
        <h2 class="text-2xl font-black text-slate-800 mb-8 flex items-center gap-4">
            <svg class="w-8 h-8 text-indigo-500" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm5.31-3.1L6.69 5.69A7.941 7.941 0 0112 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z"/></svg>
            {{ __('dashboard.company_management') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- 1. Team Management -->
            <a href="{{ route('dashboard.team') }}" class="glass-card p-10 group relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/5 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">{{ __('dashboard.team_management') }}</h3>
                <p class="text-slate-500 font-medium leading-relaxed mb-6">{{ __('dashboard.team_management_desc') }}</p>
                <div class="flex items-center gap-2 text-indigo-600 font-black text-sm uppercase tracking-widest">
                    {{ __('dashboard.go_to_team') }}
                    <svg class="w-5 h-5 rtl:rotate-180 group-hover:translate-x-2 transition-transform" viewBox="0 0 24 24"><path fill="currentColor" d="M16.01 11H4v2h12.01v3L20 12l-3.99-4z"/></svg>
                </div>
            </a>

            <!-- 2. Projects -->
            <a href="{{ route('dashboard.projects') }}" class="glass-card p-10 group relative overflow-hidden border-emerald-100/50">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-500/5 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">{{ __('dashboard.projects_contracts') }}</h3>
                <p class="text-slate-500 font-medium leading-relaxed mb-6">{{ __('dashboard.projects_contracts_desc') }}</p>
                <div class="flex items-center gap-2 text-emerald-600 font-black text-sm uppercase tracking-widest">
                    {{ app()->getLocale() == 'ar' ? 'عرض المشاريع' : 'View Projects' }}
                    <svg class="w-5 h-5 rtl:rotate-180 group-hover:translate-x-2 transition-transform" viewBox="0 0 24 24"><path fill="currentColor" d="M16.01 11H4v2h12.01v3L20 12l-3.99-4z"/></svg>
                </div>
            </a>

            <!-- 3. Settings -->
            <a href="{{ route('dashboard.settings') }}" class="glass-card p-10 group relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-slate-500/5 rounded-full transition-transform group-hover:scale-150 duration-700"></div>
                <div class="w-16 h-16 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-slate-800 group-hover:text-white transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">{{ __('dashboard.company_settings') }}</h3>
                <p class="text-slate-500 font-medium leading-relaxed mb-6">{{ __('dashboard.company_settings_desc') }}</p>
                <div class="flex items-center gap-2 text-slate-600 font-black text-sm uppercase tracking-widest">
                    {{ app()->getLocale() == 'ar' ? 'تحديث الإعدادات' : 'Update Settings' }}
                    <svg class="w-5 h-5 rtl:rotate-180 group-hover:translate-x-2 transition-transform" viewBox="0 0 24 24"><path fill="currentColor" d="M16.01 11H4v2h12.01v3L20 12l-3.99-4z"/></svg>
                </div>
            </a>

        </div>

    </div>
</div>
@endsection
