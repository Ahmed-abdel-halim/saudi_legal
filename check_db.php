<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LegalArticle;
use App\Models\LegalTask;

echo "Articles Count: " . LegalArticle::count() . "\n";
echo "Tasks Count: " . LegalTask::count() . "\n";

$alimonyArticles = LegalArticle::where('content', 'like', '%نفقة%')->get();
echo "Alimony Articles Found: " . $alimonyArticles->count() . "\n";
foreach($alimonyArticles as $art) {
    echo "- " . $art->legislation_title . " | " . $art->article_title . "\n";
}
