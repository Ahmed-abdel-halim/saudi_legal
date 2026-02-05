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
        Schema::table('projects', function (Blueprint $table) {
            $table->text('scope_description')->nullable()->after('title');
            $table->integer('requested_duration_hours')->nullable()->after('scope_description');
            $table->decimal('max_hourly_rate', 10, 2)->nullable()->after('requested_duration_hours');
            // Modify status column to include 'posted' if possible, or we can just treat 'posted' as 'open' for now to avoid enum complexity. 
            // Ideally we modify the enum, but Doctrine DBAL is needed for changing columns.
            // As a workaround, we can add a new string status column or just ensure we use 'open' for posted if we can't change it easily without dbal.
            // But wait, the previous migration defined it as enum. altering enum depends on DB driver (MariaDB/MySQL).
            // Let's try raw statement for enum modification if needed, or just stick to 'open' mapping to 'posted' in logic?
            // User asked for "retrive it on the 'الطلبات' page", the query in RequestController looks for "status = 'posted'".
            // So we MUST support 'posted'.
        });
        
        // Raw statement to modify enum for MySQL/MariaDB
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('open', 'in_progress', 'completed', 'posted') DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['scope_description', 'requested_duration_hours', 'max_hourly_rate']);
        });
         DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('open', 'in_progress', 'completed') DEFAULT 'open'");
    }
};
