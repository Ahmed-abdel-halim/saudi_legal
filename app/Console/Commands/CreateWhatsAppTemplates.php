<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CreateWhatsAppTemplates extends Command
{
    protected $signature   = 'whatsapp:create-templates';
    protected $description = 'إنشاء تمبلتس WhatsApp التفاعلية في Twilio Content API وطباعة SIDs لإضافتها في .env';

    /**
     * تعريف كل تمبلت بأزراره — النص الثابت يأتي من ContentVariables عند الإرسال
     * حد الأزرار: 3 أزرار كحد أقصى، وكل عنوان زر ≤ 20 حرفاً
     */
    private array $templates = [

        // القائمة الرئيسية — تظهر عند بدء المحادثة وعند أي رسالة غير مفهومة
        'main_menu' => [
            'friendly_name' => 'radiif_main_menu_v1',
            'buttons' => [
                ['title' => 'الاطلاع على الباقات', 'id' => 'view_plans'],
                ['title' => 'المساعد القانوني',     'id' => 'start_chat'],
                ['title' => 'طلب تنقيح بيانات',     'id' => 'request_refinement'],
            ],
        ],

        // أزرار أثناء المحادثة مع المساعد القانوني
        'in_chat' => [
            'friendly_name' => 'radiif_in_chat_v1',
            'buttons' => [
                ['title' => 'إنهاء المحادثة',    'id' => 'end_chat'],
                ['title' => 'القائمة الرئيسية', 'id' => 'main_menu'],
            ],
        ],

        // بعد إنهاء المحادثة أو بعد انتهاء وقت الخمول
        'ended_chat' => [
            'friendly_name' => 'radiif_ended_chat_v1',
            'buttons' => [
                ['title' => 'تنشيط المحادثة',   'id' => 'start_chat'],
                ['title' => 'القائمة الرئيسية', 'id' => 'main_menu'],
            ],
        ],

        // بعد عرض الباقات
        'after_plans' => [
            'friendly_name' => 'radiif_after_plans_v1',
            'buttons' => [
                ['title' => 'المساعد القانوني',  'id' => 'start_chat'],
                ['title' => 'طلب تنقيح بيانات', 'id' => 'request_refinement'],
                ['title' => 'القائمة الرئيسية', 'id' => 'main_menu'],
            ],
        ],
    ];

    public function handle(): int
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');

        if (empty($sid) || empty($token)) {
            $this->error('❌ TWILIO_SID أو TWILIO_AUTH_TOKEN غير محددان في .env');
            return Command::FAILURE;
        }

        $this->info('🚀 بدء إنشاء تمبلتس WhatsApp في Twilio Content API...');
        $this->newLine();

        $createdSids = [];

        foreach ($this->templates as $key => $template) {
            $this->info("📋 جاري إنشاء تمبلت: [{$key}] → {$template['friendly_name']}");

            $payload = [
                'friendly_name' => $template['friendly_name'],
                'language'      => 'ar',
                'variables'     => ['1' => 'نص_الرسالة'],
                'types'         => [
                    'twilio/quick-reply' => [
                        'body'    => '{{1}}',
                        'actions' => $template['buttons'],
                    ],
                ],
            ];

            $response = Http::withBasicAuth($sid, $token)
                ->timeout(30)
                ->post('https://content.twilio.com/v1/Content', $payload);

            if ($response->successful()) {
                $templateSid           = $response->json('sid');
                $createdSids[$key]     = $templateSid;
                $this->info("  ✅ تم الإنشاء — SID: {$templateSid}");
            } else {
                $status = $response->status();
                $body   = $response->body();
                $this->error("  ❌ فشل الإنشاء (HTTP {$status}): {$body}");
            }
        }

        if (!empty($createdSids)) {
            $this->newLine();
            $this->info('════════════════════════════════════════════════');
            $this->info('📝 أضف هذه القيم إلى ملف .env:');
            $this->newLine();
            foreach ($createdSids as $key => $templateSid) {
                $envKey = 'TWILIO_TEMPLATE_' . strtoupper($key);
                $this->line("{$envKey}={$templateSid}");
            }
            $this->info('════════════════════════════════════════════════');
            $this->newLine();
            $this->warn('⚠️  تذكر: شغّل "php artisan config:clear" بعد تحديث .env');
        }

        return empty($createdSids) ? Command::FAILURE : Command::SUCCESS;
    }
}
