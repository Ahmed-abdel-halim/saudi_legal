<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

// Find all legislations in DB to see what they are named
$legislations = LegalArticle::select('legislation_title')->distinct()->get();
echo "--- Legislations in DB ---\n";
foreach ($legislations as $leg) {
    echo "- {$leg->legislation_title}\n";
}

echo "\n--- Searching for Article 164 and 237 ---\n";
$articles = LegalArticle::where(function($q) {
    $q->where('article_title', 'LIKE', '%مائة%')
      ->orWhere('article_title', 'LIKE', '%مائت%')
      ->orWhere('article_title', 'LIKE', '%164%')
      ->orWhere('article_title', 'LIKE', '%237%');
})->get();

foreach ($articles as $art) {
    echo "ID: {$art->id} | Title: {$art->article_title} | Legislation: {$art->legislation_title}\n";
}
