<?php

namespace App\Console\Commands;

use App\Models\LegalTask;
use App\Models\LegalArticle;
use App\Services\AzureSearchService;
use Illuminate\Console\Command;

class TestRecall10 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:recall10 {--limit=50 : عدد الحالات المختبرة}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشغيل تقييم Recall@10 لمحرك البحث Azure AI Search';

    /**
     * Execute the console command.
     */
    public function handle(AzureSearchService $azure): int
    {
        $this->info("🚀 بدء تقييم Recall@10 على Azure AI Search...");
        
        if (!$azure->isEnabled()) {
            $this->error("❌ محرك البحث Azure AI Search غير مفعل في إعدادات البيئة.");
            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');

        // جلب عينات بربط نظامي وعينات عامة (مع استثناء مبادئ القضايا والأنظمة غير المحددة لضمان دقة مطابقة المواد)
        $annotatedTasks = LegalTask::whereNotNull('question')
            ->where('question', '!=', '')
            ->whereNotNull('law_system_name')
            ->where('law_system_name', '!=', '')
            ->whereNotIn('law_system_name', ['مبدأ قضائي', 'نظام غير محدد', 'قانون', 'من', 'المادَّة'])
            ->whereNotNull('law_article_number')
            ->where('law_article_number', '!=', '')
            ->where('law_article_number', '!=', 'غير محدد')
            ->whereNotNull('case_text')
            ->where('case_text', '!=', '')
            ->inRandomOrder()
            ->limit(intval($limit / 2))
            ->get();

        $remainingCount = $limit - $annotatedTasks->count();

        $randomTasks = LegalTask::whereNotNull('question')
            ->where('question', '!=', '')
            ->whereNotNull('case_text')
            ->where('case_text', '!=', '')
            ->whereNotIn('id', $annotatedTasks->pluck('id'))
            ->inRandomOrder()
            ->limit($remainingCount)
            ->get();

        $tasks = $annotatedTasks->merge($randomTasks);

        $this->info("تم جلب " . $annotatedTasks->count() . " حالة بربط نظامي و " . $randomTasks->count() . " حالة عامة.");

        // بناء فهرس سريع للمواد في الذاكرة لتجنب استعلامات قاعدة البيانات المكررة وربط أرقام المواد بـ reference_id
        $this->info("📥 تحميل وتشييد فهرس المواد القانونية...");
        $articles = LegalArticle::all();
        $articlesIndex = [];
        foreach ($articles as $article) {
            $title = trim($article->legislation_title);
            $refNum = null;
            if ($article->reference_id) {
                if (preg_match('/art_?(\d+(?:\/\d+)?)/i', $article->reference_id, $m)) {
                    $refNum = $m[1];
                }
            }
            if ($refNum && $title) {
                $articlesIndex[$title][$refNum] = $article->id;
            }
        }

        $results = [];
        $caseHits = 0;
        $articleHits = 0;
        $totalArticlesTested = 0;

        $bar = $this->output->createProgressBar($tasks->count());
        $bar->start();

        // دالة مساعدة لتطبيع النصوص العربية للمطابقة
        $normalizeText = function ($text) {
            $text = preg_replace('/[أإآ]/u', 'ا', $text);
            $text = str_replace(['ة','ى'], ['ه','ي'], $text);
            return trim($text);
        };

        // دالة للبحث المرن عن المادة باستخدام الفهرس
        $findTargetArticle = function ($systemName, $articleNum) use ($articlesIndex, $normalizeText) {
            if (empty($systemName) || empty($articleNum)) return null;
            
            $normSystem = $normalizeText($systemName);
            
            // 1. مطابقة مباشرة
            if (isset($articlesIndex[$systemName][$articleNum])) {
                return $articlesIndex[$systemName][$articleNum];
            }
            
            // 2. مطابقة مرنة عبر البدائل الشائعة
            $alternatives = [$systemName, $normSystem];
            if (str_starts_with($systemName, 'نظام ')) {
                $sub = mb_substr($systemName, 5);
                $alternatives[] = $sub;
                $alternatives[] = $normalizeText($sub);
            } else {
                $alternatives[] = 'نظام ' . $systemName;
                $alternatives[] = $normalizeText('نظام ' . $systemName);
            }
            
            foreach ($alternatives as $alt) {
                foreach ($articlesIndex as $title => $nums) {
                    if ($normalizeText($title) === $normalizeText($alt) && isset($nums[$articleNum])) {
                        return $nums[$articleNum];
                    }
                }
            }
            
            // 3. مطابقة جزئية (أحدهما يحتوي الآخر)
            foreach ($articlesIndex as $title => $nums) {
                if (isset($nums[$articleNum])) {
                    $normTitle = $normalizeText($title);
                    if (str_contains($normTitle, $normSystem) || str_contains($normSystem, $normTitle)) {
                        return $nums[$articleNum];
                    }
                }
            }
            
            return null;
        };

        foreach ($tasks as $task) {
            $query = $task->question;
            $targetCaseId = 'task_' . $task->id;
            $targetQaId = 'qa_' . $task->id;
            
            // البحث عن المادة النظامية المطابقة
            $targetArticleId = null;
            $articleTitle = null;
            
            if (!empty($task->law_system_name) && !empty($task->law_article_number)) {
                $resolvedArticleId = $findTargetArticle($task->law_system_name, $task->law_article_number);
                if ($resolvedArticleId) {
                    $article = LegalArticle::find($resolvedArticleId);
                    if ($article) {
                        $targetArticleId = 'article_' . $article->id;
                        $articleTitle = "المادة {$article->article_title} - {$article->legislation_title}";
                        $totalArticlesTested++;
                    }
                }
            }
            
            $caseMatched = false;
            $articleMatched = false;
            
            try {
                // البحث في القضايا والأحكام
                $casesResult = $azure->hybridSearch($query, 10, ['!source_type' => 'article']);
                foreach ($casesResult as $doc) {
                    if ($doc->id == $task->id || $doc->id == $targetCaseId || $doc->id == $targetQaId) {
                        $caseMatched = true;
                    }
                }
                
                // البحث في المواد النظامية
                if ($targetArticleId) {
                    $articlesResult = $azure->hybridSearch($query, 10, ['source_type' => 'article']);
                    foreach ($articlesResult as $doc) {
                        if ($doc->id == $targetArticleId) {
                            $articleMatched = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // تجاهل خطأ البحث الفردي
            }
            
            if ($caseMatched) $caseHits++;
            if ($articleMatched) $articleHits++;
            
            $results[] = [
                'task_id'           => $task->id,
                'question'          => $task->question,
                'target_case_id'    => $targetCaseId,
                'target_article_id' => $targetArticleId,
                'article_title'     => $articleTitle,
                'case_matched'      => $caseMatched,
                'article_matched'   => $articleMatched,
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $caseRecall = ($caseHits / count($results)) * 100;
        $articleRecall = $totalArticlesTested > 0 ? ($articleHits / $totalArticlesTested) * 100 : 0;

        $this->table(
            ['المقياس', 'النسبة المسترجعة'],
            [
                ['الأحكام والسوابق (Judgments Recall@10)', "{$caseRecall}%"],
                ['المواد والأنظمة (Articles Recall@10)', "{$articleRecall}%"],
            ]
        );

        // إنشاء التقرير
        $markdown = "# تقرير تقييم الاسترجاع Recall@10 للمستشار القانوني\n\n";
        $markdown .= "مقياس **Recall@10** هو أحد المقاييس المعيارية لتقييم أنظمة استرجاع المعلومات (RAG). يقيس هذا التقييم ما إذا كان **المستند المصدر الصحيح** يظهر ضمن **أول 10 نتائج مسترجعة** عند السؤال عنه.\n\n";
        $markdown .= "## خلاصة نتائج التقييم\n";
        $markdown .= "- **عدد الحالات المختبرة:** {$limit} حالة\n";
        $markdown .= "- **نسبة استرجاع الأحكام والسوابق (Judgments Recall@10):** `{$caseRecall}%` (تم العثور على الحكم المصدر في {$caseHits} من أصل {$limit} سؤالاً)\n";
        if ($totalArticlesTested > 0) {
            $markdown .= "- **نسبة استرجاع المواد والأنظمة (Articles Recall@10):** `{$articleRecall}%` (تم العثور على المادة النظامية المحددة في {$articleHits} من أصل {$totalArticlesTested} حالة تحتوي على ربط نظامي)\n";
        }
        $markdown .= "\n## تفاصيل الاختبار\n\n";
        $markdown .= "| الرقم | السؤال | المعرف المستهدف (الحكم) | هل تم الاسترجاع؟ | المادة المستهدفة | هل تم الاسترجاع؟ |\n";
        $markdown .= "|---|---|---|---|---|---|\n";

        foreach ($results as $index => $res) {
            $num = $index + 1;
            $qShort = mb_substr($res['question'], 0, 50) . (mb_strlen($res['question']) > 50 ? '...' : '');
            $caseStatus = $res['case_matched'] ? '✅ نعم' : '❌ لا';
            $artStatus = $res['target_article_id'] ? ($res['article_matched'] ? '✅ نعم' : '❌ لا') : 'غير محدد';
            $artTitle = $res['article_title'] ?: '-';
            
            $markdown .= "| {$num} | {$qShort} | `{$res['target_case_id']}` | {$caseStatus} | {$artTitle} | {$artStatus} |\n";
        }

        $reportPath = base_path('recall10_report.md');
        file_put_contents($reportPath, $markdown);
        
        $this->info("📄 تم حفظ التقرير المفصل في: recall10_report.md");

        return Command::SUCCESS;
    }
}
