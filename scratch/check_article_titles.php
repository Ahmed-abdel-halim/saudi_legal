<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

// Let's sample article titles under different legislations in the database
$legislations = ['نظام المحاكم التجارية', 'نظام الإثبات', 'نظام الشركات', 'نظام العمل'];

foreach ($legislations as $leg) {
    $count = LegalArticle::where('legislation_title', $leg)->count();
    echo "Legislation: {$leg} (Total: {$count})\n";
    $samples = LegalArticle::where('legislation_title', $leg)
        ->select('article_title')
        ->take(5)
        ->get();
    foreach ($samples as $s) {
        echo "  - Title: '{$s->article_title}'\n";
    }
}
