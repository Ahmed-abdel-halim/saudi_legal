<?php
// Test the workbench action endpoint directly
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "==============================================\n";
echo "TESTING WORKBENCH ACTION ENDPOINT\n";
echo "==============================================\n\n";

// Simulate being logged in as Expert 11
$expert = User::find(11);
if (!$expert) {
    echo "ERROR: Expert 11 not found!\n";
    exit(1);
}

Auth::login($expert);
echo "Logged in as: {$expert->full_name} (ID: {$expert->id})\n\n";

// Get the current task
$assignmentService = new \App\Services\TaskAssignmentService();
$task = $assignmentService->assignNextTask($expert);

if (!$task) {
    echo "ERROR: No task available!\n";
    exit(1);
}

echo "Current Task: ID {$task->id}\n";
echo "Task Data: {$task->original_data}\n\n";

// Create a request to simulate the action
echo "--- Simulating POST to /dashboard/expert/workbench/action ---\n";
echo "Action: mark_correct\n";
echo "Task ID: {$task->id}\n\n";

$request = Request::create('/dashboard/expert/workbench/action', 'POST', [
    'action' => 'mark_correct',
    'task_id' => $task->id,
    '_token' => csrf_token(),
]);

try {
    $controller = new \App\Http\Controllers\Dashboard\Expert\WorkbenchController(
        new \App\Services\TaskAssignmentService()
    );
    
    $response = $controller->action($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content:\n";
    echo $response->getContent() . "\n";
    
} catch (\Throwable $e) {
    echo "\n✗✗✗ EXCEPTION CAUGHT:\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n==============================================\n";
echo "END OF TEST\n";
echo "==============================================\n";
