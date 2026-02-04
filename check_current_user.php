<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Start session to check who's logged in
session_start();

echo "=== Current Session Check ===\n";
if (isset($_SESSION['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'])) {
    $userId = $_SESSION['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'];
    echo "Logged in User ID: " . $userId . "\n";
    
    $user = App\Models\User::find($userId);
    if ($user) {
        echo "Name: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Avatar Path: " . ($user->avatar_path ?? 'NULL') . "\n";
        
        if ($user->avatar_path) {
            $fullPath = public_path('uploads/' . $user->avatar_path);
            echo "Expected File Path: " . $fullPath . "\n";
            echo "File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
            echo "Generated URL: " . asset('uploads/' . $user->avatar_path) . "\n";
        }
    }
} else {
    echo "No user logged in via session\n";
}

// Also check User ID 33 specifically
echo "\n=== User ID 33 (Ali Hussien) Check ===\n";
$ali = App\Models\User::find(33);
if ($ali) {
    echo "Name: " . $ali->name . "\n";
    echo "Avatar Path: " . ($ali->avatar_path ?? 'NULL') . "\n";
    
    if ($ali->avatar_path) {
        $fullPath = public_path('uploads/' . $ali->avatar_path);
        echo "Expected File Path: " . $fullPath . "\n";
        echo "File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        echo "Generated URL: " . asset('uploads/' . $ali->avatar_path) . "\n";
    }
}
