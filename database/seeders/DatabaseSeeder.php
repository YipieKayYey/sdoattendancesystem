<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed schools (alphabetical list)
        $this->call([
            SchoolSeeder::class,
        ]);

        // Seed seminars with multi-day support
        $this->call([
            SeminarSeeder::class,
        ]);
        
        // Seed attendees and admin users (original data)
        $this->call([
            SeminarDataSeeder::class,
        ]);
        
        // Seed additional attendees for all seminars (including multi-day)
        $this->call([
            AttendeeSeeder::class,
        ]);
        
        // Ensure all seminars have Day 1 records (needed for multi-day support)
        $this->call([
            EnsureSeminarDaysSeeder::class,
        ]);

        // Analytics test: multi-day seminar with attendees and check-ins
        $this->call([
            AnalyticsTestSeminarSeeder::class,
        ]);

        // Test users for login: admin@test.local, attendee@test.local (password: password)
        $this->call([
            TestUserSeeder::class,
        ]);
    }
}
