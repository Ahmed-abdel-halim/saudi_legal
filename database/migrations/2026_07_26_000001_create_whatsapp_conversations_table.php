<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique(); // رقم الهاتف بصيغة: whatsapp:+966xxxxxxxxx
            $table->string('display_name')->nullable(); // اسم المستخدم في واتساب
            $table->enum('session_state', ['idle', 'in_chat'])->default('idle');
            $table->unsignedInteger('message_count')->default(0);
            $table->unsignedInteger('free_limit')->default(10);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
