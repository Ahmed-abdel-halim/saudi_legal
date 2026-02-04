<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Avatar Diagnostic Tool ===\n\n";

// Get all users
$users = App\Models\User::all();

echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Avatar Path: " . ($user->avatar_path ?? 'NULL') . "\n";
    
    if ($user->avatar_path) {
        $fullPath = storage_path('app/public/' . $user->avatar_path);
        echo "Full Path: {$fullPath}\n";
        echo "File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        echo "URL: " . asset('storage/' . $user->avatar_path) . "\n";
    }
    
    echo "---\n\n";
}

// Check storage directory
echo "\n=== Storage Directory Check ===\n";
$avatarDir = storage_path('app/public/avatars');
echo "Avatar Directory: {$avatarDir}\n";
echo "Directory Exists: " . (is_dir($avatarDir) ? 'YES' : 'NO') . "\n";

if (is_dir($avatarDir)) {
    $files = scandir($avatarDir);
    $files = array_diff($files, ['.', '..']);
    echo "Files in directory: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "  - {$file}\n";
    }
}

echo "\n=== Public Storage Symlink Check ===\n";
$publicStorage = public_path('storage');
echo "Public Storage Path: {$publicStorage}\n";
echo "Symlink Exists: " . (is_link($publicStorage) || is_dir($publicStorage) ? 'YES' : 'NO') . "\n";

if (is_link($publicStorage)) {
    echo "Points to: " . readlink($publicStorage) . "\n";
}
