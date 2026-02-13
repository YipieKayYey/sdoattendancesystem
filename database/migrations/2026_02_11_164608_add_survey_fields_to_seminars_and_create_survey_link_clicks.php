<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->text('survey_form_url')->nullable()->after('room');
        });

        Schema::create('survey_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->cascadeOnDelete();
            $table->timestamp('clicked_at');
            $table->index(['seminar_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_link_clicks');
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn('survey_form_url');
        });
    }
};
