<?php

namespace Database\Seeders;

use App\Models\Seminar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeminarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample seminars with complete data
        $seminars = [
            [
                'title' => 'Professional Development Workshop for Teachers 2024',
                'slug' => 'professional-development-workshop-2024',
                'date' => now()->addDays(30),
                'capacity' => 50,
                'is_open' => false,
                'venue' => 'SDO Conference Hall, Main Building',
                'topic' => 'Effective Teaching Strategies, Classroom Management, and Student Engagement Techniques',
                'time' => '08:00',
                'room' => 'Conference Hall A',
            ],
            [
                'title' => 'CPD Program: Modern Educational Technologies',
                'slug' => 'cpd-modern-educational-technologies',
                'date' => now()->addDays(45),
                'capacity' => 100,
                'is_open' => false,
                'venue' => 'SDO Training Center',
                'topic' => 'Digital Learning Tools, Online Assessment Methods, and Technology Integration in Education',
                'time' => '09:00',
                'room' => 'Training Room 1',
            ],
            [
                'title' => 'Open Seminar: Educational Leadership and Management',
                'slug' => 'open-seminar-educational-leadership',
                'date' => now()->addDays(60),
                'capacity' => null,
                'is_open' => true,
                'venue' => 'SDO Multi-Purpose Hall',
                'topic' => 'School Leadership, Strategic Planning, and Educational Administration',
                'time' => '13:00',
                'room' => 'MPH Main',
            ],
        ];

        foreach ($seminars as $seminarData) {
            Seminar::create($seminarData);
        }
    }
}
