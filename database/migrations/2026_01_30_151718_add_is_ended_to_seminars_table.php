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
        Schema::table('seminars', function (Blueprint $table) {
            $table->boolean('is_ended')->default(false)->after('is_open');
        });
        
        // Ensure all existing seminars are set to active (not ended)
        DB::table('seminars')->update(['is_ended' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn('is_ended');
        });
    }
};
