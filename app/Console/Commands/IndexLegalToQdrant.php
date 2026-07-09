<?php

namespace App\Console\Commands;

use App\Models\LegalTask;
use App\Models\LegalQaPair;
use App\Models\LegalArticle;
use App\Services\QdrantSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * php artisan vector:index-legal
 * ─────────────────────────────────────────────────────────────────────────────
 * يرفع البيانات القانونية لـ Qdrant Vector Database
 * يدعم: tasks | articles | qa_pairs | all
 *
 * أمثلة:
 *   php artisan vector:index-legal --create-collection --type=all
 *   php artisan vector:index-legal --type=tasks --chunk=50
 *   php artisan vector:index-legal --fresh --type=all
 *   php artisan vector:index-legal --dry-run
 * ─────────────────────────────────────────────────────────────────────────────
 */
class IndexLegalToQdrant extends Command
{
    protected $signature = 'vector:index-legal
        {type=all : النوع المراد فهرسته (tasks|articles|qa_pairs|all)}
        {--chunk=20 : حجم الـ batch في كل دفعة}
        {--create-collection : إنشاء الـ collection في Qdrant قبل الرفع}
        {--fresh : حذف كل البيانات وإعادة الفهرسة من الصفر}
        {--dry-run : تجربة بدون رفع فعلي — يوضح العدد المتوقع}
    ';

    protected $description = 'رفع الأسئلة القانونية لـ Qdrant Vector Database للبحث الذكي بالمعنى';

    public function __construct(protected QdrantSearchService $qdrant)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $type      = $this->argument('type');
        $chunkSize = (int) $this->option('chunk');
        $dryRun    = $this->option('dry-run');
        $fresh     = $this->option('fresh');

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║   🧠  Qdrant Vector Indexing — رديف القانوني               ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // ── Health Check ──────────────────────────────────────────────────────
        if (! $dryRun) {
            $health = $this->qdrant->healthCheck();
            if ($health['status'] === 'not_configured') {
                $this->error('❌ Qdrant غير مُعد! أضف QDRANT_URL و QDRANT_API_KEY في .env');
                $this->newLine();
                $this->warn('خطوات الإعداد:');
                $this->line('  1. افتح https://cloud.qdrant.io/ وأنشئ حساب مجاني');
                $this->line('  2. أنشئ Cluster جديد (Free tier)');
                $this->line('  3. انسخ الـ URL والـ API Key');
                $this->line('  4. أضفهم في .env:');
                $this->line('     QDRANT_URL=https://your-cluster.cloud.qdrant.io:6333');
                $this->line('     QDRANT_API_KEY=your-api-key');
                $this->line('     QDRANT_ENABLED=true');
                return Command::FAILURE;
            }

            if ($health['status'] === 'unreachable') {
                $this->error("❌ لا يمكن الوصول لـ Qdrant: {$health['message']}");
                return Command::FAILURE;
            }

            $this->info("✅ Qdrant متصل — Status: {$health['status']}");
        } else {
            $this->warn('⚠️  وضع التجربة (Dry Run) — لن يتم رفع أي بيانات');
        }

        $this->info("📦 النوع: {$type} | Batch: {$chunkSize}");
        $this->newLine();

        // ── Fresh: حذف كل البيانات ─────────────────────────────────────────
        if ($fresh && ! $dryRun) {
            if ($this->confirm('⚠️  هل تريد حذف كل البيانات الحالية من Qdrant وإعادة الفهرسة؟')) {
                $this->warn('🗑️  جاري حذف البيانات...');
                if ($this->qdrant->deleteAllPoints()) {
                    $this->info('✅ تم حذف البيانات وإعادة إنشاء الـ Collection.');
                } else {
                    $this->error('❌ فشل حذف البيانات.');
                    return Command::FAILURE;
                }
            }
        }

        // ── Create Collection ─────────────────────────────────────────────────
        if ($this->option('create-collection') && ! $dryRun) {
            $this->info('📋 إنشاء Qdrant Collection...');
            if ($this->qdrant->createCollection()) {
                $this->info('✅ Collection جاهز.');
            } else {
                $this->error('❌ فشل إنشاء Collection.');
                return Command::FAILURE;
            }
            $this->newLine();
        }

