<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Models\TaskAssignment;
use App\Services\TaskAssignmentService;

Schema::disableForeignKeyConstraints();
TaskAssignment::truncate();
AiTask::truncate();
User::where('email', 'workbench_test@test.com')->delete();
Schema::enableForeignKeyConstraints();

// Create Expert
$expert = User::create([
    'name' => 'Workbench Test',
    'email' => 'workbench_test@test.com',
    'password' => bcrypt('password'),
    'role' => 'expert',
    'is_active' => true,
    'is_active_for_hire' => true, // Eligible
    'trust_score' => 100
]);

// Create Pending Task
$task = AiTask::create([
    'task_type' => 'test',
    'original_data' => 'Pending Task Data',
    'status' => 'Pending',
    'required_responses' => 3,
    'current_responses' => 0,
]);

$service = new TaskAssignmentService();

echo "Testing assignNextTask...\n";

// 1. Should assign the pending task
$assignedTask = $service->assignNextTask($expert);

if ($assignedTask && $assignedTask->id === $task->id) {
    echo "SUCCESS: Assigned pending task {$task->id}\n";
} else {
    echo "FAILED: Did not assign pending task.\n";
}

// 2. Should return same task if called again (existing assignment)
$assignedTask2 = $service->assignNextTask($expert);
if ($assignedTask2 && $assignedTask2->id === $task->id) {
    echo "SUCCESS: Returned existing assignment.\n";
} else {
    echo "FAILED: Did not return existing assignment.\n";
}

// 3. Create another task
$task2 = AiTask::create([
    'task_type' => 'test',
    'original_data' => 'Task 2',
    'status' => 'Pending',
    'required_responses' => 3,
    'current_responses' => 0,
]);

// 4. Since expert has active assignment (Task 1), assignNextTask should still return Task 1?
// Logic says: "Check for existing active assignment... if $existingAssignment return it"
// So strictly it returns the FIRST active assignment found.
$assignedTask3 = $service->assignNextTask($expert);
echo "Result with active assignment: Task {$assignedTask3->id}\n";

