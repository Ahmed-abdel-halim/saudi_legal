<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

echo "Searching for Article 92 in Civil Procedure Law...\n";

$articles = LegalArticle::where('legislation_title', 'LIKE', '%المرافعات الشرعية%')
    ->where(function($q) {
        $q->where('article_title', 'LIKE', '%92%')
          ->orWhere('article_title', 'LIKE', '%الثانية والتسعون%');
    })
    ->get();

foreach ($articles as $a) {
    echo "ID: {$a->id} | Title: {$a->article_title} | System: {$a->legislation_title}\n";
}

if ($articles->isEmpty()) {
    echo "NOT FOUND. Let's see some samples from this system:\n";
    $samples = LegalArticle::where('legislation_title', 'LIKE', '%المرافعات الشرعية%')->limit(5)->get();
    foreach ($samples as $s) {
        echo "Sample Title: {$s->article_title}\n";
    }
}
