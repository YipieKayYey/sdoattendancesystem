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
        Schema::table('attendees', function (Blueprint $table) {
            $table->enum('personnel_type', ['teaching', 'non_teaching'])->nullable()->after('email');
            $table->string('first_name')->nullable()->after('personnel_type');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('mobile_phone')->nullable()->after('last_name');
            $table->string('prc_license_no')->nullable()->after('mobile_phone');
            $table->date('prc_license_expiry')->nullable()->after('prc_license_no');
            $table->boolean('signature_consent')->default(false)->after('prc_license_expiry');
            $table->text('signature_image')->nullable()->after('signature_consent');
            $table->string('signature_upload_path')->nullable()->after('signature_image');
            $table->timestamp('signature_timestamp')->nullable()->after('signature_upload_path');
            $table->string('signature_hash')->nullable()->after('signature_timestamp');
            $table->json('signature_metadata')->nullable()->after('signature_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn([
                'personnel_type',
                'first_name',
                'middle_name',
                'last_name',
                'mobile_phone',
                'prc_license_no',
                'prc_license_expiry',
                'signature_consent',
                'signature_image',
                'signature_upload_path',
                'signature_timestamp',
                'signature_hash',
                'signature_metadata',
            ]);
        });
    }
};
