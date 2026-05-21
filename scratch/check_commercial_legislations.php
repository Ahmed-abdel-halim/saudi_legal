<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

// Distinct legislations
$legislations = LegalArticle::select('legislation_title')->distinct()->get();
echo "--- Legislations in DB ---\n";
foreach ($legislations as $leg) {
    echo "- {$leg->legislation_title}\n";
}

// Articles under اللائحة التنفيذية لنظام المحاكم التجارية
echo "\n--- Articles under اللائحة التنفيذية لنظام المحاكم التجارية ---\n";
$articles = LegalArticle::where('legislation_title', 'LIKE', '%المحاكم التجارية%')
    ->select('id', 'article_title', 'legislation_title')
    ->orderBy('id')
    ->get();

echo "Total: " . $articles->count() . "\n";
foreach ($articles->take(30) as $art) {
    echo "ID: {$art->id} | Title: {$art->article_title} | Legislation: {$art->legislation_title}\n";
}

if ($articles->count() > 30) {
    echo "... and " . ($articles->count() - 30) . " more articles.\n";
    // Let's print the last 20
    foreach ($articles->slice(-20) as $art) {
        echo "ID: {$art->id} | Title: {$art->article_title} | Legislation: {$art->legislation_title}\n";
    }
}
