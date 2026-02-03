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
        // First, convert existing TIME values to strings
        DB::statement('UPDATE seminars SET time = CAST(time AS CHAR) WHERE time IS NOT NULL');
        
        // Change column type from TIME to VARCHAR(8)
        Schema::table('seminars', function (Blueprint $table) {
            $table->string('time', 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->time('time')->nullable()->change();
        });
    }
};
