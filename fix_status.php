<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiTask;

$count = AiTask::where('status', 'pending')->update(['status' => 'Pending']);
echo "Updated $count tasks from 'pending' to 'Pending'.\n";
