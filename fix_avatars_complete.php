<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPLETE AVATAR SYSTEM FIX ===\n\n";

// Step 1: Ensure directories exist
echo "Step 1: Creating upload directories...\n";
$avatarDir = public_path('uploads/avatars');
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0755, true);
    echo "✓ Created: $avatarDir\n";
} else {
    echo "✓ Directory exists: $avatarDir\n";
}

// Step 2: Copy ALL files from storage to public/uploads
echo "\nStep 2: Migrating files from storage to public/uploads...\n";
$storageAvatarDir = storage_path('app/public/avatars');
if (is_dir($storageAvatarDir)) {
    $files = scandir($storageAvatarDir);
    $copiedCount = 0;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $source = $storageAvatarDir . '/' . $file;
            $dest = $avatarDir . '/' . $file;
            if (copy($source, $dest)) {
                echo "✓ Copied: $file\n";
                $copiedCount++;
            }
        }
    }
    echo "Total files copied: $copiedCount\n";
} else {
    echo "No storage/app/public/avatars directory found\n";
}

// Step 3: Update ALL user avatar paths in database
echo "\nStep 3: Updating database paths...\n";
$users = App\Models\User::whereNotNull('avatar_path')->get();
$updatedCount = 0;

foreach ($users as $user) {
    $oldPath = $user->avatar_path;
    
    // If path starts with 'avatars/', it's already correct
    if (strpos($oldPath, 'avatars/') === 0) {
        echo "✓ User {$user->id} ({$user->name}): Path already correct\n";
        continue;
    }
    
    // Extract just the filename
    $filename = basename($oldPath);
    $newPath = 'avatars/' . $filename;
    
    // Check if file exists
    $fullPath = public_path('uploads/' . $newPath);
    if (file_exists($fullPath)) {
        $user->avatar_path = $newPath;
        $user->save();
        echo "✓ Updated User {$user->id} ({$user->name}): {$oldPath} → {$newPath}\n";
        $updatedCount++;
    } else {
        echo "✗ User {$user->id} ({$user->name}): File not found at {$fullPath}\n";
    }
}

echo "\nTotal users updated: $updatedCount\n";

// Step 4: Verify everything
echo "\n=== VERIFICATION ===\n";
$users = App\Models\User::whereNotNull('avatar_path')->get();
foreach ($users as $user) {
    $fullPath = public_path('uploads/' . $user->avatar_path);
    $exists = file_exists($fullPath);
    $url = asset('uploads/' . $user->avatar_path);
    
    echo "\nUser {$user->id}: {$user->name}\n";
    echo "  Path: {$user->avatar_path}\n";
    echo "  File exists: " . ($exists ? 'YES ✓' : 'NO ✗') . "\n";
    echo "  URL: {$url}\n";
}

echo "\n=== FIX COMPLETE ===\n";
