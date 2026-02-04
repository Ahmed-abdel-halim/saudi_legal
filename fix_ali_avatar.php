<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('name', 'LIKE', '%Ali%')->first();

if ($user) {
    echo "Found user: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Current Avatar Path: " . ($user->avatar_path ?? 'NULL') . "\n";
    
    // Check if we found the file in uploads
    $filename = 'O6NvYgLKr2l5uAAuXBMOB7WEnA9R0UPKABCQmVD4.jpg';
    $path = 'avatars/' . $filename;
    
    // FORCE UPDATE
    $user->avatar_path = $path;
    $user->save();
    
    echo "UPDATED Avatar Path to: " . $user->avatar_path . "\n";
    echo "Asset URL should be: " . asset('uploads/' . $user->avatar_path) . "\n";
} else {
    echo "User Ali Hussien not found!\n";
}
