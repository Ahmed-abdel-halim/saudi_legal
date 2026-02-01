<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\TaskAssignmentService;

echo "==============================================\n";
echo "TESTING TASK ASSIGNMENT SERVICE\n";
echo "==============================================\n\n";

// Get expert
$expertId = 11;
$expert = User::find($expertId);

if (!$expert) {
    echo "ERROR: Expert ID {$expertId} not found!\n";
    exit(1);
}

echo "Testing with Expert: {$expert->full_name} (ID: {$expertId})\n";
echo "Expert Details:\n";
echo "  - Role: {$expert->role}\n";
echo "  - Active: " . ($expert->is_active ? 'YES' : 'NO') . "\n";
echo "  - Active for Hire: " . ($expert->is_active_for_hire ? 'YES' : 'NO') . "\n";
echo "  - Banned: " . ($expert->is_banned ? 'YES' : 'NO') . "\n\n";

// Test the assignment service
echo "--- Calling TaskAssignmentService->assignNextTask() ---\n";

try {
    $assignmentService = new TaskAssignmentService();
    $task = $assignmentService->assignNextTask($expert);
    
    if ($task) {
        echo "SUCCESS: Task assigned!\n";
        echo "  Task ID: {$task->id}\n";
        echo "  Task Type: {$task->task_type}\n";
        echo "  Status: {$task->status}\n";
        echo "  Original Data: {$task->original_data}\n";
        echo "  Current Responses: {$task->current_responses}\n";
        echo "  Required Responses: {$task->required_responses}\n";
        echo "  Created At: {$task->created_at}\n";
    } else {
        echo "FAILED: assignNextTask() returned NULL\n";
        echo "This means no task was assigned despite having available tasks.\n";
        echo "\nPossible reasons:\n";
        echo "  1. Expert eligibility check failed\n";
        echo "  2. All assignments have expired\n";
        echo "  3. Exception was caught and returned null\n";
        echo "  4. No candidate tasks found (but diagnostic shows 8 available)\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION CAUGHT: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n==============================================\n";
echo "END OF TEST\n";
echo "==============================================\n";
