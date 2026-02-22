<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('universal_qr_hash', 16)->unique();
            $table->enum('personnel_type', ['teaching', 'non_teaching'])->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('school_other')->nullable();
            $table->string('school_office_agency')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('position')->nullable();
            $table->string('prc_license_no')->nullable();
            $table->date('prc_license_expiry')->nullable();
            $table->boolean('signature_consent')->default(false);
            $table->text('signature_image')->nullable();
            $table->string('signature_upload_path')->nullable();
            $table->timestamp('signature_timestamp')->nullable();
            $table->string('signature_hash')->nullable();
            $table->json('signature_metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_profiles');
    }
};
