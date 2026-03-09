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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            
            // The transaction belongs to EITHER a user (employee) or a company
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'company_id')->nullOnDelete();
            
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            
            // Polymorphic relation to whatever caused the transaction (like a ServicePurchase or a WithdrawalRequest)
            $table->nullableMorphs('reference');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
