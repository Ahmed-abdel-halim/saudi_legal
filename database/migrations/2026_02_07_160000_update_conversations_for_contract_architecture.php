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
        // Update conversations table for contract-based architecture
        Schema::table('conversations', function (Blueprint $table) {
            // Add contract-based fields
            $table->enum('contract_type', ['offer', 'hourly_purchase'])->after('id');
            $table->unsignedBigInteger('contract_id')->after('contract_type');
            
            // Add enhanced status lifecycle
            $table->enum('status', ['pending', 'active', 'closed', 'archived'])
                  ->default('pending')
                  ->after('is_active');
            
            // Add read tracking timestamps (replaces is_read in messages)
            $table->timestamp('company_last_read_at')->nullable()->after('status');
            $table->timestamp('expert_last_read_at')->nullable()->after('company_last_read_at');
            
            // Add unique constraint to prevent duplicate conversations
            $table->unique(['contract_type', 'contract_id'], 'unique_contract_conversation');
            
            // Add indexes for performance
            $table->index(['participant_1', 'status']);
            $table->index(['participant_2', 'status']);
            $table->index(['contract_type', 'contract_id']);
        });
        
        // Update messages table
        Schema::table('messages', function (Blueprint $table) {
            // Add sender_type for system messages
            $table->enum('sender_type', ['company', 'expert', 'system'])
                  ->default('company')
                  ->after('sender_id');
            
            // Add index for efficient querying
            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('unique_contract_conversation');
            $table->dropIndex(['participant_1', 'status']);
            $table->dropIndex(['participant_2', 'status']);
            $table->dropIndex(['contract_type', 'contract_id']);
            
            $table->dropColumn([
                'contract_type',
                'contract_id',
                'status',
                'company_last_read_at',
                'expert_last_read_at',
            ]);
        });
        
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'created_at']);
            $table->dropColumn('sender_type');
        });
    }
};
