<?php

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GEMINI_API_KEY'] ?? null;

if (!$apiKey) {
    echo "API Key not found in .env\n";
    exit;
}

echo "Testing with API Key: " . substr($apiKey, 0, 5) . "...\n";

$prompt = "Hello, respond with 'Success' if you can read this.";

try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey, [
        'contents' => [['parts' => [['text' => $prompt]]]]
    ]);

    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
