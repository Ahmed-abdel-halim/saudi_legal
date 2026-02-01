<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaskAssignment;

$assignments = TaskAssignment::all();
echo "Total Assignments: " . $assignments->count() . "\n";

foreach ($assignments as $a) {
    echo "Task: {$a->task_id} | Expert: {$a->expert_id} | Expires: {$a->expires_at}\n";
}
