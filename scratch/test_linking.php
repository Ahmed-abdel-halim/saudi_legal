<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\LegalReferenceService;

$service = new LegalReferenceService();
$sampleText = "بناء على المادة 92 من نظام المرافعات الشرعية، وحيث أن المدعي قدم بينة...";

echo "Testing extraction for: '{$sampleText}'\n";
$articles = $service->getMentionedArticles($sampleText);

if ($articles->count() > 0) {
    foreach ($articles as $article) {
        echo "\n[FOUND] " . $article->article_title . " from " . $article->legislation_title . "\n";
        echo "Content Snippet: " . substr(strip_tags($article->content), 0, 200) . "...\n";
    }
} else {
    echo "\n[NOT FOUND] No articles matched.\n";
}
