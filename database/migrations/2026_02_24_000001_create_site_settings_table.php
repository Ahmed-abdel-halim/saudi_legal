<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values
        $defaults = [
            ['key' => 'platform_fee_percent', 'value' => '15'],
            ['key' => 'default_currency',     'value' => 'SAR'],
            ['key' => 'support_email',        'value' => 'support@radiif.com'],
            ['key' => 'max_upload_size',      'value' => '10'],
            ['key' => 'maintenance_mode',     'value' => '0'],
        ];

        foreach ($defaults as $setting) {
            DB::table('site_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
