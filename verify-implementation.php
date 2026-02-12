#!/usr/bin/env php
<?php

/**
 * Quick System Verification Script
 * 
 * This script performs basic checks on the marketplace implementation
 * without requiring database connection.
 */

echo "🔍 Marketplace Implementation Verification\n";
echo str_repeat("=", 50) . "\n\n";

$checks = [];
$passed = 0;
$failed = 0;

// Check 1: Required files exist
echo "📁 Checking Required Files...\n";

$requiredFiles = [
    'app/Services/ChatService.php',
    'app/Services/ContractService.php',
    'app/Services/ReviewService.php',
    'app/Http/Controllers/Api/ChatController.php',
    'app/Http/Controllers/Api/ContractController.php',
    'app/Http/Controllers/Api/ReviewController.php',
    'app/Http/Controllers/Admin/DisputeController.php',
    'app/Policies/ConversationPolicy.php',
    'app/Policies/MessagePolicy.php',
    'app/Policies/ProjectOfferPolicy.php',
    'app/Policies/ServicePurchasePolicy.php',
    'app/Policies/AdminPolicy.php',
    'app/Notifications/NewMessageNotification.php',
    'app/Notifications/ServiceStartedNotification.php',
    'app/Notifications/ServiceFinishedNotification.php',
    'app/Notifications/ServiceCompletedNotification.php',
    'app/Notifications/DisputeOpenedNotification.php',
    'app/Notifications/ReviewReceivedNotification.php',
    'app/Events/NewMessageEvent.php',
    'app/Events/ChatCreatedEvent.php',
    'routes/api.php',
    'config/broadcasting.php',
    'resources/js/bootstrap.js',
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "  ✅ $file\n";
        $passed++;
    } else {
        echo "  ❌ $file (MISSING)\n";
        $failed++;
    }
}

echo "\n";

// Check 2: Migrations exist
echo "📊 Checking Migrations...\n";

$requiredMigrations = [
    'database/migrations/2026_02_07_160000_update_conversations_for_contract_architecture.php',
    'database/migrations/2026_02_07_160001_add_service_lifecycle_to_contracts.php',
    'database/migrations/2026_02_07_160002_create_reviews_table.php',
    'database/migrations/2026_02_07_160003_add_reputation_fields_to_users.php',
    'database/migrations/2026_02_07_180000_add_dispute_resolution_fields.php',
];

foreach ($requiredMigrations as $migration) {
    if (file_exists($migration)) {
        echo "  ✅ " . basename($migration) . "\n";
        $passed++;
    } else {
        echo "  ❌ " . basename($migration) . " (MISSING)\n";
        $failed++;
    }
}

echo "\n";

// Check 3: Environment configuration
echo "⚙️  Checking Environment Configuration...\n";

if (file_exists('.env')) {
    $env = file_get_contents('.env');
    
    $envChecks = [
        'BROADCAST_CONNECTION' => strpos($env, 'BROADCAST_CONNECTION=pusher') !== false,
        'PUSHER_APP_KEY' => strpos($env, 'PUSHER_APP_KEY=') !== false,
        'QUEUE_CONNECTION' => strpos($env, 'QUEUE_CONNECTION=') !== false,
    ];
    
    foreach ($envChecks as $key => $exists) {
        if ($exists) {
            echo "  ✅ $key configured\n";
            $passed++;
        } else {
            echo "  ⚠️  $key not configured\n";
            $failed++;
        }
    }
} else {
    echo "  ❌ .env file not found\n";
    $failed++;
}

echo "\n";

// Check 4: Composer dependencies
echo "📦 Checking Composer Dependencies...\n";

if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);
    
    $requiredPackages = [
        'laravel/sanctum',
        'pusher/pusher-php-server',
    ];
    
    foreach ($requiredPackages as $package) {
        if (isset($composer['require'][$package])) {
            echo "  ✅ $package installed\n";
            $passed++;
        } else {
            echo "  ❌ $package not installed\n";
            $failed++;
        }
    }
} else {
    echo "  ❌ composer.json not found\n";
    $failed++;
}

echo "\n";

// Check 5: NPM dependencies
echo "📦 Checking NPM Dependencies...\n";

if (file_exists('package.json')) {
    $package = json_decode(file_get_contents('package.json'), true);
    
    // Check if node_modules exists
    if (is_dir('node_modules')) {
        if (is_dir('node_modules/laravel-echo') && is_dir('node_modules/pusher-js')) {
            echo "  ✅ laravel-echo installed\n";
            echo "  ✅ pusher-js installed\n";
            $passed += 2;
        } else {
            echo "  ❌ laravel-echo or pusher-js not installed\n";
            $failed++;
        }
    } else {
        echo "  ⚠️  node_modules not found (run npm install)\n";
        $failed++;
    }
} else {
    echo "  ❌ package.json not found\n";
    $failed++;
}

echo "\n";

// Check 6: Model updates
echo "🏗️  Checking Model Updates...\n";

$models = [
    'app/Models/Conversation.php' => ['contract_type', 'contract_id', 'STATUS_ACTIVE'],
    'app/Models/Message.php' => ['sender_type', 'isSystemMessage'],
    'app/Models/ProjectOffer.php' => ['service_status', 'conversation'],
    'app/Models/ServicePurchase.php' => ['service_status', 'conversation'],
    'app/Models/User.php' => ['rating_average', 'completion_rate'],
    'app/Models/Review.php' => ['rating', 'communication_rating'],
];

foreach ($models as $model => $requiredContent) {
    if (file_exists($model)) {
        $content = file_get_contents($model);
        $allFound = true;
        
        foreach ($requiredContent as $search) {
            if (strpos($content, $search) === false) {
                $allFound = false;
                break;
            }
        }
        
        if ($allFound) {
            echo "  ✅ " . basename($model) . " updated\n";
            $passed++;
        } else {
            echo "  ⚠️  " . basename($model) . " may be missing updates\n";
            $failed++;
        }
    } else {
        echo "  ❌ " . basename($model) . " not found\n";
        $failed++;
    }
}

echo "\n";

// Summary
echo str_repeat("=", 50) . "\n";
echo "📊 Verification Summary\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "Total Checks: " . ($passed + $failed) . "\n";

$percentage = ($passed / ($passed + $failed)) * 100;
echo "\nCompletion: " . number_format($percentage, 1) . "%\n";

if ($percentage >= 90) {
    echo "\n🎉 Implementation is complete and ready for testing!\n";
} elseif ($percentage >= 70) {
    echo "\n⚠️  Implementation is mostly complete but has some issues.\n";
} else {
    echo "\n❌ Implementation has significant issues that need to be addressed.\n";
}

echo "\n";

// Next steps
echo "📋 Next Steps:\n";
echo "  1. Start XAMPP MySQL service\n";
echo "  2. Run: php artisan migrate\n";
echo "  3. Configure Pusher credentials in .env\n";
echo "  4. Start queue worker: php artisan queue:work\n";
echo "  5. Run tests: php artisan test --filter=ServiceLifecycleTest\n";
echo "\n";
