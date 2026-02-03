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
        // Seed seminars and attendees
        $this->call([
            SeminarDataSeeder::class,
        ]);
        
        // Ensure all seminars have Day 1 records (needed for multi-day support)
        $this->call([
            EnsureSeminarDaysSeeder::class,
        ]);

        // If you want to also seed default admin users (in case they don't exist in SQL dump),
        // uncomment the following:
        /*
        User::updateOrCreate(
            ['email' => 'sdoadmin@example.com'],
            [
                'name' => 'SDO Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'sdoadmin2@example.com'],
            [
                'name' => 'SDO Admin 2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        */
    }
}
