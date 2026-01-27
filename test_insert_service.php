<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find an expert user
$user = DB::table('users')->first();

if ($user) {
    // Insert a test expert service
    DB::table('expert_services')->insert([
        'expert_id' => $user->id,
        'title' => 'تطوير تطبيقات ويب احترافية',
        'category' => 'تطوير البرمجيات',
        'price' => 150.00,
        'delivery_days' => 7,
        'description' => 'خدمة تطوير تطبيقات ويب احترافية باستخدام Laravel و Vue.js مع خبرة أكثر من 5 سنوات',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Test service created successfully for user: {$user->name}\n";
    echo "Service ID: " . DB::getPdo()->lastInsertId() . "\n";
} else {
    echo "❌ No user found in database\n";
}
