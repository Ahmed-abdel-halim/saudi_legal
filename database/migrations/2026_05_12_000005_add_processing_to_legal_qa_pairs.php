<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'Processing' to review_status to allow task locking for experts
        DB::statement("ALTER TABLE legal_qa_pairs MODIFY COLUMN review_status 
            ENUM('Pending', 'Processing', 'Approved', 'Rejected', 'Modified') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE legal_qa_pairs MODIFY COLUMN review_status 
            ENUM('Pending', 'Approved', 'Rejected', 'Modified') NOT NULL DEFAULT 'Pending'");
    }
};
