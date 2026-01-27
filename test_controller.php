<?php

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ServiceController;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Testing ServiceController Browse Method ===\n\n";

// Create a mock request
$request = new \Illuminate\Http\Request();

// Create controller instance
$controller = new ServiceController();

// Use reflection to call the private method
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('fetchServicesFromDatabase');
$method->setAccessible(true);

try {
    $services = $method->invoke($controller, '', [], '', '', '');
    echo "Services found: " . $services->count() . "\n\n";
    
    foreach ($services as $service) {
        echo "ID: {$service->service_id}\n";
        echo "Title: {$service->title}\n";
        echo "Type: {$service->service_type}\n";
        echo "Expert: {$service->expert_name}\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "This will trigger mock data fallback\n";
}
