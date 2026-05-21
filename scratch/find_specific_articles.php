<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

// Find all articles in DB with Title containing "الرابعة والستون بعد المائة" or "164" or "السابعة والثلاثون بعد المائتين" or "237"
$articles = LegalArticle::where('article_title', 'LIKE', '%الرابعة والستون بعد المائة%')
    ->orWhere('article_title', 'LIKE', '%164%')
    ->orWhere('article_title', 'LIKE', '%السابعة والثلاثون بعد المائتين%')
    ->orWhere('article_title', 'LIKE', '%237%')
    ->get();

echo "Total matching articles in DB: " . $articles->count() . "\n";
foreach ($articles as $art) {
    echo "- ID: {$art->id} | Title: {$art->article_title} | Legislation: {$art->legislation_title}\n";
}
