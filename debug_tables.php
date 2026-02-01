<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Dump Config
$config = DB::connection()->getConfig();
echo "DB Connection: " . $config['driver'] . "\n";
echo "DB Host: " . ($config['host'] ?? 'null') . "\n";
echo "DB Database: " . ($config['database'] ?? 'null') . "\n";

echo "Tables:\n";
$tables = DB::select('SHOW TABLES');
foreach($tables as $t) {
    // Convert object to array values
    foreach((array)$t as $value) {
        if (str_contains($value, 'task')) echo "- $value\n";
    }
}

echo "\nai_tasks_v2 Count: " . DB::table('ai_tasks_v2')->count() . "\n";
if (Schema::hasTable('ai_tasks')) {
    echo "ai_tasks (v1) Count: " . DB::table('ai_tasks')->count() . "\n";
}

// Create a valid task
$taskId = DB::table('ai_tasks_v2')->insertGetId([
    'task_type' => 'test_visibility',
    'original_data' => 'Visibility Test Task',
    'status' => 'Pending',
    'required_responses' => 3,
    'current_responses' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "Created Test Task ID: $taskId\n";

// Check assignment availability
$expert = \App\Models\User::where('role', 'expert')->first();
$service = new \App\Services\TaskAssignmentService();
try {
    $assigned = $service->assignNextTask($expert);
    if ($assigned && $assigned->id == $taskId) {
        echo "SUCCESS: Expert assigned to Test Task!\n";
    } else {
        echo "FAILED: Expert NOT assigned. Assigned: " . ($assigned->id ?? 'None') . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
