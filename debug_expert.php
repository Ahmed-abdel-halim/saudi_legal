<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Services\TaskAssignmentService;

// Check Experts
echo "--- Expert Status ---\n";
$experts = User::where('role', 'expert')->get();
foreach ($experts as $expert) {
    echo "ID: {$expert->id} | Name: {$expert->name} | Email: {$expert->email}\n";
    echo "  Active: " . ($expert->is_active ? 'YES' : 'NO') . "\n";
    echo "  For Hire: " . ($expert->is_active_for_hire ? 'YES' : 'NO') . "\n";
    echo "  Banned: " . ($expert->is_banned ? 'YES' : 'NO') . "\n";
    echo "-------------------------\n";
}

// Check Tasks
echo "\n--- Pending Tasks ---\n";
$pendingCount = AiTask::where('status', 'Pending')->count();
echo "Pending Tasks Count: {$pendingCount}\n";

// Dry Run Assignment
if ($experts->isNotEmpty()) {
    $expert = $experts->first();
    echo "\n--- Attempting Assignment for Expert {$expert->id} ---\n";
    $service = new TaskAssignmentService();
    try {
        $task = $service->assignNextTask($expert);
        if ($task) {
            echo "SUCCESS: Assigned Task {$task->id}\n";
        } else {
            echo "FAILED: null returned (Silent failure in Service?)\n";
            // Let's try explicit assignment to see error
            $candidate = AiTask::where('status', 'Pending')->first();
            if ($candidate) {
                echo "Retrying explicit assignment for Task {$candidate->id}...\n";
                $service->assignTaskToExpert($candidate->id, $expert->id);
                echo "Explicit Assignment Success!\n";
            }
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
