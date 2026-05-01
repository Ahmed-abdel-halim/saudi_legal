<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    /**
     * All settings with their defaults.
     */
    private function defaults(): array
    {
        return [
            // General
            'platform_fee_percent' => 15,
            'default_currency'     => 'SAR',
            'support_email'        => 'support@radiif.com',
            'maintenance_mode'     => '0',
            'max_upload_size'      => 10,

            // Financials
            'min_payout_amount'    => 100,
            'payout_schedule'      => 'monthly',
            'tax_rate'             => 15,
            'vat_number'           => '',
            'invoice_prefix'       => 'INV-',
            'escrow_hold_days'     => 7,

            // Email / SMTP
            'mail_host'            => 'smtp.mailtrap.io',
            'mail_port'            => 587,
            'mail_username'        => '',
            'mail_from_name'       => 'Radiif',
            'mail_from_address'    => 'no-reply@radiif.com',
            'mail_notifications'   => '1',

            // Task Rewards
            'price_per_ai_task'    => 5.00,
            'price_per_legal_task' => 5.00,
            'price_per_linguistic_task' => 0.25,

            // Security
            'session_timeout'      => 120,
            'max_login_attempts'   => 5,
            'lockout_minutes'      => 30,
            'admin_2fa'            => '0',
            'ip_whitelist'         => '',
        ];
    }

    /**
     * Display the global system settings page.
     */
    public function index()
    {
        $raw      = SiteSetting::all_settings();
        $defaults = $this->defaults();
        $settings = [];

        foreach ($defaults as $key => $default) {
            $settings[$key] = $raw[$key] ?? $default;
        }

        // Seed any missing keys to the DB
        foreach ($defaults as $key => $default) {
            if (!isset($raw[$key])) {
                SiteSetting::set($key, $default);
            }
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the global system settings.
     */
    public function update(Request $request)
    {
        $tab = $request->input('tab', 'general');

        switch ($tab) {
            case 'financials':
                $request->validate([
                    'min_payout_amount' => 'required|numeric|min:0',
                    'payout_schedule'   => 'required|in:weekly,monthly,quarterly',
                    'tax_rate'          => 'required|numeric|min:0|max:100',
                    'vat_number'        => 'nullable|string|max:50',
                    'invoice_prefix'    => 'required|string|max:10',
                    'escrow_hold_days'  => 'required|integer|min:1|max:90',
                ]);
                SiteSetting::setMany([
                    'min_payout_amount' => $request->min_payout_amount,
                    'payout_schedule'   => $request->payout_schedule,
                    'tax_rate'          => $request->tax_rate,
                    'vat_number'        => $request->vat_number,
                    'invoice_prefix'    => $request->invoice_prefix,
                    'escrow_hold_days'  => $request->escrow_hold_days,
                ]);
                break;

            case 'emails':
                $request->validate([
                    'mail_host'          => 'required|string|max:100',
                    'mail_port'          => 'required|integer',
                    'mail_username'      => 'nullable|string|max:150',
                    'mail_from_name'     => 'required|string|max:80',
                    'mail_from_address'  => 'required|email',
                ]);
                SiteSetting::setMany([
                    'mail_host'           => $request->mail_host,
                    'mail_port'           => $request->mail_port,
                    'mail_username'       => $request->mail_username,
                    'mail_from_name'      => $request->mail_from_name,
                    'mail_from_address'   => $request->mail_from_address,
                    'mail_notifications'  => $request->has('mail_notifications') ? '1' : '0',
                ]);
                if ($request->filled('mail_password')) {
                    SiteSetting::set('mail_password', bcrypt($request->mail_password));
                }
                break;

            case 'security':
                $request->validate([
                    'session_timeout'   => 'required|integer|min:5|max:1440',
                    'max_login_attempts'=> 'required|integer|min:1|max:20',
                    'lockout_minutes'   => 'required|integer|min:1|max:1440',
                    'ip_whitelist'      => 'nullable|string',
                ]);
                SiteSetting::setMany([
                    'session_timeout'    => $request->session_timeout,
                    'max_login_attempts' => $request->max_login_attempts,
                    'lockout_minutes'    => $request->lockout_minutes,
                    'admin_2fa'          => $request->has('admin_2fa') ? '1' : '0',
                    'ip_whitelist'       => $request->ip_whitelist,
                ]);
                break;

            case 'rewards':
                $request->validate([
                    'price_per_ai_task'         => 'required|numeric|min:0',
                    'price_per_legal_task'      => 'required|numeric|min:0',
                    'price_per_linguistic_task' => 'required|numeric|min:0',
                ]);
                SiteSetting::setMany([
                    'price_per_ai_task'         => $request->price_per_ai_task,
                    'price_per_legal_task'      => $request->price_per_legal_task,
                    'price_per_linguistic_task' => $request->price_per_linguistic_task,
                ]);
                break;

            default: // general
                $request->validate([
                    'platform_fee_percent' => 'required|numeric|min:0|max:100',
                    'default_currency'     => 'required|string|size:3',
                    'support_email'        => 'required|email',
                    'max_upload_size'      => 'required|integer|min:1|max:500',
                ]);
                SiteSetting::setMany([
                    'platform_fee_percent' => $request->platform_fee_percent,
                    'default_currency'     => $request->default_currency,
                    'support_email'        => $request->support_email,
                    'max_upload_size'      => $request->max_upload_size,
                    'maintenance_mode'     => $request->has('maintenance_mode') ? '1' : '0',
                ]);
                break;
        }

        return back()->with('success', __('admin.settings_saved_success') ?? 'Settings saved successfully!');
    }
}
