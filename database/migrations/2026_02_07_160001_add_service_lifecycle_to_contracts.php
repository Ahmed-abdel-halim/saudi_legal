<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add service lifecycle to project_offers table
        Schema::table('project_offers', function (Blueprint $table) {
            $table->enum('service_status', [
                'awaiting_start',
                'in_progress',
                'awaiting_confirmation',
                'completed',
                'cancelled',
                'disputed'
            ])->default('awaiting_start')->after('status');
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->index('service_status');
        });

        // Add service lifecycle to service_purchases table
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->enum('service_status', [
                'awaiting_start',
                'in_progress',
                'awaiting_confirmation',
                'completed',
                'cancelled',
                'disputed'
            ])->default('awaiting_start')->after('status');
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->index('service_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_offers', function (Blueprint $table) {
            $table->dropIndex(['service_status']);
            $table->dropColumn([
                'service_status',
                'started_at',
                'finished_at',
                'completed_at',
            ]);
        });

        Schema::table('service_purchases', function (Blueprint $table) {
            $table->dropIndex(['service_status']);
            $table->dropColumn([
                'service_status',
                'started_at',
                'finished_at',
                'completed_at',
            ]);
        });
    }
};
