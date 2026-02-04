<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert seminar times from HH:MM:SS to HH:MM format
        DB::statement('UPDATE seminars SET time = SUBSTR(time, 1, 5) WHERE time IS NOT NULL AND LENGTH(time) > 5');
        
        // Convert seminar_day times from HH:MM:SS to HH:MM format  
        DB::statement('UPDATE seminar_days SET start_time = SUBSTR(start_time, 1, 5) WHERE start_time IS NOT NULL AND LENGTH(start_time) > 5');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to HH:MM:SS format
        DB::statement('UPDATE seminars SET time = CONCAT(time, ":00") WHERE time IS NOT NULL AND LENGTH(time) = 5');
        
        DB::statement('UPDATE seminar_days SET start_time = CONCAT(start_time, ":00") WHERE start_time IS NOT NULL AND LENGTH(start_time) = 5');
    }
};
