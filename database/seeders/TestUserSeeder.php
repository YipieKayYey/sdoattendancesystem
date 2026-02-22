<?php

namespace Database\Seeders;

use App\Models\AttendeeProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Test Admin: admin@test.local / password
        User::firstOrCreate(
            ['email' => 'admin@test.local'],
            [
                'name' => 'Test Admin',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        // Test Attendee: attendee@test.local / password
        $attendee = User::firstOrCreate(
            ['email' => 'attendee@test.local'],
            [
                'name' => 'Test Attendee',
                'password' => 'password',
                'role' => 'attendee',
            ]
        );

        if ($attendee->attendeeProfile()->doesntExist()) {
            do {
                $hash = Str::random(16);
            } while (AttendeeProfile::where('universal_qr_hash', $hash)->exists());

            AttendeeProfile::create([
                'user_id' => $attendee->id,
                'universal_qr_hash' => $hash,
                'personnel_type' => 'teaching',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'position' => 'Teacher I',
            ]);
        }
    }
}
