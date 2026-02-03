<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing seminars to seminar_days
        $seminars = DB::table('seminars')->get();
        
        foreach ($seminars as $seminar) {
            // Check if Day 1 already exists (idempotent - safe to run multiple times)
            $existingDay = DB::table('seminar_days')
                ->where('seminar_id', $seminar->id)
                ->where('day_number', 1)
                ->first();
            
            if ($existingDay) {
                $dayId = $existingDay->id;
            } else {
                // Create Day 1 for each existing seminar
                $dayId = DB::table('seminar_days')->insertGetId([
                    'seminar_id' => $seminar->id,
                    'day_number' => 1,
                    'date' => $seminar->date,
                    'start_time' => $seminar->time ?? null,
                    'venue' => $seminar->venue ?? null,
                    'topic' => $seminar->topic ?? null,
                    'room' => $seminar->room ?? null,
                    'created_at' => $seminar->created_at ?? now(),
                    'updated_at' => $seminar->updated_at ?? now(),
                ]);
            }
            
            // Migrate existing attendee check-ins (only if not already migrated)
            $attendees = DB::table('attendees')
                ->where('seminar_id', $seminar->id)
                ->where(function($query) {
                    $query->whereNotNull('checked_in_at')
                          ->orWhereNotNull('checked_out_at');
                })
                ->get();
            
            foreach ($attendees as $attendee) {
                // Check if check-in already exists for this attendee and day
                $existingCheckIn = DB::table('attendee_check_ins')
                    ->where('attendee_id', $attendee->id)
                    ->where('seminar_day_id', $dayId)
                    ->first();
                
                if (!$existingCheckIn) {
                    DB::table('attendee_check_ins')->insert([
                        'attendee_id' => $attendee->id,
                        'seminar_day_id' => $dayId,
                        'checked_in_at' => $attendee->checked_in_at,
                        'checked_out_at' => $attendee->checked_out_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Clear migrated data
        DB::table('attendee_check_ins')->truncate();
        DB::table('seminar_days')->truncate();
    }
};
