<?php

namespace App\Services;

use App\Models\AttendeeCheckIn;
use App\Models\Seminar;

class SeminarAnalyticsService
{
    /**
     * Get comprehensive analytics data for a seminar.
     * Uses attendee_check_ins for multi-day seminars; attendee-level fields for single-day.
     *
     * @param  int  $topSchoolsLimit  Max number of top schools to return (default 10)
     */
    public function getAnalyticsData(Seminar $seminar, int $topSchoolsLimit = 10): array
    {
        $totalRegistrations = $seminar->attendees()->count();

        [$totalCheckedIn, $totalCheckedOut] = $this->getCheckInCounts($seminar);

        $personnelBreakdown = $seminar->attendees()
            ->selectRaw('personnel_type, COUNT(*) as count')
            ->groupBy('personnel_type')
            ->pluck('count', 'personnel_type')
            ->toArray();

        $genderBreakdown = $seminar->attendees()
            ->selectRaw('sex, COUNT(*) as count')
            ->whereNotNull('sex')
            ->groupBy('sex')
            ->pluck('count', 'sex')
            ->toArray();

        $topSchools = $this->getTopSchools($seminar, $topSchoolsLimit);

        $dailyAttendance = $this->getDailyAttendance($seminar);

        return [
            'total_registrations' => $totalRegistrations,
            'total_checked_in' => $totalCheckedIn,
            'total_checked_out' => $totalCheckedOut,
            'check_in_rate' => $totalRegistrations > 0 ? round(($totalCheckedIn / $totalRegistrations) * 100, 1) : 0,
            'check_out_rate' => $totalCheckedIn > 0 ? round(($totalCheckedOut / $totalCheckedIn) * 100, 1) : 0,
            'personnel_breakdown' => $personnelBreakdown,
            'gender_breakdown' => $genderBreakdown,
            'top_schools' => $topSchools,
            'daily_attendance' => $dailyAttendance,
            'is_multi_day' => $seminar->isMultiDay(),
        ];
    }

    /**
     * Get only checked-in and checked-out counts (lightweight for list views).
     */
    public function getCheckInCountsOnly(Seminar $seminar): array
    {
        return $this->getCheckInCounts($seminar);
    }

    /**
     * Get total checked-in and checked-out counts.
     * For multi-day: unique attendees from attendee_check_ins.
     * For single-day: attendee-level checked_in_at/checked_out_at (legacy).
     */
    protected function getCheckInCounts(Seminar $seminar): array
    {
        if ($seminar->isMultiDay()) {
            $dayIds = $seminar->days()->pluck('id');

            $checkedInCount = (int) AttendeeCheckIn::whereIn('seminar_day_id', $dayIds)
                ->whereNotNull('checked_in_at')
                ->selectRaw('COUNT(DISTINCT attendee_id) as cnt')
                ->value('cnt');

            $checkedOutCount = (int) AttendeeCheckIn::whereIn('seminar_day_id', $dayIds)
                ->whereNotNull('checked_out_at')
                ->selectRaw('COUNT(DISTINCT attendee_id) as cnt')
                ->value('cnt');

            return [$checkedInCount, $checkedOutCount];
        }

        $totalCheckedIn = $seminar->attendees()->whereNotNull('checked_in_at')->count();
        $totalCheckedOut = $seminar->attendees()->whereNotNull('checked_out_at')->count();

        return [$totalCheckedIn, $totalCheckedOut];
    }

    /**
     * Get top schools using unified display: school name from relationship,
     * school_other, or legacy school_office_agency.
     */
    protected function getTopSchools(Seminar $seminar, int $limit = 10): array
    {
        $schoolDisplay = 'COALESCE(schools.name, attendees.school_other, attendees.school_office_agency)';

        return $seminar->attendees()
            ->leftJoin('schools', 'attendees.school_id', '=', 'schools.id')
            ->whereRaw("TRIM({$schoolDisplay}) != ''")
            ->selectRaw("{$schoolDisplay} as school_display, COUNT(*) as count")
            ->groupBy('school_display')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'school_display')
            ->toArray();
    }

    /**
     * Get daily attendance for multi-day seminars.
     */
    protected function getDailyAttendance(Seminar $seminar): array
    {
        $dailyAttendance = [];

        if (! $seminar->isMultiDay()) {
            return $dailyAttendance;
        }

        foreach ($seminar->days as $day) {
            $checkedInCount = AttendeeCheckIn::where('seminar_day_id', $day->id)
                ->whereNotNull('checked_in_at')
                ->count();
            $checkedOutCount = AttendeeCheckIn::where('seminar_day_id', $day->id)
                ->whereNotNull('checked_out_at')
                ->count();

            $dailyAttendance[] = [
                'day' => $day->day_number,
                'date' => $day->date->format('M j, Y'),
                'date_long' => $day->date->format('F j, Y'),
                'start_time' => $day->start_time,
                'venue' => $day->venue,
                'checked_in' => $checkedInCount,
                'checked_out' => $checkedOutCount,
            ];
        }

        return $dailyAttendance;
    }
}
