<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add resolution tracking to project_offers
        Schema::table('project_offers', function (Blueprint $table) {
            $table->text('resolution_note')->nullable()->after('service_status');
            $table->unsignedBigInteger('resolved_by')->nullable()->after('resolution_note');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });

        // Add resolution tracking to service_purchases
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->text('resolution_note')->nullable()->after('service_status');
            $table->unsignedBigInteger('resolved_by')->nullable()->after('resolution_note');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('project_offers', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['resolution_note', 'resolved_by', 'resolved_at']);
        });

        Schema::table('service_purchases', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['resolution_note', 'resolved_by', 'resolved_at']);
        });
    }
};
