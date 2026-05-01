<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\SiteSetting::set('price_per_legal_task', 0.25);
echo "Price updated to 0.25 SAR\n";
