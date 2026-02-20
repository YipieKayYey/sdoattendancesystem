<?php

namespace Database\Seeders;

use App\Models\Attendee;
use App\Models\AttendeeCheckIn;
use App\Models\School;
use App\Models\Seminar;
use App\Models\SeminarDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnalyticsTestSeminarSeeder extends Seeder
{
    /**
     * Creates a multi-day seminar with attendees and check-ins for testing analytics.
     */
    public function run(): void
    {
        $schools = School::pluck('id', 'name')->toArray();
        $schoolNames = array_keys($schools);

        if (empty($schoolNames)) {
            $this->command->warn('No schools found. Run SchoolSeeder first.');
            return;
        }

        // Create multi-day seminar
        $seminar = Seminar::create([
            'title' => 'Analytics Test: Multi-Day Professional Development',
            'slug' => 'analytics-test-' . Str::random(6),
            'date' => now()->addDays(7),
            'capacity' => 50,
            'is_open' => false,
            'is_multi_day' => true,
            'venue' => 'SDO Conference Hall',
            'topic' => 'Testing analytics with schools, personnel types, and daily attendance',
            'room' => 'Hall A',
        ]);

        // Create 3 seminar days
        $days = [];
        for ($i = 1; $i <= 3; $i++) {
            $days[] = $seminar->days()->create([
                'day_number' => $i,
                'date' => $seminar->date->copy()->addDays($i - 1),
                'start_time' => $i === 1 ? '08:00' : '09:00',
                'venue' => ['Conference Hall A', 'Training Room 1', 'Multi-Purpose Hall'][$i - 1],
                'topic' => "Day {$i} Topic",
                'room' => ['Hall A', 'Room 1', 'MPH'][$i - 1],
            ]);
        }

        // Attendee data: varied schools, personnel types, sex
        $attendeesData = [
            ['first_name' => 'Rosa', 'middle_name' => 'Mendoza', 'last_name' => 'Diaz', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 0],
            ['first_name' => 'Pedro', 'middle_name' => 'Santos', 'last_name' => 'Lopez', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 1],
            ['first_name' => 'Carmen', 'middle_name' => 'Reyes', 'last_name' => 'Villanueva', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 2],
            ['first_name' => 'Ramon', 'middle_name' => 'Garcia', 'last_name' => 'Torres', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 0],
            ['first_name' => 'Elena', 'middle_name' => 'Cruz', 'last_name' => 'Ramos', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 1],
            ['first_name' => 'Antonio', 'middle_name' => 'Bautista', 'last_name' => 'Sanchez', 'sex' => 'male', 'personnel_type' => 'non_teaching', 'school_index' => 2],
            ['first_name' => 'Teresa', 'middle_name' => 'Alvarez', 'last_name' => 'Rivera', 'sex' => 'female', 'personnel_type' => 'non_teaching', 'school_index' => 0],
            ['first_name' => 'Felipe', 'middle_name' => 'Martinez', 'last_name' => 'Gonzales', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 3],
            ['first_name' => 'Lucia', 'middle_name' => 'Fernandez', 'last_name' => 'Mendoza', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 1],
            ['first_name' => 'Ricardo', 'middle_name' => 'Dela', 'last_name' => 'Cruz', 'sex' => 'male', 'personnel_type' => 'non_teaching', 'school_index' => 4],
            ['first_name' => 'Sofia', 'middle_name' => 'Ocampo', 'last_name' => 'Herrera', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 0],
            ['first_name' => 'Miguel', 'middle_name' => 'Castillo', 'last_name' => 'Aquino', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 2],
            ['first_name' => 'Rosa', 'middle_name' => 'Perez', 'last_name' => 'Santiago', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 1],
            ['first_name' => 'Jose', 'middle_name' => 'Ramos', 'last_name' => 'Ortiz', 'sex' => 'male', 'personnel_type' => 'non_teaching', 'school_index' => 0],
            ['first_name' => 'Maria', 'middle_name' => 'Flores', 'last_name' => 'Vargas', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 3],
            ['first_name' => 'Carlos', 'middle_name' => 'Gomez', 'last_name' => 'Serrano', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 4],
            ['first_name' => 'Ana', 'middle_name' => 'Moreno', 'last_name' => 'Jimenez', 'sex' => 'female', 'personnel_type' => 'non_teaching', 'school_index' => 2],
            ['first_name' => 'Fernando', 'middle_name' => 'Ruiz', 'last_name' => 'Navarro', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 1],
            ['first_name' => 'Luz', 'middle_name' => 'Herrera', 'last_name' => 'Molina', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_index' => 0],
            ['first_name' => 'Roberto', 'middle_name' => 'Vega', 'last_name' => 'Romero', 'sex' => 'male', 'personnel_type' => 'teaching', 'school_index' => 5],
            ['first_name' => 'Pilar', 'middle_name' => 'Diaz', 'last_name' => 'Espinoza', 'sex' => 'female', 'personnel_type' => 'teaching', 'school_other' => 'Other School - Test'],
        ];

        foreach ($attendeesData as $i => $data) {
            $idx = isset($data['school_other']) ? 0 : ($data['school_index'] % count($schoolNames));
            $schoolId = isset($data['school_other']) ? null : ($schools[$schoolNames[$idx]] ?? null);
            $schoolOther = $data['school_other'] ?? null;

            $attrs = [
                'seminar_id' => $seminar->id,
                'name' => trim("{$data['first_name']} {$data['middle_name']} {$data['last_name']}"),
                'email' => strtolower($data['first_name'] . '.' . $data['last_name'] . $i . '@test.deped.gov.ph'),
                'position' => $data['personnel_type'] === 'teaching' ? 'Teacher III' : 'Admin Officer',
                'ticket_hash' => Str::random(16),
                'personnel_type' => $data['personnel_type'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'sex' => $data['sex'],
                'school_id' => $schoolId,
                'school_other' => $schoolOther,
                'mobile_phone' => '09' . str_pad((string) ($i + 17), 9, '0'),
                'signature_consent' => true,
            ];
            if ($schoolOther) {
                $attrs['school_office_agency'] = $schoolOther;
            }
            $attendee = Attendee::create($attrs);

            // Assign check-in behavior: some attend day 1 only, some 1+2, some all 3; some checkout
            $behavior = $i % 5; // 0-4 patterns
            $baseTime = now()->subDays(3);

            if ($behavior === 0) {
                // Day 1 only, checked in & out
                $this->createCheckIn($attendee, $days[0], $baseTime->copy()->addHours(8), $baseTime->copy()->addHours(16));
            } elseif ($behavior === 1) {
                // Day 1 + 2, checked in & out both
                $this->createCheckIn($attendee, $days[0], $baseTime->copy()->addHours(8), $baseTime->copy()->addHours(16));
                $this->createCheckIn($attendee, $days[1], $baseTime->copy()->addDay()->addHours(9), $baseTime->copy()->addDay()->addHours(17));
            } elseif ($behavior === 2) {
                // All 3 days, full check-in/out
                $this->createCheckIn($attendee, $days[0], $baseTime->copy()->addHours(8), $baseTime->copy()->addHours(16));
                $this->createCheckIn($attendee, $days[1], $baseTime->copy()->addDay()->addHours(9), $baseTime->copy()->addDay()->addHours(17));
                $this->createCheckIn($attendee, $days[2], $baseTime->copy()->addDays(2)->addHours(9), $baseTime->copy()->addDays(2)->addHours(17));
            } elseif ($behavior === 3) {
                // Day 1 only, checked in but NOT out
                $this->createCheckIn($attendee, $days[0], $baseTime->copy()->addHours(8), null);
            } else {
                // Day 2 + 3, with checkouts
                $this->createCheckIn($attendee, $days[1], $baseTime->copy()->addDay()->addHours(9), $baseTime->copy()->addDay()->addHours(17));
                $this->createCheckIn($attendee, $days[2], $baseTime->copy()->addDays(2)->addHours(9), null);
            }
        }

        $this->command->info("Created analytics test seminar: '{$seminar->title}' with " . count($attendeesData) . ' attendees and multi-day check-ins.');
    }

    protected function createCheckIn(Attendee $attendee, SeminarDay $day, $checkedInAt, $checkedOutAt): void
    {
        AttendeeCheckIn::create([
            'attendee_id' => $attendee->id,
            'seminar_day_id' => $day->id,
            'checked_in_at' => $checkedInAt,
            'checked_out_at' => $checkedOutAt,
        ]);
    }
}
