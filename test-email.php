<?php

/**
 * Quick Email Test Script
 * 
 * This script tests the email configuration by sending a test email
 * Run: php test-email.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\InviteEmployee;
use App\Models\User;

echo "=================================\n";
echo "Radiif Email Configuration Test\n";
echo "=================================\n\n";

// Test 1: Simple Email
echo "Test 1: Sending simple test email...\n";
try {
    Mail::raw('This is a test email from Radiif.', function($message) {
        $message->to('muhameddelsayed@gmail.com')
                ->subject('Radiif Email Test - ' . date('Y-m-d H:i:s'));
    });
    echo "✅ Simple email sent successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Invitation Email Template
echo "Test 2: Sending invitation email template...\n";
try {
    // Create a test user object
    $testUser = new User();
    $testUser->id = 999;
    $testUser->name = 'Test Employee';
    $testUser->email = 'test@example.com';
    
    // Create test activation URL
    $activationUrl = url('/activate/999?test=true');
    
    // Send invitation email
    Mail::to('muhameddelsayed@gmail.com')
        ->send(new InviteEmployee($testUser, $activationUrl));
    
    echo "✅ Invitation email sent successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "=================================\n";
echo "Testing Complete!\n";
echo "Check your inbox at: muhameddelsayed@gmail.com\n";
echo "=================================\n";
