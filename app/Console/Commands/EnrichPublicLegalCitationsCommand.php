<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PublicLegalAnswer;
use App\Models\LegalTask;
use App\Services\Legal\LegalQueryExpansionService;

class EnrichPublicLegalCitationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:enrich-citations {--force : Enrich all records even if already clean}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrich PublicLegalAnswer citations with actual Saudi law systems, precedents, case references, and eliminate placeholder articles';

    /**
     * Execute the console command.
     */
    public function handle(LegalQueryExpansionService $expansionService): int
    {
        $this->info("🚀 بدء عملية إثراء وتصحيح المراجع القانونية وتجاوز وسوم (مبدأ)...");

        $query = PublicLegalAnswer::query();
        if (!$this->option('force')) {
            // استهداف السجلات التي تحتوي على "مبدأ" أو "Saudi Law" كـ placeholder
            $query->where(function ($q) {
                $q->where('citations', 'LIKE', '%\\\\u0645\\\\u0628\\\\u062f\\\\u0623%')
                  ->orWhere('citations', 'LIKE', '%مبدأ%')
                  ->orWhere('citations', 'LIKE', '%Saudi Law%');
            });
        }

        $total = $query->count();
        $this->info("📊 إجمالي السجلات المستهدفة بالتحديث: {$total}");

        if ($total === 0) {
            $this->info("✅ جميع المراجع القانونية منقحة ومحدثة بالفعل!");
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $updated = 0;

        $query->chunkById(500, function ($records) use (&$processed, &$updated, $bar, $expansionService) {
            // جلب المهام المرتبطة في دفعة واحدة لتحسين الأداء
            $taskIds = $records->where('source_type', 'legal_task')->pluck('source_id')->filter()->unique();
            $tasks = LegalTask::whereIn('id', $taskIds)->get()->keyBy('id');

            foreach ($records as $record) {
                $processed++;
                $cits = is_array($record->citations)
                    ? $record->citations
                    : (is_string($record->citations) ? (json_decode($record->citations, true) ?? []) : []);

                $task = ($record->source_type === 'legal_task' && isset($tasks[$record->source_id]))
                    ? $tasks[$record->source_id]
                    : null;

                $detectedLaw = $expansionService->detectTargetLawSystem($record->question) ?? 'الأنظمة واللوائح السعودية';
                $caseRef = $task ? $task->case_reference : null;

                $newCits = [];
                if (empty($cits) && $task) {
                    $cits = [[
                        'law_system'     => $task->law_system ?: '',
                        'article_number' => $task->law_article_number ?: '',
                        'text'           => $task->law_article_text ?: '',
                    ]];
                }

                foreach ($cits as $cit) {
                    if (!is_array($cit)) continue;

                    $rawSys = trim($cit['law_system'] ?? '');
                    $rawArt = trim($cit['article_number'] ?? '');
                    $citText = trim($cit['text'] ?? '');

                    // اسم النظام
                    $cleanSys = (!empty($rawSys) && $rawSys !== 'مبدأ قضائي' && $rawSys !== 'Saudi Law' && $rawSys !== 'null' && $rawSys !== 'غير محدد')
                        ? $rawSys
                        : $detectedLaw;

                    // رقم المادة
                    $cleanArt = null;
                    if (!empty($rawArt) && !in_array($rawArt, ['مبدأ', 'null', 'غير محدد', 'عام', '-', 'undefined'])) {
                        $cleanArt = $rawArt;
                    }

                    // استخراج رقم المادة من نص السؤال أو السند إن وجد
                    if (!$cleanArt) {
                        if (preg_match('/الماد[ةه]\s*[:(]?\s*(\d{1,4})/u', $record->question . ' ' . $citText, $am)) {
                            $cleanArt = $am[1];
                        }
                    }

                    // استخراج السابقة القضائية أو القرار
                    $precedent = null;
                    if (preg_match('/(قرار\s+المحكمة\s+العليا[^\n\r،]+)/u', $citText, $pm)) {
                        $precedent = trim($pm[1]);
                    } elseif (preg_match('/(حكم\s+محكمة\s+الاستئناف[^\n\r،]+)/u', $citText, $pm)) {
                        $precedent = trim($pm[1]);
                    } elseif (preg_match('/(مبدأ\s+قضائي[^\n\r:،]+[:]?\s*[^\n\r،]+)/u', $citText, $pm)) {
                        $precedent = trim($pm[1]);
                    } elseif (!empty($cit['precedent'])) {
                        $precedent = trim($cit['precedent']);
                    }

                    $item = [
                        'law_system'     => $cleanSys,
                        'article_number' => $cleanArt,
                        'text'           => $citText,
                    ];
                    if ($precedent) {
                        $item['precedent'] = $precedent;
                    }
                    if (!empty($caseRef) && is_numeric($caseRef)) {
                        $item['case_reference'] = (string)$caseRef;
                    }

                    $newCits[] = $item;
                }

                // حفظ التحديث
                $record->citations = $newCits;
                $record->save();
                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("✅ اكتملت العملية بنجاح! تم تحديث {$updated} سجلاً وحذف جميع وسوم (مبدأ) وإثراء السوابق والأنظمة.");

        return 0;
    }
}

