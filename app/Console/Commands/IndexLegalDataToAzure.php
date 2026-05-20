<?php

namespace App\Console\Commands;

use App\Models\LegalTask;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;
use App\Models\LegalArticle;
use App\Services\AzureSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * php artisan azure:index-legal
 * ─────────────────────────────────────────────────────────────────────────────
 * يرفع البيانات القانونية الموجودة في قاعدة البيانات لـ Azure AI Search
 * يدعم الأنواع: tasks | articles | qa_pairs | all
 * ─────────────────────────────────────────────────────────────────────────────
 */
class IndexLegalDataToAzure extends Command
{
    protected $signature = 'azure:index-legal
        {type=all : النوع (tasks|articles|qa_pairs|all)}
        {--chunk=50 : حجم الـ batch في كل مرة}
        {--create-index : إنشاء الـ index في Azure قبل الرفع}
        {--dry-run : تجربة بدون رفع فعلي}
    ';

    protected $description = 'رفع البيانات القانونية لـ Azure AI Search';

    public function __construct(protected AzureSearchService $azure)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $type      = $this->argument('type');
        $chunkSize = (int) $this->option('chunk');
        $dryRun    = $this->option('dry-run');

        $this->info("🚀 بدء الفهرسة على Azure AI Search");
        $this->info("النوع: {$type} | Batch: {$chunkSize}");

        if ($dryRun) {
            $this->warn('⚠️  Dry-run mode — لن يتم رفع أي بيانات فعلياً');
        }

        // إنشاء الـ index إذا طُلب
        if ($this->option('create-index') && !$dryRun) {
            $this->info('📋 إنشاء Azure Search Index...');
            if ($this->azure->createIndex()) {
                $this->info('✅ Index تم إنشاؤه بنجاح');
            } else {
                $this->error('❌ فشل إنشاء Index');
                return Command::FAILURE;
            }
        }

        $totalIndexed = 0;
        $totalFailed  = 0;

        match ($type) {
            'tasks'    => [$totalIndexed, $totalFailed] = $this->indexLegalTasks($chunkSize, $dryRun),
            'articles' => [$totalIndexed, $totalFailed] = $this->indexLegalArticles($chunkSize, $dryRun),
            'qa_pairs' => [$totalIndexed, $totalFailed] = $this->indexQaPairs($chunkSize, $dryRun),
            'all'      => [
                [$t1, $f1] = $this->indexLegalTasks($chunkSize, $dryRun),
                [$t2, $f2] = $this->indexLegalArticles($chunkSize, $dryRun),
                [$t3, $f3] = $this->indexQaPairs($chunkSize, $dryRun),
                $totalIndexed = $t1 + $t2 + $t3,
                $totalFailed  = $f1 + $f2 + $f3,
            ],
            default => $this->error("نوع غير معروف: {$type}"),
        };

        $this->newLine();
        $this->table(
            ['الحالة', 'العدد'],
            [
                ['✅ تم الرفع', $totalIndexed],
                ['❌ فشل', $totalFailed],
                ['📊 المجموع', $totalIndexed + $totalFailed],
            ]
        );

        return $totalFailed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEXERS
    // ─────────────────────────────────────────────────────────────────────────

    private function indexLegalTasks(int $chunkSize, bool $dryRun): array
    {
        $this->info("\n📚 فهرسة Legal Tasks...");
        $indexed = 0;
        $failed  = 0;

        $total = LegalTask::whereNotNull('correct_answer')->count();
        $bar   = $this->output->createProgressBar($total);
        $bar->start();

        LegalTask::whereNotNull('correct_answer')
            ->chunkById($chunkSize, function ($tasks) use (&$indexed, &$failed, $dryRun, $bar) {
                $docs = $tasks->map(fn($t) => [
                    'id'             => 'task_' . $t->id,
                    'question'       => $t->question ?? '',
                    'answer'         => $t->correct_answer ?? '',
                    'case_text'      => $t->case_text ?? '',
                    'domain'         => $t->domain ?? 'general',
                    'source_type'    => 'judgment',
                    'law_system'     => $t->law_system_name ?? '',
                    'case_reference' => $t->case_reference ?? '',
                ])->toArray();

                if (!$dryRun) {
                    $result   = $this->azure->indexBatch($docs);
                    $indexed += $result['success'];
                    $failed  += $result['failed'];
                } else {
                    $indexed += count($docs);
                }

                $bar->advance(count($docs));
            });

        $bar->finish();
        $this->newLine();
        return [$indexed, $failed];
    }

    private function indexLegalArticles(int $chunkSize, bool $dryRun): array
    {
        $this->info("\n📖 فهرسة Legal Articles...");
        $indexed = 0;
        $failed  = 0;

        $total = LegalArticle::count();
        $bar   = $this->output->createProgressBar($total);
        $bar->start();

        LegalArticle::chunkById($chunkSize, function ($articles) use (&$indexed, &$failed, $dryRun, $bar) {
            $docs = $articles->map(fn($a) => [
                'id'             => 'article_' . $a->id,
                'question'       => $a->article_title ?? '',
                'answer'         => $a->content ?? '',
                'case_text'      => $a->content ?? '',
                'domain'         => 'law_article',
                'source_type'    => 'article',
                'law_system'     => $a->legislation_title ?? '',
                'case_reference' => 'مادة رقم ' . ($a->article_number ?? ''),
            ])->toArray();

            if (!$dryRun) {
                $result   = $this->azure->indexBatch($docs);
                $indexed += $result['success'];
                $failed  += $result['failed'];
            } else {
                $indexed += count($docs);
            }

            $bar->advance(count($docs));
        });

        $bar->finish();
        $this->newLine();
        return [$indexed, $failed];
    }

    private function indexQaPairs(int $chunkSize, bool $dryRun): array
    {
        $this->info("\n❓ فهرسة QA Pairs...");
        $indexed = 0;
        $failed  = 0;

        $total = LegalQaPair::approved()->count();
        $bar   = $this->output->createProgressBar($total);
        $bar->start();

        LegalQaPair::approved()
            ->with('record:id,domain,sub_domain,source_reference')
            ->chunkById($chunkSize, function ($pairs) use (&$indexed, &$failed, $dryRun, $bar) {
                $docs = $pairs->map(fn($qa) => [
                    'id'             => 'qa_' . $qa->id,
                    'question'       => $qa->question ?? '',
                    'answer'         => $qa->final_answer ?? '',
                    'case_text'      => $qa->final_answer ?? '',
                    'domain'         => $qa->record?->domain ?? 'legal',
                    'source_type'    => 'qa_pair',
                    'law_system'     => $qa->record?->sub_domain ?? '',
                    'case_reference' => $qa->record?->source_reference ?? '',
                ])->toArray();

                if (!$dryRun) {
                    $result   = $this->azure->indexBatch($docs);
                    $indexed += $result['success'];
                    $failed  += $result['failed'];
                } else {
                    $indexed += count($docs);
                }

                $bar->advance(count($docs));
            });

        $bar->finish();
        $this->newLine();
        return [$indexed, $failed];
    }
}
