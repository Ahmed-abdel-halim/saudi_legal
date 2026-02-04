<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('expert_dashboard.page_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-700 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-green-200 shadow-lg">R</div>
                <div>
                    <h1 class="font-bold text-lg leading-none text-slate-800">Radiif</h1>
                    <span class="text-[10px] text-slate-500 font-bold tracking-wider">EXPERT DASHBOARD</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-sm font-bold text-slate-700">{{ $user->full_name ?? $user->name }}</span>
                    <span class="text-xs text-green-600 font-medium">{{ $expert_level }}</span>
                </div>
                <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white shadow-sm">
                    @if($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}" class="w-full h-full object-cover" alt="Avatar">
                    @else
                        @php
                            $initials = '';
                            $name = $user->full_name ?? $user->name ?? 'User';
                            $nameParts = explode(' ', $name);
                            foreach($nameParts as $part) {
                                $initials .= mb_substr($part, 0, 1);
                            }
                            $initials = mb_substr($initials, 0, 2);
                        @endphp
                        <div class="w-full h-full bg-green-700 flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper($initials) }}
                        </div>
                    @endif
                </div>
                <!-- Language Toggle -->
                <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() == 'ar' ? 'en' : 'ar']) }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 transition font-bold text-sm">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_dashboard.total_balance') }}</p>
                    <h2 class="text-3xl font-bold text-slate-800">{{ number_format($total_balance, 2) }} <span class="text-sm text-slate-400 font-normal">{{ __('expert_dashboard.currency') }}</span></h2>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_dashboard.today_earnings') }}</p>
                    <h2 class="text-3xl font-bold text-slate-800">{{ number_format($today_balance, 2) }} <span class="text-sm text-slate-400 font-normal">{{ __('expert_dashboard.currency') }}</span></h2>
                </div>
                <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-medium mb-1">{{ __('expert_dashboard.completed_tasks') }}</p>
                    <h2 class="text-3xl font-bold text-slate-800">{{ $total_tasks }} <span class="text-sm text-slate-400 font-normal">{{ __('expert_dashboard.task_unit') }}</span></h2>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="bg-green-500 text-white text-[10px] font-bold px-2 py-1 rounded">{{ __('expert_dashboard.live_badge') }}</span>
                                @if($pending_count > 0)
                                    <span class="text-green-300 text-sm font-bold animate-pulse">{{ __('expert_dashboard.pending_tasks_msg', ['count' => $pending_count]) }}</span>
                                @else
                                    <span class="text-slate-400 text-sm">{{ __('expert_dashboard.no_tasks_msg') }}</span>
                                @endif
                            </div>
                            <h2 class="text-3xl font-bold mb-2">{{ __('expert_dashboard.workbench_title') }}</h2>
                            <p class="text-slate-300 text-sm max-w-md">{{ __('expert_dashboard.workbench_desc') }}</p>
                        </div>
                        
                        <a href="{{ route('dashboard.expert.workbench') }}" class="bg-green-600 hover:bg-green-500 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-green-900/50 transition transform hover:-translate-y-1 flex items-center gap-3">
                            <i class="fa-solid fa-play"></i> {{ __('expert_dashboard.start_audit') }}
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-slate-700">{{ __('expert_dashboard.recent_activity') }}</h3>
                        <span class="text-xs text-slate-400">{{ __('expert_dashboard.last_5_ops') }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="p-4 font-semibold">{{ __('expert_dashboard.tbl_task_id') }}</th>
                                    <th class="p-4 font-semibold">{{ __('expert_dashboard.tbl_action') }}</th>
                                    <th class="p-4 font-semibold">{{ __('expert_dashboard.tbl_time') }}</th>
                                    <th class="p-4 font-semibold">{{ __('expert_dashboard.tbl_value') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($history as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-mono text-slate-600">#{{ $row->task_id ?? $row->id ?? 'unknown' }}</td> <!-- Ensuring fallback if needed -->
                                    <td class="p-4">
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">{{ __('expert_dashboard.status_corrected') }}</span>
                                    </td>
                                    <td class="p-4 text-slate-500">{{ date('H:i A', strtotime($row->created_at)) }}</td>
                                    <td class="p-4 font-bold text-slate-700">+{{ number_format($price_per_task, 2) }} {{ __('expert_dashboard.currency') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400">{{ __('expert_dashboard.no_activity') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="space-y-6">
                
                <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                    <div class="px-6 py-6">
                        <div class="flex flex-col items-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-green-600 to-green-700 rounded-full p-1 shadow-lg">
                                @if($user->avatar_path)
                                    <img src="{{ asset('storage/' . $user->avatar_path) }}" class="w-full h-full rounded-full object-cover bg-white" alt="{{ $user->full_name ?? $user->name }}">
                                @else
                                    @php
                                        $initials = '';
                                        $name = $user->full_name ?? $user->name ?? 'User';
                                        $nameParts = explode(' ', $name);
                                        foreach($nameParts as $part) {
                                            $initials .= mb_substr($part, 0, 1);
                                        }
                                        $initials = mb_substr($initials, 0, 2);
                                    @endphp
                                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center text-green-700 font-bold text-3xl">
                                        {{ strtoupper($initials) }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-4 text-center">
                                <h3 class="text-xl font-bold text-slate-800">{{ $user->full_name ?? $user->name }}</h3>
                                <p class="text-sm text-slate-500 mt-1">{{ !empty($user->job_title) ? __($user->job_title) : __('expert_dashboard.job_title_default') }}</p>
                                
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mt-4 {{ $badge_color }}">
                                    <i class="fa-solid {{ $badge_icon }}"></i>
                                    <span class="text-xs font-bold">{{ $expert_level }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3 border-t border-slate-100 pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('expert_dashboard.expert_number') }}</span>
                                <span class="font-mono font-bold text-slate-700">EXP-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('expert_dashboard.join_date') }}</span>
                                <span class="font-bold text-slate-700">{{ date('Y/m/d', strtotime($user->created_at)) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">{{ __('expert_dashboard.account_status') }}</span>
                                <span class="text-green-600 font-bold flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> {{ __('expert_dashboard.active') }}</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('dashboard.expert.cv-builder') }}" class="block w-full py-2 bg-slate-50 text-slate-600 text-center rounded-lg text-sm font-bold hover:bg-slate-100 transition">{{ __('expert_dashboard.update_profile') }}</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
                    <h4 class="font-bold text-sm text-slate-700 mb-3">{{ __('expert_dashboard.quick_actions') }}</h4>
                    @if($user->role !== 'student')
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('dashboard.expert.services') }}" class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-lg hover:bg-green-50 hover:text-green-700 transition cursor-pointer">
                            <i class="fa-solid fa-box-open mb-2 text-lg"></i>
                            <span class="text-xs font-bold">{{ __('expert_dashboard.services') }}</span>
                        </a>
                        <a href="{{ route('dashboard.expert.availability') }}" class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition cursor-pointer">
                            <i class="fa-regular fa-clock mb-2 text-lg"></i>
                            <span class="text-xs font-bold">{{ __('expert_dashboard.availability') }}</span>
                        </a>
                    </div>
                    @else
                    <div class="p-4 bg-slate-50 rounded-lg text-center text-slate-500 text-sm">
                        <i class="fa-solid fa-user-graduate mb-2 text-lg block"></i>
                        {{ __('expert_dashboard.student_account') }}
                    </div>
                    @endif
                </div>

            </div>
        </div>
        
        <div class="mt-12 text-center text-slate-400 text-xs">
            &copy; {{ date('Y') }} {{ __('expert_dashboard.copyright') }}
        </div>

    </div>
</body>
</html>
