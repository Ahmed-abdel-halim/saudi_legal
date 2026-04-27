<?php

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = trim($_ENV['GEMINI_API_KEY'] ?? '');

if (!$apiKey) {
    echo "API Key not found in .env\n";
    exit;
}

echo "Checking available models for key: " . substr($apiKey, 0, 5) . "...\n";

try {
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
        ->timeout(30)
        ->get("https://generativelanguage.googleapis.com/v1/models?key=" . $apiKey);

    if ($response->successful()) {
        $models = $response->json()['models'] ?? [];
        echo "Total models found: " . count($models) . "\n";
        foreach ($models as $model) {
            echo "- " . $model['name'] . " (" . $model['displayName'] . ")\n";
        }
    } else {
        echo "Error listing models: " . $response->status() . "\n";
        echo "Body: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
