<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

$system = 'تنظيم مدينة الملك عبدالله للطاقة الذرية والمتجددة';
$exists = LegalArticle::where('legislation_title', 'LIKE', '%' . $system . '%')
    ->orWhere('legislation_title', 'LIKE', '%مدينة الملك عبدالله للطاقة%')
    ->get();

echo "Count: " . $exists->count() . "\n";
foreach ($exists as $art) {
    echo "- ID: {$art->id} | Title: {$art->article_title} | Legislation: {$art->legislation_title}\n";
}
