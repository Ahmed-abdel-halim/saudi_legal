<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = 'abdalhlym674@gmail.com';
$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    echo "Current Role: {$user->role}\n";
    echo "Updating role to 'expert'...\n";
    $user->role = 'expert';
    $user->expert_domain = 'law'; // Ensure expert domain is set to 'law' or whatever is appropriate
    $user->save();
    
    echo "Updated Role: {$user->role}\n";
    echo "Expert Domain: {$user->expert_domain}\n";
    echo "User updated successfully!\n";
} else {
    echo "User '{$email}' not found!\n";
}
