<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureSeminarDaysSeeder extends Seeder
{
    /**
     * Ensure all seminars have at least Day 1 record.
     * This is needed because seeders run after migrations,
     * so seminars created by seeders won't have Day 1 records.
     */
    public function run(): void
    {
        // Check if table exists (defensive check)
        if (!Schema::hasTable('seminar_days')) {
            $this->command->warn('seminar_days table does not exist. Skipping seeder.');
            return;
        }
        
        // Use raw queries to avoid relationship issues
        $seminars = DB::table('seminars')->get();
        
        foreach ($seminars as $seminar) {
            // Check if Day 1 already exists for this seminar
            $existingDay = DB::table('seminar_days')
                ->where('seminar_id', $seminar->id)
                ->where('day_number', 1)
                ->first();
            
            if (!$existingDay) {
                // Create Day 1 for this seminar
                DB::table('seminar_days')->insert([
                    'seminar_id' => $seminar->id,
                    'day_number' => 1,
                    'date' => $seminar->date,
                    'start_time' => $seminar->time,
                    'venue' => $seminar->venue,
                    'topic' => $seminar->topic,
                    'room' => $seminar->room,
                    'created_at' => $seminar->created_at ?? now(),
                    'updated_at' => $seminar->updated_at ?? now(),
                ]);
                
                $this->command->info("Created Day 1 for seminar: {$seminar->title}");
            }
        }
    }
}
