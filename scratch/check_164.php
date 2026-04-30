<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;
use App\Services\LegalReferenceService;

$service = new LegalReferenceService();
$text = "تُلزم المادة (164) من اللائحة التنفيذية لنظام المحاكم التجارية المحكمة بتضمين حكمها الفصل في طلب التعويض عن الأضرار المادية والمعنوية، بما في ذلك مصاريف التقاضي";

echo "Testing extraction...\n";
$articles = $service->getMentionedArticles($text);

if ($articles->isEmpty()) {
    echo "No articles found via service.\n";
} else {
    echo "Found articles:\n";
    foreach ($articles as $article) {
        echo "- ID: {$article->id}, Title: {$article->article_title}, Law: {$article->legislation_title}\n";
    }
}

echo "\nManual Database Search for Article 164 in Commercial Law Regulations:\n";
$manual = LegalArticle::where('article_title', 'LIKE', '%المادة 164%')
    ->orWhere('article_title', 'LIKE', '%المادة (164)%')
    ->get();

foreach ($manual as $m) {
    echo "- ID: {$m->id}, Title: {$m->article_title}, Law: {$m->legislation_title}\n";
}
