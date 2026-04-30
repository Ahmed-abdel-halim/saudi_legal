<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

echo "Available Legislation Titles:\n";
$titles = LegalArticle::distinct()->pluck('legislation_title');
foreach ($titles as $title) {
    echo "- $title\n";
}

echo "\nSearching for Article 164 in any Law:\n";
$articles = LegalArticle::where('article_title', 'LIKE', '%164%')->get();
foreach ($articles as $article) {
    echo "- Law: {$article->legislation_title}, Article: {$article->article_title}\n";
}
