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
        Schema::table('ai_tasks_v2', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('payment_status')->default('unpaid')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_tasks_v2', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('payment_status');
        });
    }
};
