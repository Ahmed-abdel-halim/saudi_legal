@extends('layouts.admin')

@section('title', __('admin.system_settings') ?? 'System Settings')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{!! __('admin.system_settings') ?? 'System Settings' !!}</h1>
        <p class="text-slate-500 mt-1">{!! __('admin.system_settings_desc') ?? 'Manage global platform configuration, financials, and security rules.' !!}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8 pb-10">
    
    {{-- Left Side: Navigation --}}
    <div class="lg:col-span-1">
        <nav class="flex flex-col gap-2 relative sticky top-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider pl-4 mb-2">{!! __('admin.configuration') ?? 'Configuration Menu' !!}</h3>
            
            <a href="#" data-tab="general" class="tab-link bg-primary text-white font-bold px-5 py-4 rounded-xl shadow-[0_4px_15px_-3px_rgba(79,70,229,0.3)] border border-primary text-sm flex items-center justify-between group transition active">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-sliders tab-icon text-indigo-200 text-lg w-5 text-center"></i> {!! __('admin.general_settings') ?? 'General Settings' !!}
                </div>
                <i class="fa-solid fa-chevron-right tab-chevron text-xs opacity-70 rtl:rotate-180"></i>
            </a>
            
            <a href="#" data-tab="financials" class="tab-link bg-white text-slate-600 font-bold px-5 py-4 rounded-xl hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between group transition shadow-sm hover:shadow-md hover:border-slate-300">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-wallet tab-icon text-slate-400 transition text-lg w-5 text-center"></i> {!! __('admin.financials_fees') ?? 'Financial Configuration' !!}
                </div>
                <i class="fa-solid fa-chevron-right tab-chevron text-xs text-slate-300 transition rtl:rotate-180"></i>
            </a>
            
            <a href="#" data-tab="emails" class="tab-link bg-white text-slate-600 font-bold px-5 py-4 rounded-xl hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between group transition shadow-sm hover:shadow-md hover:border-slate-300">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-envelope tab-icon text-slate-400 transition text-lg w-5 text-center"></i> {!! __('admin.email_templates') ?? 'Email & Notifications' !!}
                </div>
                <i class="fa-solid fa-chevron-right tab-chevron text-xs text-slate-300 transition rtl:rotate-180"></i>
            </a>
            
            <a href="#" data-tab="security" class="tab-link bg-white text-slate-600 font-bold px-5 py-4 rounded-xl hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between group transition shadow-sm hover:shadow-md hover:border-slate-300">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-shield-halved tab-icon text-slate-400 transition text-lg w-5 text-center"></i> {!! __('admin.security_access') ?? 'Security & Access' !!}
                </div>
                <i class="fa-solid fa-chevron-right tab-chevron text-xs text-slate-300 transition rtl:rotate-180"></i>
            </a>

            <a href="#" data-tab="rewards" class="tab-link bg-white text-slate-600 font-bold px-5 py-4 rounded-xl hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between group transition shadow-sm hover:shadow-md hover:border-slate-300">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-coins tab-icon text-slate-400 transition text-lg w-5 text-center"></i> {!! __('admin.task_rewards') ?? 'Task Rewards' !!}
                </div>
                <i class="fa-solid fa-chevron-right tab-chevron text-xs text-slate-300 transition rtl:rotate-180"></i>
            </a>
            
            
        </nav>
    </div>

    {{-- Right Side: Form Content --}}
    <div class="lg:col-span-3" id="settings-panel">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div id="tab-general" class="tab-content bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden min-h-[500px]">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{!! __('admin.general_configuration') ?? 'General Preferences' !!}</h3>
                    <p class="text-sm text-slate-500 mt-1">{!! __('admin.general_configuration_desc') ?? 'Base settings affecting platform operations.' !!}</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-sliders text-xl text-primary block"></i>
                </div>
            </div>
            
            <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="space-y-8">
                    {{-- Row 1 --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.platform_fee') ?? 'Platform Transaction Fee (%)' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 rtl:pr-4 rtl:pl-0 pointer-events-none">
                                    <i class="fa-solid fa-percent text-slate-400"></i>
                                </div>
                                <input type="number" name="platform_fee_percent" value="{{ old('platform_fee_percent', $settings['platform_fee_percent']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                            <p class="mt-2 text-xs text-slate-500 font-medium"><i class="fa-solid fa-circle-info mr-1 text-slate-400"></i> {!! __('admin.fee_deduction_note') ?? 'Amount deducted from expert payout' !!}</p>
                            @error('platform_fee_percent') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.default_currency') ?? 'Default Display Currency' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 rtl:pr-4 rtl:pl-0 pointer-events-none">
                                    <i class="fa-solid fa-money-bill-wave text-slate-400"></i>
                                </div>
                                <select name="default_currency" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner appearance-none" required>
                                    <option value="SAR" {{ $settings['default_currency'] == 'SAR' ? 'selected' : '' }}>SAR (Saudi Riyal)</option>
                                    <option value="USD" {{ $settings['default_currency'] == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                    <option value="EUR" {{ $settings['default_currency'] == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 rtl:pl-4 rtl:pr-0">
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                                </div>
                            </div>
                            @error('default_currency') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 2 --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.support_email') ?? 'Official Support Contact Email' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 rtl:pr-4 rtl:pl-0 pointer-events-none">
                                    <i class="fa-solid fa-envelope-open-text text-slate-400"></i>
                                </div>
                                <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                            @error('support_email') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.max_upload_size') ?? 'Max File Upload Size (MB)' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 rtl:pr-4 rtl:pl-0 pointer-events-none">
                                    <i class="fa-solid fa-file-arrow-up text-slate-400"></i>
                                </div>
                                <input type="number" name="max_upload_size" value="{{ old('max_upload_size', $settings['max_upload_size']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                            @error('max_upload_size') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="py-2"><hr class="border-slate-100"></div>

                    {{-- Toggle --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-power-off text-slate-400"></i> {!! __('admin.enable_maintenance') ?? 'Enable Maintenance Mode' !!}
                            </h4>
                            <p class="text-xs text-slate-500 mt-1 font-medium leading-relaxed max-w-xl">{!! __('admin.maintenance_desc') ?? 'Temporarily block access to all users except super admins. Useful when deploying major updates or database migrations.' !!}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer mb-0">
                            <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                            <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-500 border border-slate-400 peer-checked:border-red-600 shadow-inner"></div>
                        </label>
                    </div>

                </div>

                <div class="mt-10 flex justify-end">
                    <button type="submit" class="text-white bg-primary hover:bg-primary/90 focus:ring-4 focus:outline-none focus:ring-primary/30 font-bold rounded-xl text-sm px-8 py-3.5 text-center shadow-[0_4px_15px_-3px_rgba(79,70,229,0.3)] transition flex items-center gap-2 hover:-translate-y-0.5 transform">
                        <i class="fa-solid fa-save text-indigo-200"></i> {!! __('admin.save_settings') ?? 'Save Configuration Changes' !!}
                    </button>
                </div>
            </form>
        </div>

        {{-- Financials & Fees Tab --}}
        <div id="tab-financials" class="tab-content hidden bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{!! __('admin.financials_fees') ?? 'Financials & Fees' !!}</h3>
                    <p class="text-sm text-slate-500 mt-1">{!! __('admin.financials_placeholder_desc') ?? 'Manage payout gateways, taxation rules, and invoice settings.' !!}</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-wallet text-xl text-emerald-500 block"></i>
                </div>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8">
                @csrf @method('PUT')
                <input type="hidden" name="tab" value="financials">
                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.min_payout_amount') ?? 'Minimum Payout Amount' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none">
                                    <i class="fa-solid fa-coins text-slate-400"></i>
                                </div>
                                <input type="number" name="min_payout_amount" value="{{ old('min_payout_amount', $settings['min_payout_amount']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.payout_schedule') ?? 'Payout Schedule' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none">
                                    <i class="fa-solid fa-calendar-days text-slate-400"></i>
                                </div>
                                <select name="payout_schedule" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner appearance-none">
                                    <option value="weekly"    {{ $settings['payout_schedule'] == 'weekly'    ? 'selected' : '' }}>{!! __('admin.weekly') ?? 'Weekly' !!}</option>
                                    <option value="monthly"   {{ $settings['payout_schedule'] == 'monthly'   ? 'selected' : '' }}>{!! __('admin.monthly') ?? 'Monthly' !!}</option>
                                    <option value="quarterly" {{ $settings['payout_schedule'] == 'quarterly' ? 'selected' : '' }}>{!! __('admin.quarterly') ?? 'Quarterly' !!}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.tax_rate') ?? 'Tax Rate (%)' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-percent text-slate-400"></i></div>
                                <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.invoice_prefix') ?? 'Invoice Prefix' !!}</label>
                            <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full p-3.5 transition outline-none shadow-inner" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.escrow_hold_days') ?? 'Escrow Hold (days)' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-clock text-slate-400"></i></div>
                                <input type="number" name="escrow_hold_days" value="{{ old('escrow_hold_days', $settings['escrow_hold_days']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.vat_number') ?? 'VAT Number' !!}</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number', $settings['vat_number']) }}" placeholder="e.g. 300123456789003" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full p-3.5 transition outline-none shadow-inner">
                    </div>
                </div>
                <div class="mt-10 flex justify-end">
                    <button type="submit" class="text-white bg-emerald-500 hover:bg-emerald-600 font-bold rounded-xl text-sm px-8 py-3.5 shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> {!! __('admin.save_settings') ?? 'Save Configuration Changes' !!}
                    </button>
                </div>
            </form>
        </div>

        {{-- Email Templates Tab --}}
        <div id="tab-emails" class="tab-content hidden bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{!! __('admin.email_templates') ?? 'Email & Notifications' !!}</h3>
                    <p class="text-sm text-slate-500 mt-1">{!! __('admin.emails_placeholder_desc') ?? 'Configure SMTP settings and automated notifications.' !!}</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-envelope text-xl text-sky-500 block"></i>
                </div>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8">
                @csrf @method('PUT')
                <input type="hidden" name="tab" value="emails">
                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.mail_host') ?? 'SMTP Host' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-server text-slate-400"></i></div>
                                <input type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.mail_port') ?? 'SMTP Port' !!}</label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full p-3.5 transition outline-none shadow-inner" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.mail_username') ?? 'SMTP Username' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-user text-slate-400"></i></div>
                                <input type="text" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner">
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.mail_password') ?? 'SMTP Password' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-lock text-slate-400"></i></div>
                                <input type="password" name="mail_password" placeholder="{{ __('admin.leave_blank_no_change') ?? 'Leave blank to keep current' }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.mail_from_name') ?? 'From Name' !!}</label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full p-3.5 transition outline-none shadow-inner" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.mail_from_address') ?? 'From Email Address' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-at text-slate-400"></i></div>
                                <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">{!! __('admin.enable_notifications') ?? 'Enable Email Notifications' !!}</h4>
                            <p class="text-xs text-slate-500 mt-1">{!! __('admin.enable_notifications_desc') ?? 'Send automated emails for task assignments, completions, and alerts.' !!}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="mail_notifications" value="1" class="sr-only peer" {{ $settings['mail_notifications'] ? 'checked' : '' }}>
                            <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-sky-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-sky-500 border border-slate-400 shadow-inner"></div>
                        </label>
                    </div>
                </div>
                <div class="mt-10 flex justify-end">
                    <button type="submit" class="text-white bg-sky-500 hover:bg-sky-600 font-bold rounded-xl text-sm px-8 py-3.5 shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> {!! __('admin.save_settings') ?? 'Save Configuration Changes' !!}
                    </button>
                </div>
            </form>
        </div>
        {{-- Security & Access Tab --}}
        <div id="tab-security" class="tab-content hidden bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{!! __('admin.security_access') ?? 'Security & Access' !!}</h3>
                    <p class="text-sm text-slate-500 mt-1">{!! __('admin.security_placeholder_desc') ?? 'Manage ACL, login policies, 2FA enforcement, and IP whitelists.' !!}</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-shield-halved text-xl text-red-500 block"></i>
                </div>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8">
                @csrf @method('PUT')
                <input type="hidden" name="tab" value="security">
                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.session_timeout') ?? 'Session Timeout (min)' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-hourglass text-slate-400"></i></div>
                                <input type="number" name="session_timeout" value="{{ old('session_timeout', $settings['session_timeout']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.max_login_attempts') ?? 'Max Login Attempts' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-key text-slate-400"></i></div>
                                <input type="number" name="max_login_attempts" value="{{ old('max_login_attempts', $settings['max_login_attempts']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.lockout_minutes') ?? 'Lockout Duration (min)' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none"><i class="fa-solid fa-ban text-slate-400"></i></div>
                                <input type="number" name="lockout_minutes" value="{{ old('lockout_minutes', $settings['lockout_minutes']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.ip_whitelist') ?? 'Admin IP Whitelist' !!}</label>
                        <textarea name="ip_whitelist" rows="3" placeholder="192.168.1.1, 10.0.0.0/24" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full p-3.5 transition outline-none shadow-inner font-mono text-sm">{{ old('ip_whitelist', $settings['ip_whitelist']) }}</textarea>
                        <p class="mt-2 text-xs text-slate-500">{!! __('admin.ip_whitelist_hint') ?? 'Comma-separated IPs or CIDR ranges. Leave empty to allow all.' !!}</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-mobile-screen text-slate-400"></i> {!! __('admin.require_2fa') ?? 'Require 2FA for Admins' !!}</h4>
                            <p class="text-xs text-slate-500 mt-1">{!! __('admin.require_2fa_desc') ?? 'Force all admin users to enroll in two-factor authentication.' !!}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="admin_2fa" value="1" class="sr-only peer" {{ $settings['admin_2fa'] ? 'checked' : '' }}>
                            <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-500 border border-slate-400 shadow-inner"></div>
                        </label>
                    </div>
                </div>
                <div class="mt-10 flex justify-end">
                    <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-bold rounded-xl text-sm px-8 py-3.5 shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> {!! __('admin.save_settings') ?? 'Save Configuration Changes' !!}
                    </button>
                </div>
            </form>
        </div>


        {{-- Task Rewards Tab --}}
        <div id="tab-rewards" class="tab-content hidden bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{!! __('admin.task_rewards') ?? 'Task Rewards' !!}</h3>
                    <p class="text-sm text-slate-500 mt-1">{!! __('admin.task_rewards_desc') ?? 'Define the amounts experts receive for each type of task.' !!}</p>
                </div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-slate-200 shadow-sm">
                    <i class="fa-solid fa-coins text-xl text-amber-500 block"></i>
                </div>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8">
                @csrf @method('PUT')
                <input type="hidden" name="tab" value="rewards">
                <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.price_per_ai_task') ?? 'AI Task Reward' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none">
                                    <i class="fa-solid fa-robot text-slate-400"></i>
                                </div>
                                <input type="number" step="0.01" name="price_per_ai_task" value="{{ old('price_per_ai_task', $settings['price_per_ai_task']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.price_per_legal_task') ?? 'Legal Assistant Task Reward' !!}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none">
                                    <i class="fa-solid fa-gavel text-slate-400"></i>
                                </div>
                                <input type="number" step="0.01" name="price_per_legal_task" value="{{ old('price_per_legal_task', $settings['price_per_legal_task']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-bold text-slate-700">{!! __('admin.price_per_linguistic_task') ?? 'Refining / Linguistic Task Reward' !!}</label>
                        <div class="relative max-w-md">
                            <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-4 rtl:pr-4 pointer-events-none">
                                <i class="fa-solid fa-pen-nib text-slate-400"></i>
                            </div>
                            <input type="number" step="0.01" name="price_per_linguistic_task" value="{{ old('price_per_linguistic_task', $settings['price_per_linguistic_task']) }}" class="bg-slate-50 border border-slate-200 text-slate-800 font-bold focus:bg-white rounded-xl focus:ring-primary focus:border-primary block w-full pl-12 rtl:pr-12 rtl:pl-4 p-3.5 transition outline-none shadow-inner" required>
                        </div>
                        <p class="mt-2 text-xs text-slate-500"><i class="fa-solid fa-info-circle mr-1"></i> This reward applies to Sentiment Analysis and Sentence Correction tasks.</p>
                    </div>
                </div>
                <div class="mt-10 flex justify-end">
                    <button type="submit" class="text-white bg-amber-500 hover:bg-amber-600 font-bold rounded-xl text-sm px-8 py-3.5 shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> {!! __('admin.save_settings') ?? 'Save Configuration Changes' !!}
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-link');

        const activeClasses = ['bg-primary', 'text-white', 'shadow-[0_4px_15px_-3px_rgba(79,70,229,0.3)]', 'border-primary', 'active'];
        const inactiveClasses = ['bg-white', 'text-slate-600', 'hover:bg-slate-50', 'border-slate-200', 'shadow-sm', 'hover:shadow-md', 'hover:border-slate-300'];




        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-tab');

                // Hide all panels
                document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.add('hidden'); });

                // Reset all tabs
                tabs.forEach(function(t) {
                    t.classList.remove.apply(t.classList, activeClasses);
                    t.classList.add.apply(t.classList, inactiveClasses);
                    var ic = t.querySelector('.tab-icon');
                    if (ic) { ic.classList.remove('text-indigo-200'); ic.classList.add('text-slate-400'); }
                    var chv = t.querySelector('.tab-chevron');
                    if (chv) { chv.classList.remove('opacity-70'); chv.classList.add('text-slate-300'); }
                });

                // Show target
                var target = document.getElementById('tab-' + targetId);
                if (target) { target.classList.remove('hidden'); }

                // Activate current tab
                this.classList.remove.apply(this.classList, inactiveClasses);
                this.classList.add.apply(this.classList, activeClasses);
                var curIc = this.querySelector('.tab-icon');
                if (curIc) { curIc.classList.remove('text-slate-400'); curIc.classList.add('text-indigo-200'); }
                var curChv = this.querySelector('.tab-chevron');
                if (curChv) { curChv.classList.remove('text-slate-300'); curChv.classList.add('opacity-70'); }
            });
        });
    });
</script>
@endpush
