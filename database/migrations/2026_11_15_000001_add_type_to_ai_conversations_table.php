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
        if (!Schema::hasColumn('ai_conversations', 'type')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                $table->string('type', 20)->default('legal')->index()->after('user_id');
            });
        }

        // Auto-classify existing conversations based on message citations or contents
        \App\Models\AiConversation::whereHas('messages', function ($q) {
            $q->where('citations', 'LIKE', '%quran%')
              ->orWhere('citations', 'LIKE', '%hadiths%')
              ->orWhere('message', 'LIKE', '%المفتي الإسلامي الذكي%');
        })->update(['type' => 'sharia']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ai_conversations', 'type')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
