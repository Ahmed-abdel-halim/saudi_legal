<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = config('mail.mailers.smtp');
echo "Host: " . $config['host'] . "\n";
echo "Port: " . $config['port'] . "\n";
echo "Username: " . $config['username'] . "\n";
echo "Password is exactly: " . $config['password'] . "\n";
echo "Encryption: " . $config['encryption'] . "\n";
