<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('expert_settings.title') }} | Radiif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f8fafc; }
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
                <a href="{{ route('dashboard.expert') }}" class="text-slate-600 hover:text-green-600 transition text-sm font-medium">
                    <i class="fa-solid fa-arrow-left ml-2"></i> {{ __('expert_settings.back_to_dashboard') }}
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-5xl">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ __('expert_settings.title') }}</h1>
            <p class="text-slate-600">{{ __('expert_settings.subtitle') }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fa-solid fa-circle-check ml-2"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.expert.settings') }}">
            @csrf

            <!-- Profile Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user text-green-600"></i> {{ __('expert_settings.profile_settings') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.full_name') }}</label>
                        <input type="text" name="full_name" value="{{ $user->full_name ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.email') }}</label>
                        <input type="email" name="email" value="{{ $user->email ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.phone') }}</label>
                        <input type="tel" name="phone" value="{{ $user->phone ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.job_title') }}</label>
                        <input type="text" name="job_title" value="{{ $user->job_title ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-shield text-green-600"></i> {{ __('expert_settings.security_settings') }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.current_password') }}</label>
                        <input type="password" name="current_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.new_password') }}</label>
                            <input type="password" name="new_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.confirm_password') }}</label>
                            <input type="password" name="confirm_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                        <div>
                            <p class="font-medium text-slate-700">{{ __('expert_settings.two_factor_auth') }}</p>
                            <p class="text-sm text-slate-500">{{ __('expert_settings.2fa_status') }}: <span class="text-red-600 font-medium">{{ __('expert_settings.disabled') }}</span></p>
                        </div>
                        <button type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
                            {{ __('expert_settings.enable_2fa') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-green-600"></i> {{ __('expert_settings.notification_settings') }}
                </h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg">
                        <label class="font-medium text-slate-700">{{ __('expert_settings.new_task_notification') }}</label>
                        <input type="checkbox" name="notify_new_task" checked class="w-5 h-5 text-green-600 rounded">
                    </div>
                    <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg">
                        <label class="font-medium text-slate-700">{{ __('expert_settings.payment_notification') }}</label>
                        <input type="checkbox" name="notify_payment" checked class="w-5 h-5 text-green-600 rounded">
                    </div>
                    <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg">
                        <label class="font-medium text-slate-700">{{ __('expert_settings.message_notification') }}</label>
                        <input type="checkbox" name="notify_message" checked class="w-5 h-5 text-green-600 rounded">
                    </div>
                    <div class="flex items-center justify-between p-3 hover:bg-slate-50 rounded-lg">
                        <label class="font-medium text-slate-700">{{ __('expert_settings.system_notification') }}</label>
                        <input type="checkbox" name="notify_system" class="w-5 h-5 text-green-600 rounded">
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-green-600"></i> {{ __('expert_settings.payment_settings') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.account_holder_name') }}</label>
                        <input type="text" name="account_holder" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.bank_name') }}</label>
                        <input type="text" name="bank_name" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.iban') }}</label>
                        <input type="text" name="iban" placeholder="SA00 0000 0000 0000 0000 0000" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.minimum_payout') }}</label>
                        <input type="number" name="min_payout" value="100" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="flex items-center p-4 bg-slate-50 rounded-lg">
                        <input type="checkbox" name="auto_payout" id="auto_payout" class="w-5 h-5 text-green-600 rounded ml-3">
                        <label for="auto_payout" class="text-sm text-slate-700">{{ __('expert_settings.enable_auto_payout') }}</label>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-green-600"></i> {{ __('expert_settings.privacy_settings') }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('expert_settings.profile_visibility') }}</label>
                        <select name="profile_visibility" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            @foreach(__('expert_settings.visibility_options') as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center p-3 hover:bg-slate-50 rounded-lg">
                            <input type="checkbox" name="show_email" id="show_email" class="w-5 h-5 text-green-600 rounded ml-3">
                            <label for="show_email" class="text-sm text-slate-700">{{ __('expert_settings.show_email') }}</label>
                        </div>
                        <div class="flex items-center p-3 hover:bg-slate-50 rounded-lg">
                            <input type="checkbox" name="show_phone" id="show_phone" class="w-5 h-5 text-green-600 rounded ml-3">
                            <label for="show_phone" class="text-sm text-slate-700">{{ __('expert_settings.show_phone') }}</label>
                        </div>
                        <div class="flex items-center p-3 hover:bg-slate-50 rounded-lg">
                            <input type="checkbox" name="allow_messages" id="allow_messages" checked class="w-5 h-5 text-green-600 rounded ml-3">
                            <label for="allow_messages" class="text-sm text-slate-700">{{ __('expert_settings.allow_messages') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 justify-end">
                <button type="button" class="px-6 py-3 border border-slate-300 text-slate-600 rounded-xl font-medium hover:bg-slate-50 transition">
                    {{ __('expert_settings.cancel') }}
                </button>
                <button type="submit" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition">
                    <i class="fa-solid fa-save ml-2"></i> {{ __('expert_settings.save_changes') }}
                </button>
            </div>
        </form>

    </div>

</body>
</html>
