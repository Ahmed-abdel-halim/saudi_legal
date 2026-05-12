<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_citations', function (Blueprint $table) {
            // Drop index because system_name will become TEXT (cannot be part of standard index)
            $table->dropIndex(['legal_record_id', 'system_name']);
            
            // 1. system_name: VARCHAR(255) → TEXT
            $table->text('system_name')->nullable()->change();
        });

        // 2. citation_source enum: add 'religious'
        DB::statement("ALTER TABLE legal_citations MODIFY COLUMN citation_source 
            ENUM('law','contract','religious','other') NOT NULL DEFAULT 'law'");
    }

    public function down(): void
    {
        Schema::table('legal_citations', function (Blueprint $table) {
            $table->string('system_name', 255)->nullable()->change();
            $table->index(['legal_record_id', 'system_name']);
        });

        DB::statement("ALTER TABLE legal_citations MODIFY COLUMN citation_source 
            ENUM('law','contract','other') NOT NULL DEFAULT 'law'");
    }
};
