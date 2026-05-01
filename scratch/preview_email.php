<?php

use App\Mail\InviteEmployee;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mock data
$user = User::first() ?? new User(['name' => 'John Doe']);
$activationUrl = 'https://radiif.com/activate/test-token';

$mailable = new InviteEmployee($user, $activationUrl);

// Render the mail
$html = $mailable->render();

// Save to file
file_put_contents(__DIR__ . '/scratch/email_preview.html', $html);

echo "Email preview saved to scratch/email_preview.html\n";
