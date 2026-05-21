<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

$count = LegalArticle::where('legislation_title', 'النظام التجاري ( نظام المحكمة التجارية)')->count();
echo "Count for النظام التجاري ( نظام المحكمة التجارية): " . $count . "\n";

$sample = LegalArticle::where('legislation_title', 'النظام التجاري ( نظام المحكمة التجارية)')
    ->select('id', 'article_title')
    ->orderBy('id')
    ->take(10)
    ->get();

foreach ($sample as $s) {
    echo "- ID: {$s->id} | Title: {$s->article_title}\n";
}
