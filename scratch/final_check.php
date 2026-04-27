<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

echo "--- Checking Evidence Law (نظام الإثبات) ---\n";
$system = LegalArticle::where('legislation_title', 'LIKE', '%الإثبات%')->first();
if ($system) {
    echo "Evidence Law Found: " . $system->legislation_title . "\n";
    
    echo "\n--- Searching for Article 92 in it ---\n";
    $article = LegalArticle::where('legislation_title', 'LIKE', '%الإثبات%')
        ->where('article_title', 'LIKE', '%الثانية والتسعون%')
        ->first();
    
    if ($article) {
        echo "Article 92 Found! Content: " . mb_substr($article->content, 0, 100) . "...\n";
    } else {
        echo "Article 92 NOT FOUND in Evidence Law. Checking all articles in this law:\n";
        $all = LegalArticle::where('legislation_title', 'LIKE', '%الإثبات%')->limit(10)->get();
        foreach ($all as $a) {
            echo " - " . $a->article_title . "\n";
        }
    }
} else {
    echo "Evidence Law (نظام الإثبات) NOT FOUND in database at all.\n";
}
