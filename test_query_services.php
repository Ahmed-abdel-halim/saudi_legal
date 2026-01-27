<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Expert Services Query ===\n\n";

// Test the expert services query
try {
    $expertServices = DB::table('expert_services as es')
        ->join('users as u', 'es.expert_id', '=', 'u.id')
        ->where('es.is_active', 1)
        ->select([
            'es.service_id',
            'es.title',
            'es.description',
            'es.price as hourly_rate',
            DB::raw("NULL as service_image"),
            'u.name as expert_name',
            'es.category as industry'
        ])
        ->get();
    
    echo "Expert Services Found: " . $expertServices->count() . "\n\n";
    
    foreach ($expertServices as $service) {
        echo "ID: {$service->service_id}\n";
        echo "Title: {$service->title}\n";
        echo "Expert: {$service->expert_name}\n";
        echo "Price: {$service->hourly_rate}\n";
        echo "Category: {$service->industry}\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
