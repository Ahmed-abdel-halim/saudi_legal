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
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id('freelancer_profile_id'); // Keeping explicit naming style
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('freelancer_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freelancer_profile_id');
            $table->unsignedBigInteger('skill_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('freelancer_profile_id')->references('freelancer_profile_id')->on('freelancer_profiles')->onDelete('cascade');
            // Assuming skills table has primary key 'skill_id' based on previous check
             $table->foreign('skill_id')->references('skill_id')->on('skills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freelancer_skills');
        Schema::dropIfExists('freelancer_profiles');
    }
};
