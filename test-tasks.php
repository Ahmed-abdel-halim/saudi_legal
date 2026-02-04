php
<?php
// Test script to check task retrieval
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AiTask;
use App\Models\User;

echo "=== Task Retrieval Test ===\n\n";

// Check tasks
echo "Total tasks: " . AiTask::count() . "\n";
echo "Pending tasks: " . AiTask::where('status', 'pending')->count() . "\n";
echo "Tasks with space: " . AiTask::where('status', 'pending')
    ->whereColumn('current_responses', '<', 'required_responses')->count() . "\n\n";

// Show task details
echo "Task Details:\n";
AiTask::all()->each(function($task) {
    echo "ID: {$task->id} | Status: {$task->status} | ";
    echo "Responses: {$task->current_responses}/{$task->required_responses} | ";
    echo "Data: " . substr($task->original_data, 0, 50) . "\n";
});

echo "\n=== Users with 'expert' or 'student' role ===\n";
User::whereIn('role', ['expert', 'student'])->get(['id', 'name', 'email', 'role'])->each(function($user) {
    echo "ID: {$user->id} | Name: {$user->name} | Role: {$user->role}\n";
});
