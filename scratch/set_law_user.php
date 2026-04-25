<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'admin@admin.com'; // Change this to your test user's email

$user = User::where('email', $email)->first();

if ($user) {
    $user->update([
        'expert_domain' => 'law',
        'expert_specialization' => 'Lawyer',
        'job_title' => 'Lawyer'
    ]);
    echo "✅ User {$email} domain updated to 'law'.\n";
} else {
    echo "❌ User {$email} not found.\n";
}
