<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$count = User::where('role', 'expert')->update([
    'is_active' => true,
    'is_active_for_hire' => true
]);

echo "Activated $count expert accounts.\n";
