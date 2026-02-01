<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiTask;
use App\Models\User;
use App\Models\TaskAssignment;

echo "==============================================\n";
echo "WORKBENCH TASK VISIBILITY DIAGNOSTIC\n";
echo "==============================================\n\n";

// Get an expert user (assuming expert ID 11 from previous logs)
$expertId = 11;
$expert = User::find($expertId);

if (!$expert) {
    echo "ERROR: Expert ID {$expertId} not found!\n";
    exit(1);
}

echo "Expert: {$expert->full_name} (ID: {$expertId})\n";
echo "Role: {$expert->role}\n";
echo "Active: " . ($expert->is_active ? 'YES' : 'NO') . "\n";
echo "Active for Hire: " . ($expert->is_active_for_hire ? 'YES' : 'NO') . "\n";
echo "Banned: " . ($expert->is_banned ? 'YES' : 'NO') . "\n\n";

// Check for existing active assignments
echo "--- Checking Existing Active Assignments ---\n";
$existingAssignment = TaskAssignment::where('expert_id', $expertId)
    ->where('expires_at', '>', now())
    ->whereHas('task', function ($q) {
        $q->whereIn('status', ['Pending', 'In_Progress']);
    })
    ->with('task')
    ->first();

if ($existingAssignment) {
    echo "Found existing assignment:\n";
    echo "  Task ID: {$existingAssignment->task_id}\n";
    echo "  Task Status: {$existingAssignment->task->status}\n";
    echo "  Assigned At: {$existingAssignment->assigned_at}\n";
    echo "  Expires At: {$existingAssignment->expires_at}\n\n";
} else {
    echo "No existing active assignments found.\n\n";
}

// Check for candidate tasks
echo "--- Checking Candidate Tasks ---\n";
$candidateTasks = AiTask::whereIn('status', ['Pending', 'In_Progress'])
    ->whereColumn('current_responses', '<', 'required_responses')
    ->whereDoesntHave('assignments', function ($q) use ($expertId) {
        $q->where('expert_id', $expertId);
    })
    ->orderBy('id', 'asc')
    ->get();

echo "Found {$candidateTasks->count()} candidate tasks:\n\n";

foreach ($candidateTasks as $task) {
    echo "Task ID: {$task->id}\n";
    echo "  Status: {$task->status}\n";
    echo "  Current Responses: {$task->current_responses}\n";
    echo "  Required Responses: {$task->required_responses}\n";
    echo "  Comparison (current < required): " . ($task->current_responses < $task->required_responses ? 'TRUE' : 'FALSE') . "\n";
    echo "  Client ID: {$task->client_id}\n";
    echo "  Created At: {$task->created_at}\n";
    
    // Check assignments for this task
    $assignments = TaskAssignment::where('task_id', $task->id)->get();
    echo "  Total Assignments: {$assignments->count()}\n";
    foreach ($assignments as $assignment) {
        echo "    - Expert ID: {$assignment->expert_id}, Expires: {$assignment->expires_at}\n";
    }
    echo "\n";
}

// Check all tasks regardless of filters
echo "--- All Tasks Summary ---\n";
$allTasks = AiTask::all();
echo "Total tasks in database: {$allTasks->count()}\n";
$statusBreakdown = $allTasks->groupBy('status');
foreach ($statusBreakdown as $status => $tasks) {
    echo "  {$status}: {$tasks->count()}\n";
}

echo "\n==============================================\n";
echo "END OF DIAGNOSTIC\n";
echo "==============================================\n";
