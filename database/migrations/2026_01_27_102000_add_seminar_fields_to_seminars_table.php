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
        Schema::table('seminars', function (Blueprint $table) {
            $table->string('venue')->nullable()->after('date');
            $table->text('topic')->nullable()->after('venue');
            $table->time('time')->nullable()->after('topic');
            $table->string('room')->nullable()->after('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn(['venue', 'topic', 'time', 'room']);
        });
    }
};
