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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->enum('contract_type', ['offer', 'hourly_purchase']);
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('expert_id');
            
            // Multi-dimensional ratings (1-5)
            $table->tinyInteger('rating')->unsigned();
            $table->tinyInteger('communication_rating')->unsigned();
            $table->tinyInteger('quality_rating')->unsigned();
            $table->tinyInteger('delivery_time_rating')->unsigned();
            
            $table->text('comment')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            // Constraints
            $table->unique(['contract_type', 'contract_id'], 'unique_contract_review');
            $table->foreign('company_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('expert_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('expert_id');
            $table->index(['expert_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
