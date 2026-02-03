<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendee_id')->constrained()->onDelete('cascade');
            $table->foreignId('seminar_day_id')->constrained()->onDelete('cascade');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
            
            $table->unique(['attendee_id', 'seminar_day_id']);
            $table->index('seminar_day_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_check_ins');
    }
};
