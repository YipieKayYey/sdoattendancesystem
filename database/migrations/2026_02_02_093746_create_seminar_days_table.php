<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->onDelete('cascade');
            $table->integer('day_number')->default(1);
            $table->date('date');
            $table->string('start_time', 8)->nullable(); // HH:MM:SS format
            $table->string('venue')->nullable();
            $table->text('topic')->nullable();
            $table->string('room')->nullable();
            $table->timestamps();
            
            $table->index(['seminar_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_days');
    }
};
