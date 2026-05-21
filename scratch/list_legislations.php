<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$titles = DB::table('legal_articles')
    ->select('legislation_title', DB::raw('count(*) as count'))
    ->groupBy('legislation_title')
    ->get();

foreach ($titles as $t) {
    echo "- {$t->legislation_title} (Count: {$t->count})\n";
}
