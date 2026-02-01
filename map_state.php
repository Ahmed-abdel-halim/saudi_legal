<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Models\TaskAssignment;

echo "--- EXPERTS ---\n";
$experts = User::where('role', 'expert')->get();
foreach($experts as $e) {
    echo "[$e->id] $e->email (Active: $e->is_active)\n";
}

echo "\n--- TASKS (Pending/In_Progress) ---\n";
$tasks = AiTask::whereIn('status', ['Pending', 'In_Progress'])->get();
foreach($tasks as $t) {
    echo "[$t->id] Status: $t->status | Responses: $t->current_responses/$t->required_responses\n";
}

echo "\n--- ASSIGNMENTS ---\n";
$assignments = TaskAssignment::all();
foreach($assignments as $a) {
    echo "Task: $a->task_id | Expert: $a->expert_id | Active: " . ($a->expires_at > now() ? 'YES' : 'NO') . "\n";
}

// Check Expert 11 specifically against Task 19 logic
$e11 = User::find(11);
if ($e11) {
    echo "\n--- Expert 11 Check ---\n";
    $hasAssignment = TaskAssignment::where('expert_id', 11)->where('task_id', 19)->exists();
    echo "Expert 11 Assigned to Task 19 History? " . ($hasAssignment ? 'YES' : 'NO') . "\n";
}
