<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalCitation;
use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

class LinkCitationsToArticles extends Command
{
    protected $signature = 'link:citations {--force : Re-link all citations even if already linked}';
    protected $description = 'Link legal_citations to legal_articles by matching system_name + article_number';

    public function handle()
    {
        $this->info('🔗 بدء ربط الإشارات القانونية بالمواد...');

        // جلب كل الإشارات اللي محتاجة ربط
        $query = LegalCitation::whereNotNull('article_number')
            ->where('article_number', '!=', '');

        if (!$this->option('force')) {
            $query->whereNull('legal_article_id');
        }

        $totalCitations = $query->count();
        $this->info("📊 إجمالي الإشارات المطلوب ربطها: {$totalCitations}");

        if ($totalCitations === 0) {
            $this->info('✅ جميع الإشارات مرتبطة بالفعل!');
            return;
        }

        // تحميل كل المواد في الذاكرة مرة واحدة (indexed by legislation_title + section number)
        $this->info('📥 تحميل قاعدة المواد القانونية...');
        $articles = LegalArticle::all();
        $this->info("   تم تحميل {$articles->count()} مادة.");

        // بناء فهرس سريع: legislation_title => [article_number => article_id]
        $index = [];
        foreach ($articles as $article) {
            $title = trim($article->legislation_title);
            // استخراج رقم المادة من reference_id (مثل art_164 => 164, art_20 => 20)
            $refNum = null;
            if ($article->reference_id) {
                // art_164, art20, art_1/2
                if (preg_match('/art_?(\d+(?:\/\d+)?)/i', $article->reference_id, $m)) {
                    $refNum = $m[1];
                }
            }

            if ($refNum && $title) {
                $index[$title][$refNum] = $article->id;
            }
        }

        $this->info('🔍 بدء المطابقة...');
        $bar = $this->output->createProgressBar($totalCitations);
        $bar->start();

        $linked = 0;
        $notFound = 0;
        $alreadyLinked = 0;
        $notFoundSamples = [];

        // معالجة بالدفعات
        $query->chunk(500, function ($citations) use ($index, &$linked, &$notFound, &$alreadyLinked, &$notFoundSamples, $bar) {
            $updates = [];

            foreach ($citations as $citation) {
                $systemName = trim($citation->system_name ?? '');
                $artNum = trim($citation->article_number ?? '');
                $bar->advance();

                if (empty($systemName) || empty($artNum)) {
                    $notFound++;
                    continue;
                }

                // 1. مطابقة مباشرة: system_name == legislation_title AND article_number == reference number
                $articleId = $index[$systemName][$artNum] ?? null;

                // 2. لو ما لقيناش، نجرب مطابقة مرنة (بدون كلمة "نظام" أو اختلاف تشكيل)
                if (!$articleId) {
                    $articleId = $this->fuzzyMatch($systemName, $artNum, $index);
                }

                if ($articleId) {
                    $updates[] = ['id' => $citation->id, 'legal_article_id' => $articleId];
                    $linked++;
                } else {
                    $notFound++;
                    // حفظ أول 20 عينة من المفقودة
                    if (count($notFoundSamples) < 20) {
                        $notFoundSamples[] = "{$systemName} → المادة {$artNum}";
                    }
                }
            }

            // Batch update
            if (!empty($updates)) {
                foreach (array_chunk($updates, 100) as $chunk) {
                    $cases = [];
                    $ids = [];
                    foreach ($chunk as $u) {
                        $cases[] = "WHEN {$u['id']} THEN {$u['legal_article_id']}";
                        $ids[] = $u['id'];
                    }
                    $caseSql = implode(' ', $cases);
                    $idsSql = implode(',', $ids);
                    DB::statement("UPDATE legal_citations SET legal_article_id = CASE id {$caseSql} END WHERE id IN ({$idsSql})");
                }
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['البيان', 'العدد'],
            [
                ['✅ تم ربطها', $linked],
                ['❌ غير موجودة في DB', $notFound],
                ['📊 الإجمالي', $totalCitations],
                ['📈 نسبة الربط', $totalCitations > 0 ? round(($linked / $totalCitations) * 100, 1) . '%' : '0%'],
            ]
        );

        if (!empty($notFoundSamples)) {
            $this->warn("\n⚠️ عيّنة من الإشارات غير المتطابقة:");
            foreach ($notFoundSamples as $sample) {
                $this->line("   • {$sample}");
            }
        }

        $this->info("\n🎉 تم الربط بنجاح!");
    }

    /**
     * Fuzzy matching: try common name variations
     */
    private function fuzzyMatch(string $systemName, string $artNum, array $index): ?int
    {
        // تجربة أسماء بديلة شائعة
        $alternatives = [
            $systemName,
        ];

        // لو الاسم يبدأ بـ "نظام" نجرب بدونه والعكس
        if (str_starts_with($systemName, 'نظام ')) {
            $alternatives[] = mb_substr($systemName, 5); // بدون "نظام "
        } else {
            $alternatives[] = 'نظام ' . $systemName;
        }

        // لو فيه "لائحة" نجرب "اللائحة التنفيذية لـ"
        if (str_contains($systemName, 'لائحة') && !str_contains($systemName, 'اللائحة التنفيذية')) {
            $withPrefix = str_replace('لائحة', 'اللائحة التنفيذية ل', $systemName);
            $alternatives[] = $withPrefix;
        }

        // لو "لائحة نظام..." نجرب "اللائحة التنفيذية لنظام..."
        if (str_starts_with($systemName, 'لائحة نظام ')) {
            $alternatives[] = 'اللائحة التنفيذية ل' . mb_substr($systemName, 6);
        }

        foreach ($alternatives as $alt) {
            if (isset($index[$alt][$artNum])) {
                return $index[$alt][$artNum];
            }
        }

        // مطابقة جزئية: نبحث في كل عنوان عن تطابق جزئي
        foreach ($index as $title => $nums) {
            if (isset($nums[$artNum])) {
                // نتحقق إن الاسم يشبه النظام المطلوب
                if (str_contains($title, $systemName) || str_contains($systemName, $title)) {
                    return $nums[$artNum];
                }
            }
        }

        return null;
    }
}
