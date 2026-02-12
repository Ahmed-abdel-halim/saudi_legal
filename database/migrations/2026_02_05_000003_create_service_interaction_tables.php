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
        // 1. Project Offers Table (Workflow 1)
        Schema::create('project_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('expert_id'); // User ID of the expert
            $table->decimal('price', 15, 2);
            $table->integer('delivery_time_days');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('expert_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Service Purchases Table (Workflow 2)
        Schema::create('service_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expert_id'); // User ID of the expert
            $table->unsignedBigInteger('client_id'); // User ID of the buyer (company rep)
            $table->unsignedBigInteger('service_id')->nullable(); // Optional link to specific service
            $table->integer('hours_purchased');
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('total_price', 15, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('expert_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('expert_services')->onDelete('set null');
        });

        // 3. Conversations Table (Chat)
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'project_offer' or 'service_purchase'
            $table->unsignedBigInteger('reference_id'); // ID of the offer or purchase
            $table->unsignedBigInteger('participant_1'); // User ID 1 (e.g., Client)
            $table->unsignedBigInteger('participant_2'); // User ID 2 (e.g., Expert)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('participant_1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('participant_2')->references('id')->on('users')->onDelete('cascade');
            
            // Index for faster lookups
            $table->index(['participant_1', 'participant_2']);
        });

        // 4. Messages Table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('service_purchases');
        Schema::dropIfExists('project_offers');
    }
};
