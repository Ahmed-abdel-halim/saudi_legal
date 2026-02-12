<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ExpertService;
use App\Models\ServicePurchase;
use Illuminate\Support\Facades\Hash;

class ChatTestSeeder extends Seeder
{
    public function run()
    {
        // 1. Get an Expert (The one logged in hopefully, or just the first one)
        // We'll target the one with ID 1 or the first expert
        $expert = User::where('role', 'expert')->first();

        if (!$expert) {
            $this->command->error("No expert found! Please create an expert first.");
            return;
        }

        // 2. Create a generic Client
        $client = User::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'name' => 'Test Client',
                'password' => Hash::make('password'),
                'role' => 'client',
                'avatar_path' => null // Will use UI Avatar
            ]
        );

        // 3. Create a Service for this Expert (if none)
        $service = ExpertService::where('expert_id', $expert->id)->first();
        if (!$service) {
            $service = ExpertService::create([
                'expert_id' => $expert->id,
                'title' => 'Consultation Service',
                'category' => 'consulting',
                'price' => 50,
                'delivery_days' => 2,
                'description' => 'Expert consultation for your needs.'
            ]);
        }

        // 4. Create a Pending Service Purchase
        // Check if one exists first to avoid dupes
        $existingPurchase = ServicePurchase::where('client_id', $client->id)
            ->where('expert_id', $expert->id)
            ->where('status', 'pending')
            ->first();

        if (!$existingPurchase) {
            ServicePurchase::create([
                'expert_id' => $expert->id,
                'client_id' => $client->id,
                'service_id' => $service->id,
                'hours_purchased' => 5,
                'hourly_rate' => 50,
                'total_price' => 250,
                'status' => 'pending'
            ]);
            $this->command->info("✅ Created pending request from Client {$client->name} to Expert {$expert->name}");
        } else {
            $this->command->info("ℹ️ Pending request already exists.");
        }
    }
}
