<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('name', 'LIKE', '%Ahmed Abdel Halim%')->first();
if ($user) {
    $user->update([
        'expert_domain' => 'law',
        'expert_specialization' => 'Legal Advisor',
        'is_active' => true,
        'is_active_for_hire' => true,
        'role' => 'expert'
    ]);
    echo "User updated: {$user->name}\n";
    echo "Domain: {$user->expert_domain}\n";
    echo "Specialization: {$user->expert_specialization}\n";
} else {
    echo "User not found\n";
}
