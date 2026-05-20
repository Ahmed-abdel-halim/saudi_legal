<?php

namespace App\Console\Commands;

use App\Services\AzureSearchService;
use Illuminate\Console\Command;

/**
 * php artisan azure:setup
 * ─────────────────────────────────────────────────────────────────────────────
 * يتحقق من إعدادات Azure ويختبر الاتصال
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AzureSetup extends Command
{
    protected $signature   = 'azure:setup {--test : اختبار الاتصال فقط}';
    protected $description  = 'إعداد وفحص تكامل Azure';

    public function __construct(protected AzureSearchService $azure)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 فحص إعدادات Azure...');
        $this->newLine();

        // ─── فحص المتغيرات ───────────────────────────────────────────────────
        $checks = [
            'AZURE_SEARCH_ENDPOINT' => config('azure.search.endpoint'),
            'AZURE_SEARCH_KEY'      => config('azure.search.key'),
            'AZURE_SEARCH_INDEX'    => config('azure.search.index'),
            'AZURE_SEARCH_ENABLED'  => config('azure.search.enabled') ? 'true' : 'false',
            'AZURE_STORAGE_NAME'    => config('azure.storage.name'),
            'GEMINI_API_KEY'        => env('GEMINI_API_KEY'),
        ];

        $rows = [];
        $allOk = true;

        foreach ($checks as $key => $value) {
            $isEmpty = empty($value) || $value === 'false';
            if ($isEmpty && $key !== 'AZURE_SEARCH_ENABLED') {
                $allOk = false;
            }
            $rows[] = [
                $key,
                $isEmpty ? '❌ فارغ' : '✅ موجود',
                $isEmpty ? '' : mb_substr($value, 0, 30) . (mb_strlen($value) > 30 ? '...' : ''),
            ];
        }

        $this->table(['المتغير', 'الحالة', 'القيمة (مختصرة)'], $rows);

        if (! $allOk) {
            $this->newLine();
            $this->warn('⚠️  بعض المتغيرات مفقودة. أضفها في ملف .env:');
            $this->line('');
            $this->line('AZURE_SEARCH_ENDPOINT=https://YOUR-SERVICE.search.windows.net');
            $this->line('AZURE_SEARCH_KEY=YOUR_ADMIN_KEY');
            $this->line('AZURE_SEARCH_INDEX=legal-documents');
            $this->line('AZURE_SEARCH_ENABLED=true');
            $this->line('AZURE_STORAGE_NAME=radiifstore');
            $this->line('AZURE_STORAGE_KEY=YOUR_STORAGE_KEY');
            $this->newLine();
        }

        // ─── اختبار الاتصال إذا طُلب ─────────────────────────────────────────
        if ($this->option('test')) {
            $this->info('🔌 اختبار الاتصال بـ Azure AI Search...');

            $stats = $this->azure->getIndexStats();

            if ($stats) {
                $this->info('✅ الاتصال ناجح!');
                $this->table(
                    ['المقياس', 'القيمة'],
                    [
                        ['عدد الوثائق', $stats['documentCount'] ?? 'غير معروف'],
                        ['حجم التخزين', ($stats['storageSize'] ?? 0) . ' bytes'],
                    ]
                );
            } else {
                $this->error('❌ فشل الاتصال بـ Azure AI Search. تحقق من الـ endpoint والـ key.');
                return Command::FAILURE;
            }
        }

        // ─── تعليمات البدء ───────────────────────────────────────────────────
        $this->newLine();
        $this->info('📋 الخطوات التالية:');
        $this->line('  1. أضف متغيرات Azure في .env');
        $this->line('  2. php artisan azure:setup --test   # اختبار الاتصال');
        $this->line('  3. php artisan azure:index-legal --create-index --type=all --dry-run');
        $this->line('  4. php artisan azure:index-legal --create-index --type=all');
        $this->line('  5. في .env: AZURE_SEARCH_ENABLED=true');

        return Command::SUCCESS;
    }
}
