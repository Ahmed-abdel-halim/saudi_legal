<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'lawyer' . rand(10, 99) . '@radiif.com';
$password = 'password123';

$user = User::create([
    'name' => 'المحامي التجريبي',
    'email' => $email,
    'password' => Hash::make($password),
    'role' => 'expert', // يجب أن يكون خبير
    'expert_domain' => 'law', // هذا هو المفتاح السحري
    'job_title' => 'مستشار قانوني سعودي',
    'is_active' => true,
]);

echo "--- ⚖️ New Lawyer Account Created --- \n";
echo "Email: $email\n";
echo "Password: $password\n";
echo "Role: Expert | Domain: Law\n";
echo "------------------------------------ \n";
echo "Go to /login to test the Saudi Legal module.\n";
