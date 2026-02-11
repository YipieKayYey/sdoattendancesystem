<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $used = [];
        foreach (DB::table('seminars')->get() as $seminar) {
            if (strlen($seminar->slug) > 8) {
                do {
                    $slug = Str::random(8);
                } while (in_array($slug, $used) || DB::table('seminars')->where('slug', $slug)->exists());
                $used[] = $slug;
                DB::table('seminars')->where('id', $seminar->id)->update(['slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        // Cannot revert - old slugs are not stored
    }
};