        // ── Index Data ────────────────────────────────────────────────────────
        $totalIndexed = 0;
        $totalFailed  = 0;

        $startTime = microtime(true);

        match ($type) {
            'tasks' => [$totalIndexed, $totalFailed] = $this->indexLegalTasks($chunkSize, $dryRun),
            'articles' => [$totalIndexed, $totalFailed] = $this->indexLegalArticles($chunkSize, $dryRun),
            'qa_pairs' => [$totalIndexed, $totalFailed] = $this->indexQaPairs($chunkSize, $dryRun),
            'all' => [
                [$t1, $f1] = $this->indexLegalTasks($chunkSize, $dryRun),
                [$t2, $f2] = $this->indexLegalArticles($chunkSize, $dryRun),
                [$t3, $f3] = $this->indexQaPairs($chunkSize, $dryRun),
                $totalIndexed = $t1 + $t2 + $t3,
                $totalFailed  = $f1 + $f2 + $f3,
            ],
            default => $this->error("نوع غير معروف: {$type}. الخيارات: tasks|articles|qa_pairs|all"),
        };

        $elapsed = round(microtime(true) - $startTime, 1);

        // ── Summary ───────────────────────────────────────────────────────────
        $this->newLine(2);
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║   📊  ملخص الفهرسة                                        ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');

        $this->table(
            ['الحالة', 'العدد'],
            [
                ['✅ تم الرفع', number_format($totalIndexed)],
                ['❌ فشل', number_format($totalFailed)],
                ['📊 المجموع', number_format($totalIndexed + $totalFailed)],
                ['⏱️ الوقت', "{$elapsed} ثانية"],
            ]
        );

        if (! $dryRun && $totalIndexed > 0) {
            $info = $this->qdrant->getCollectionInfo();
            if ($info) {
                $this->newLine();
                $this->info("📈 إجمالي النقاط في Qdrant: " . number_format($info['points_count']));
            }
        }

        return $totalFailed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEXERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * فهرسة المهام القانونية (LegalTask)
     *
     * يشمل كل المهام — المدققة وغير المدققة.
     * يستخدم correct_answer إذا موجود، وإلا proposed_answer.
     */
    private function indexLegalTasks(int $chunkSize, bool $dryRun): array
    {
        $this->info("\n📚 فهرسة Legal Tasks (الأسئلة القانونية)...");

        $indexed = 0;
        $failed  = 0;

        // كل المهام اللي عندها سؤال (بما فيها غير المدققة)
        $query = LegalTask::whereNotNull('question')
            ->where('question', '!=', '');

        $total = $query->count();
        $this->info("   وُجد {$total} مهمة للفهرسة.");

        if ($total === 0) {
            $this->warn('   لا توجد مهام للفهرسة.');
            return [0, 0];
        }

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% | ✅ %indexed% | ❌ %failed%");
        $bar->setMessage('0', 'indexed');
        $bar->setMessage('0', 'failed');
        $bar->start();

        $query->chunkById($chunkSize, function ($tasks) use (&$indexed, &$failed, $dryRun, $bar) {
            $docs = $tasks->map(function ($t) {
                // الأولوية: correct_answer → proposed_answer → ''
                $answer = $t->correct_answer ?: $t->proposed_answer ?: '';
                $isVerified = ! empty($t->correct_answer);

                return [
                    'id'             => 'task_' . $t->id,
                    'question'       => $t->question ?? '',
                    'answer'         => $answer,
                    'case_text'      => mb_substr($t->case_text ?? '', 0, 2000),
                    'domain'         => $t->domain ?? 'law',
                    'source_type'    => 'judgment',
                    'law_system'     => $t->law_system_name ?? '',
                    'case_reference' => $t->case_reference ?? '',
                    'is_verified'    => $isVerified,
                ];
            })->toArray();

            if (! $dryRun) {
                $result   = $this->qdrant->upsertBatch($docs);
                $indexed += $result['success'];
                $failed  += $result['failed'];
            } else {
                $indexed += count($docs);
            }

            $bar->setMessage((string) $indexed, 'indexed');
            $bar->setMessage((string) $failed, 'failed');
            $bar->advance(count($docs));
        });

        $bar->finish();
        $this->newLine();
        return [$indexed, $failed];
    }

