<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalCitation;
use App\Models\LegalRecord;
use App\Models\LegalQaPair;
use App\Models\LegalArticle;

// ============================================================
// عرض نصوص legal_articles المرتبطة بـ Task #13
// ============================================================
$qa = LegalQaPair::find(13);

if (!$qa) {
    echo "Task #13 غير موجود.\n";
    exit;
}

echo "=== Task #13 ===\n";
echo "السؤال: " . $qa->question . "\n\n";

// جيب الـ citations المرتبطة بالـ QA pair أو بالـ record
$citations = $qa->citations()->with('article')->get();
if ($citations->isEmpty()) {
    $citations = LegalCitation::where('record_id', $qa->record_id)->with('article')->get();
}

echo "عدد الـ Citations: " . $citations->count() . "\n\n";

foreach ($citations as $i => $c) {
    echo "--- Citation #" . ($i + 1) . " ---\n";
    echo "  system_name    : " . ($c->system_name ?? '-') . "\n";
    echo "  article_number : " . ($c->article_number ?? '-') . "\n";
    echo "  citation_source: " . ($c->citation_source ?? '-') . "\n";
    echo "  legal_article_id: " . ($c->legal_article_id ?? 'NULL') . "\n";

    if ($c->article) {
        echo "  [legal_articles] legislation_title: " . $c->article->legislation_title . "\n";
        echo "  [legal_articles] article_title    : " . $c->article->article_title . "\n";
        echo "  [legal_articles] content:\n";
        echo "    " . wordwrap($c->article->content, 80, "\n    ") . "\n";
    } else {
        echo "  [legal_articles] غير مرتبط بمادة في الجدول\n";
    }
    echo "\n";
}

// ============================================================
// بحث مباشر في legal_articles بكلمات من السؤال
// ============================================================
echo "=== بحث في legal_articles بكلمة 'الشريك' ===\n";
LegalArticle::where('content', 'like', '%الشريك%')
    ->orWhere('content', 'like', '%رأس المال%')
    ->limit(5)
    ->get()
    ->each(function ($a) {
        echo "  [{$a->legislation_id}] {$a->legislation_title} - {$a->article_title}\n";
        echo "  " . mb_substr($a->content, 0, 200) . "...\n\n";
    });
