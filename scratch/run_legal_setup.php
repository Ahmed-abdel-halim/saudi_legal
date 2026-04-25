<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "--- Starting Saudi Legal Setup ---\n";

try {
    echo "1. Running Migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();

    echo "2. Seeding Sample Data...\n";
    // We can't call a specific seeder class via Artisan::call as easily if it's not registered
    // So we will manually run the seeder logic here
    $seeder = new \Database\Seeders\LegalTaskSeeder();
    $seeder->up();
    echo "✅ Sample data seeded successfully.\n";

    echo "--- Setup Complete! ---\n";
    echo "You can now visit: /dashboard/expert/legal-workbench\n";
    echo "(Make sure your user domain is set to 'law')\n";

} catch (\Exception $e) {
    echo "❌ Error during setup: " . $e->getMessage() . "\n";
}
