<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LegalArticle;

$article = LegalArticle::where('article_number', '164')->where('legislation_title', 'LIKE', '%التجارية%')->first();
if ($article) {
    echo "FOUND: " . $article->legislation_title . " - " . $article->article_title;
} else {
    echo "NOT FOUND!";
}
