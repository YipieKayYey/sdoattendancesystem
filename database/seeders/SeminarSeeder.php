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
                'slug' => Str::random(8),
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
                'slug' => Str::random(8),
                'date' => now()->addDays(45),
                'capacity' => 100,
                'is_open' => false,
                'venue' => 'SDO Training Center',
                'topic' => 'Digital Learning Tools, Online Assessment Methods, and Technology Integration in Education',
                'time' => '09:00',
                'room' => 'Training Room 1',
            ],
            [
                'title' => 'Multi-Day Training: Advanced Teaching Methods',
                'slug' => Str::random(8),
                'date' => now()->addDays(60),
                'capacity' => 75,
                'is_open' => false,
                'is_multi_day' => true,
                'venue' => 'SDO Main Building',
                'topic' => 'Advanced Pedagogical Approaches and Modern Teaching Techniques',
                'room' => 'Main Hall',
            ],
            [
                'title' => 'Open Seminar: Educational Leadership and Management',
                'slug' => Str::random(8),
                'date' => now()->addDays(90),
                'capacity' => null,
                'is_open' => true,
                'venue' => 'SDO Multi-Purpose Hall',
                'topic' => 'School Leadership, Strategic Planning, and Educational Administration',
                'time' => '13:00',
                'room' => 'MPH Main',
            ],
        ];

        foreach ($seminars as $seminarData) {
            $seminar = Seminar::create($seminarData);
            
            // Create multi-day seminar days for the multi-day seminar
            if ($seminar->is_multi_day) {
                $seminar->days()->createMany([
                    [
                        'day_number' => 1,
                        'date' => $seminar->date,
                        'start_time' => '08:00',
                        'venue' => 'SDO Conference Hall A',
                        'topic' => 'Introduction to Advanced Teaching Methods',
                        'room' => 'Conference Hall A',
                    ],
                    [
                        'day_number' => 2,
                        'date' => $seminar->date->addDay(),
                        'start_time' => '08:30',
                        'venue' => 'SDO Training Center',
                        'topic' => 'Practical Applications and Case Studies',
                        'room' => 'Training Room 2',
                    ],
                    [
                        'day_number' => 3,
                        'date' => $seminar->date->addDays(2),
                        'start_time' => '09:00',
                        'venue' => 'SDO Multi-Purpose Hall',
                        'topic' => 'Assessment and Evaluation Techniques',
                        'room' => 'MPH Seminar Room',
                    ],
                ]);
            }
        }
    }
}
