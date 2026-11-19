<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE whatsapp_conversations MODIFY COLUMN session_state VARCHAR(50) NOT NULL DEFAULT 'idle'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE whatsapp_conversations MODIFY COLUMN session_state ENUM('idle', 'in_chat') NOT NULL DEFAULT 'idle'");
    }
};
