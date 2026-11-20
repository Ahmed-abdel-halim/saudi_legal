<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CreateWhatsAppTemplates extends Command
{
    protected $signature   = 'whatsapp:create-templates {--only= : اسم تمبلت محدد فقط مثل main_menu}';
    protected $description = 'إنشاء تمبلتس WhatsApp التفاعلية في Twilio Content API وطباعة SIDs لإضافتها في .env';

    /**
     * تعريف كل تمبلت:
     * - type = 'list-picker': قائمة خيارات منبثقة (تتسع حتى 10 خيارات كاملة بنقرة واحدة)
     * - type = 'quick-reply': أزرار سريعة مباشرة (حد أقصى 3 أزرار)
     */
    private array $templates = [

        // القائمة الرئيسية — قائمة تفاعلية منبثقة (List Picker) تضم الـ 5 خيارات كاملة بنقرة واحدة
        'main_menu' => [
            'friendly_name' => 'radiif_main_menu_list_v1',
            'type'          => 'list-picker',
            'button'        => 'اختر من القائمة 👇',
            'items'         => [
                ['item' => 'المساعد القانوني',     'id' => 'start_chat',         'description' => 'استشارات وأنظمة قضائية سعودية'],
                ['item' => 'المفتي الذكي ⚖️',     'id' => 'sharia_chat',        'description' => 'فتاوى وأحكام شرعية موثقة'],
                ['item' => 'ديمو مياه بيرين 💧',  'id' => 'berain_demo',        'description' => 'عناية العملاء بمياه بيرين (نادية)'],
                ['item' => 'الاطلاع على الباقات', 'id' => 'view_plans',         'description' => 'باقات وأسعار منصة رديف'],
                ['item' => 'طلب تنقيح بيانات',   'id' => 'request_refinement', 'description' => 'طلب تدقيق وتحديث البيانات'],
            ],
        ],

        // أزرار أثناء محادثة المفتي الذكي
        'in_sharia_chat' => [
            'friendly_name' => 'radiif_in_sharia_chat_v1',
            'type'          => 'quick-reply',
            'buttons'       => [
                ['title' => 'إنهاء المحادثة',    'id' => 'end_chat'],
                ['title' => 'القائمة الرئيسية', 'id' => 'main_menu'],
            ],
        ],

        // أزرار أثناء محادثة ديمو مياه بيرين مع نادية
        'in_berain_chat' => [
            'friendly_name' => 'radiif_in_berain_chat_v1',
            'type'          => 'quick-reply',
            'buttons'       => [
                ['title' => '🔄 تكرار طلبي الأخير', 'id' => 'repeat_order'],
                ['title' => 'إنهاء المحادثة',      'id' => 'end_chat'],
                ['title' => 'القائمة الرئيسية',   'id' => 'main_menu'],
            ],
        ],

        // أزرار أثناء المحادثة مع المساعد القانوني
        'in_chat' => [
            'friendly_name' => 'radiif_in_chat_v1',
            'type'          => 'quick-reply',
            'buttons'       => [
                ['title' => 'إنهاء المحادثة',    'id' => 'end_chat'],
                ['title' => 'القائمة الرئيسية', 'id' => 'main_menu'],
            ],
        ],

        // بعد إنهاء المحادثة أو بعد انتهاء وقت الخمول
        'ended_chat' => [
            'friendly_name' => 'radiif_ended_chat_v1',
            'type'          => 'quick-reply',
            'buttons'       => [
                ['title' => 'تنشيط المحادثة',   'id' => 'start_chat'],
                ['title' => 'القائمة الرئيسية', 'id' => 'main_menu'],
            ],
        ],

        // بعد عرض الباقات
        'after_plans' => [
            'friendly_name' => 'radiif_after_plans_v1',
            'type'          => 'quick-reply',
            'buttons'       => [
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

        $only = $this->option('only');
        $templatesToCreate = $this->templates;
        if (!empty($only)) {
            if (!isset($this->templates[$only])) {
                $this->error("❌ التمبلت المحدد [{$only}] غير موجود.");
                return Command::FAILURE;
            }
            $templatesToCreate = [$only => $this->templates[$only]];
        }

        $this->info('🚀 بدء إنشاء تمبلتس WhatsApp في Twilio Content API...');
        $this->newLine();

        $createdSids = [];

        foreach ($templatesToCreate as $key => $template) {
            $this->info("📋 جاري إنشاء تمبلت: [{$key}] → {$template['friendly_name']}");

            $types = [];
            if (($template['type'] ?? 'quick-reply') === 'list-picker') {
                $types['twilio/list-picker'] = [
                    'body'   => '{{1}}',
                    'button' => $template['button'] ?? 'اختر من القائمة 👇',
                    'items'  => $template['items'],
                ];
            } else {
                $types['twilio/quick-reply'] = [
                    'body'    => '{{1}}',
                    'actions' => $template['buttons'],
                ];
            }

            $payload = [
                'friendly_name' => $template['friendly_name'],
                'language'      => 'ar',
                'variables'     => ['1' => 'نص_الرسالة'],
                'types'         => $types,
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
