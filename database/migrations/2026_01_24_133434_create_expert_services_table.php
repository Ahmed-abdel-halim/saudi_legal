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
        Schema::create('expert_services', function (Blueprint $table) {
            $table->id('service_id');
            $table->unsignedBigInteger('expert_id');
            $table->string('title');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->integer('delivery_days');
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('expert_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_services');
    }
};
