<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppConversation;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckWhatsAppInactivity extends Command
{
    /**
     * اسم ووصف الأمر البرمجي
     */
    protected $signature = 'whatsapp:check-inactivity';
    protected $description = 'متابعة خمول المحادثات وتذكير العميل بعد 10 دقائق وإنهاء المحادثة بعد 20 دقيقة';

    public function handle(TwilioService $twilio): int
    {
        $now = Carbon::now();

        // 1. التنبيه الأول: مر على آخر نشاط 10 دقائق في محادثة نشطة ولم يتم تنبيه العميل بعد
        $conversationsToWarn = WhatsAppConversation::where('session_state', 'in_chat')
            ->whereNull('inactivity_warned_at')
            ->where('last_active_at', '<=', $now->copy()->subMinutes(10))
            ->get();

        foreach ($conversationsToWarn as $conv) {
            $msg = "هل تحتاج اي معلومة اخرى بخصوص استفسارك ؟";
            $buttons = ['إنهاء المحادثة 🛑', 'القائمة الرئيسية 🏠'];

            $sent = $twilio->sendMessage($conv->phone_number, $msg, $buttons);

            if ($sent) {
                $conv->update(['inactivity_warned_at' => Carbon::now()]);
                Log::info('[WhatsApp Inactivity] أُرسل تنبيه الخمول إلى: ' . $conv->phone_number);
                $this->info("Sent 10-min warning to {$conv->phone_number}");
            }
        }

        // 2. الإنهاء التلقائي: مر على التنبيه الأول 10 دقائق أخرى بدون أي رد من العميل
        $conversationsToEnd = WhatsAppConversation::where('session_state', 'in_chat')
            ->whereNotNull('inactivity_warned_at')
            ->where('inactivity_warned_at', '<=', $now->copy()->subMinutes(10))
            ->get();

        foreach ($conversationsToEnd as $conv) {
            $msg = "سيتم انهاء هذه المحادثة الان\nيمكنك البدء من جديد في أي وقت فقط اختر تنشيط المحادثة";
            $buttons = ['تنشيط المحادثة 🔄', 'القائمة الرئيسية 🏠'];

            $sent = $twilio->sendMessage($conv->phone_number, $msg, $buttons);

            $conv->update([
                'session_state'        => 'idle',
                'inactivity_warned_at' => null,
            ]);

            Log::info('[WhatsApp Inactivity] تم إنهاء جلسة المحادثة بسبب الخمول للرقم: ' . $conv->phone_number);
            $this->info("Ended conversation due to inactivity for {$conv->phone_number}");
        }

        return Command::SUCCESS;
    }
}
