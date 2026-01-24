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
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('title');
            $table->enum('status', ['open', 'in_progress', 'completed'])->default('open');
            $table->decimal('budget', 15, 2)->nullable();
            
            $table->unsignedBigInteger('requester_company_id')->nullable();
            $table->unsignedBigInteger('supplier_company_id')->nullable();
            
            $table->timestamps();

            $table->foreign('requester_company_id')->references('company_id')->on('companies')->onDelete('set null');
            $table->foreign('supplier_company_id')->references('company_id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