    /**
     * فهرسة المواد القانونية (LegalArticle)
     *
     * نصوص الأنظمة السعودية — مهمة جداً لأن المساعد يرجع لها كمرجع.
     */
    private function indexLegalArticles(int $chunkSize, bool $dryRun): array
    {
        $this->info("\n📖 فهرسة Legal Articles (نصوص الأنظمة)...");

        $indexed = 0;
        $failed  = 0;

        $total = LegalArticle::count();
        $this->info("   وُجد {$total} مادة قانونية.");

        if ($total === 0) {
            $this->warn('   لا توجد مواد قانونية للفهرسة.');
            return [0, 0];
        }

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% | ✅ %indexed% | ❌ %failed%");
        $bar->setMessage('0', 'indexed');
        $bar->setMessage('0', 'failed');
        $bar->start();

        LegalArticle::chunkById($chunkSize, function ($articles) use (&$indexed, &$failed, $dryRun, $bar) {
            $docs = $articles->map(fn($a) => [
                'id'             => 'article_' . $a->id,
                'question'       => $a->article_title ?? '',
                'answer'         => $a->content ?? '',
                'case_text'      => '',
                'domain'         => 'law_article',
                'source_type'    => 'article',
                'law_system'     => $a->legislation_title ?? '',
                'case_reference' => 'مادة رقم ' . ($a->article_number ?? ''),
                'is_verified'    => true, // المواد القانونية دائماً مدققة
            ])->toArray();

            if (! $dryRun) {
                $result   = $this->qdrant->upsertBatch($docs);
                $indexed += $result['success'];
                $failed  += $result['failed'];
            } else {
                $indexed += count($docs);
            }

            $bar->setMessage((string) $indexed, 'indexed');
            $bar->setMessage((string) $failed, 'failed');
            $bar->advance(count($docs));
        });

        $bar->finish();
        $this->newLine();
        return [$indexed, $failed];
    }

    /**
     * فهرسة أزواج الأسئلة والإجابات (LegalQaPair)
     *
     * يشمل كل الأسئلة — المعتمدة وغير المعتمدة.
     */
    private function indexQaPairs(int $chunkSize, bool $dryRun): array
    {
        $this->info("\n❓ فهرسة QA Pairs (أسئلة وإجابات)...");

        $indexed = 0;
        $failed  = 0;

        $total = LegalQaPair::count();
        $this->info("   وُجد {$total} زوج سؤال/إجابة.");

        if ($total === 0) {
            $this->warn('   لا توجد أسئلة للفهرسة.');
            return [0, 0];
        }

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% | ✅ %indexed% | ❌ %failed%");
        $bar->setMessage('0', 'indexed');
        $bar->setMessage('0', 'failed');
        $bar->start();

        LegalQaPair::with('record:id,domain,sub_domain,source_reference')
            ->chunkById($chunkSize, function ($pairs) use (&$indexed, &$failed, $dryRun, $bar) {
                $docs = $pairs->map(function ($qa) {
                    // الأولوية: corrected_answer → generated_answer
                    $answer     = $qa->corrected_answer ?: $qa->generated_answer ?: '';
                    $isVerified = ! empty($qa->corrected_answer) || $qa->review_status === 'Approved';

                    return [
                        'id'             => 'qa_' . $qa->id,
                        'question'       => $qa->question ?? '',
                        'answer'         => $answer,
                        'case_text'      => '',
                        'domain'         => $qa->record?->domain ?? 'legal',
                        'source_type'    => 'qa_pair',
                        'law_system'     => $qa->record?->sub_domain ?? '',
                        'case_reference' => $qa->record?->source_reference ?? '',
                        'is_verified'    => $isVerified,
                    ];
                })->toArray();

                if (! $dryRun) {
                    $result   = $this->qdrant->upsertBatch($docs);
                    $indexed += $result['success'];
                    $failed  += $result['failed'];
                } else {
                    $indexed += count($docs);
                }

                $bar->setMessage((string) $indexed, 'indexed');
                $bar->setMessage((string) $failed, 'failed');
                $bar->advance(count($docs));
            });

        $bar->finish();
        $this->newLine();
        return [$indexed, $failed];
    }
}
