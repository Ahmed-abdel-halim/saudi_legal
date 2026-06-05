<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalArticle;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ImportMcpLaws extends Command
{
    protected $signature = 'import:mcp-laws {--fresh : Truncate all articles before importing}';
    protected $description = 'Import Saudi laws and articles from MCP JSON files';

    public function handle()
    {
        $directory = base_path('Saudi-law-mcp-main/data/seed');

        if (!File::exists($directory)) {
            $this->error("Directory not found: {$directory}");
            return;
        }

        // Option --fresh: truncate table first
        if ($this->option('fresh')) {
            $currentCount = LegalArticle::count();
            if ($this->confirm("⚠️ هل أنت متأكد؟ سيتم حذف جميع المواد ({$currentCount} مادة) وإعادة استيرادها من الصفر؟", true)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('legal_citations')->whereNotNull('legal_article_id')->update(['legal_article_id' => null]);
                LegalArticle::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->warn("✓ تم تصفير جدول المواد القانونية.");
            } else {
                $this->info("تم الإلغاء.");
                return;
            }
        }

        $files = File::files($directory);
        $jsonFiles = collect($files)->filter(fn($f) => $f->getExtension() === 'json');
        $this->info("Found {$jsonFiles->count()} JSON files. Starting import...");

        $bar = $this->output->createProgressBar($jsonFiles->count());
        $bar->start();

        $totalArticles = 0;
        $totalLaws = 0;
        $errors = [];

        foreach ($jsonFiles as $file) {
            $content = File::get($file->getPathname());
            $data = json_decode($content, true);

            if (!$data || !isset($data['provisions'])) {
                $bar->advance();
                continue;
            }

            $legislationTitle = $data['title'];
            $legislationId = $data['id'];
            $provisionCount = 0;

            // Batch insert for better performance
            $records = [];
            foreach ($data['provisions'] as $provision) {
                $records[] = [
                    'legislation_id' => $legislationId,
                    'legislation_title' => $legislationTitle,
                    'article_title' => $provision['title'] ?? '',
                    'content' => $provision['content'] ?? '',
                    'reference_id' => $provision['provision_ref'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $provisionCount++;
            }

            // Insert in chunks for efficiency
            if ($this->option('fresh')) {
                // Fresh import: use bulk insert (much faster)
                foreach (array_chunk($records, 500) as $chunk) {
                    DB::table('legal_articles')->insert($chunk);
                }
            } else {
                // Normal import: use updateOrCreate
                foreach ($data['provisions'] as $provision) {
                    LegalArticle::updateOrCreate(
                        [
                            'legislation_id' => $legislationId,
                            'article_title' => $provision['title'],
                        ],
                        [
                            'legislation_title' => $legislationTitle,
                            'content' => $provision['content'],
                            'reference_id' => $provision['provision_ref'] ?? null,
                        ]
                    );
                }
            }

            $totalArticles += $provisionCount;
            $totalLaws++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Import completed successfully!");
        $this->table(
            ['البيان', 'العدد'],
            [
                ['الأنظمة المستوردة', $totalLaws],
                ['المواد المستوردة', $totalArticles],
                ['إجمالي المواد في DB', LegalArticle::count()],
            ]
        );

        // ربط الإشارات القانونية تلقائياً بعد الاستيراد
        $this->newLine();
        $this->info('🔗 ربط الإشارات القانونية بالمواد المستوردة...');
        $this->call('link:citations', ['--force' => $this->option('fresh')]);
    }
}
