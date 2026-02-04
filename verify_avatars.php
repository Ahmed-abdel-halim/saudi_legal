<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FINAL AVATAR SYSTEM VERIFICATION ===\n\n";

// Check User 33 (Ali Hussien)
$user = App\Models\User::find(33);

if ($user) {
    echo "User Found: {$user->name} (ID: {$user->id})\n";
    echo "Avatar Path in DB: " . ($user->avatar_path ?? 'NULL') . "\n\n";
    
    if ($user->avatar_path) {
        $fullPath = public_path('uploads/' . $user->avatar_path);
        $url = asset('uploads/' . $user->avatar_path);
        
        echo "Expected File Location: {$fullPath}\n";
        echo "File Exists: " . (file_exists($fullPath) ? '✓ YES' : '✗ NO') . "\n";
        echo "Generated URL: {$url}\n\n";
        
        if (file_exists($fullPath)) {
            echo "✓ AVATAR SYSTEM IS WORKING!\n";
            echo "\nTo test:\n";
            echo "1. Visit: http://127.0.0.1:8000/test-avatar.html\n";
            echo "2. Login as Ali Hussien and visit dashboard\n";
            echo "3. Press Ctrl+Shift+R to hard refresh\n";
        } else {
            echo "✗ FILE NOT FOUND - Need to copy avatar file\n";
        }
    } else {
        echo "✗ No avatar_path set in database\n";
    }
} else {
    echo "✗ User ID 33 not found\n";
}

echo "\n=== ALL USERS WITH AVATARS ===\n";
$usersWithAvatars = App\Models\User::whereNotNull('avatar_path')->get();
foreach ($usersWithAvatars as $u) {
    $exists = file_exists(public_path('uploads/' . $u->avatar_path));
    echo "User {$u->id}: {$u->name} - " . ($exists ? '✓' : '✗') . "\n";
}
