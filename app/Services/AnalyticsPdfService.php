<?php

namespace App\Services;

use App\Models\Seminar;
use App\Models\Attendee;
use App\Models\AttendeeCheckIn;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AnalyticsPdfService
{
    public function generateAnalyticsReport(Seminar $seminar)
    {
        // Get analytics data
        $totalRegistrations = $seminar->attendees()->count();
        $totalCheckedIn = $seminar->attendees()->whereNotNull('checked_in_at')->count();
        $totalCheckedOut = $seminar->attendees()->whereNotNull('checked_out_at')->count();
        
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
        
        $topSchools = $seminar->attendees()
            ->selectRaw('school_office_agency, COUNT(*) as count')
            ->whereNotNull('school_office_agency')
            ->groupBy('school_office_agency')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'school_office_agency')
            ->toArray();
        
        // Daily attendance for multi-day seminars
        $dailyAttendance = [];
        if ($seminar->isMultiDay()) {
            foreach ($seminar->days as $day) {
                $checkedInCount = AttendeeCheckIn::where('seminar_day_id', $day->id)
                    ->whereNotNull('checked_in_at')
                    ->count();
                $checkedOutCount = AttendeeCheckIn::where('seminar_day_id', $day->id)
                    ->whereNotNull('checked_out_at')
                    ->count();
                
                $dailyAttendance[] = [
                    'day' => $day->day_number,
                    'date' => $day->date->format('F j, Y'),
                    'start_time' => $day->start_time,
                    'venue' => $day->venue,
                    'checked_in' => $checkedInCount,
                    'checked_out' => $checkedOutCount,
                ];
            }
        }
        
        $data = [
            'seminar' => $seminar,
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
            'generated_at' => now()->format('F j, Y g:i A'),
        ];
        
        $pdf = Pdf::loadView('pdf.analytics-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => false,
                'defaultFont' => 'sans-serif',
                'margin_top' => 20,
                'margin_bottom' => 20,
                'margin_left' => 15,
                'margin_right' => 15,
                'enable_php' => true,
            ]);
        
        return $pdf->stream('analytics-report-' . $seminar->slug . '-' . now()->format('Y-m-d') . '.pdf');
    }
}
