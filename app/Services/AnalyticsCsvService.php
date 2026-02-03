<?php

namespace App\Services;

use App\Models\Seminar;
use App\Models\Attendee;
use App\Models\AttendeeCheckIn;

class AnalyticsCsvService
{
    public function generateAnalyticsCsv(Seminar $seminar)
    {
        $filename = 'analytics-data-' . $seminar->slug . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
        
        $callback = function() use ($seminar) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Seminar Information Header
            fputcsv($file, ['SEMINAR ANALYTICS REPORT']);
            fputcsv($file, ['Seminar Title:', $seminar->title]);
            fputcsv($file, ['Date:', $seminar->date->format('F j, Y')]);
            fputcsv($file, ['Generated:', now()->format('F j, Y g:i A')]);
            fputcsv($file, []);
            
            // Summary Statistics
            fputcsv($file, ['SUMMARY STATISTICS']);
            fputcsv($file, ['Metric', 'Count', 'Percentage']);
            
            $totalRegistrations = $seminar->attendees()->count();
            $totalCheckedIn = $seminar->attendees()->whereNotNull('checked_in_at')->count();
            $totalCheckedOut = $seminar->attendees()->whereNotNull('checked_out_at')->count();
            
            fputcsv($file, ['Total Registrations', $totalRegistrations, '100.0%']);
            fputcsv($file, ['Checked In', $totalCheckedIn, 
                $totalRegistrations > 0 ? round(($totalCheckedIn / $totalRegistrations) * 100, 1) . '%' : '0%']);
            fputcsv($file, ['Checked Out', $totalCheckedOut, 
                $totalCheckedIn > 0 ? round(($totalCheckedOut / $totalCheckedIn) * 100, 1) . '%' : '0%']);
            fputcsv($file, []);
            
            // Personnel Type Breakdown
            fputcsv($file, ['PERSONNEL TYPE BREAKDOWN']);
            fputcsv($file, ['Personnel Type', 'Count', 'Percentage']);
            
            $personnelBreakdown = $seminar->attendees()
                ->selectRaw('personnel_type, COUNT(*) as count')
                ->groupBy('personnel_type')
                ->pluck('count', 'personnel_type')
                ->toArray();
            
            foreach ($personnelBreakdown as $type => $count) {
                $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) . '%' : '0%';
                fputcsv($file, [ucfirst($type), $count, $percentage]);
            }
            fputcsv($file, []);
            
            // Gender Breakdown
            fputcsv($file, ['GENDER DISTRIBUTION']);
            fputcsv($file, ['Gender', 'Count', 'Percentage']);
            
            $genderBreakdown = $seminar->attendees()
                ->selectRaw('sex, COUNT(*) as count')
                ->whereNotNull('sex')
                ->groupBy('sex')
                ->pluck('count', 'sex')
                ->toArray();
            
            foreach ($genderBreakdown as $gender => $count) {
                $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) . '%' : '0%';
                fputcsv($file, [ucfirst($gender), $count, $percentage]);
            }
            fputcsv($file, []);
            
            // Top Schools
            fputcsv($file, ['TOP PARTICIPATING SCHOOLS/OFFICES']);
            fputcsv($file, ['School/Office/Agency', 'Count', 'Percentage']);
            
            $topSchools = $seminar->attendees()
                ->selectRaw('school_office_agency, COUNT(*) as count')
                ->whereNotNull('school_office_agency')
                ->groupBy('school_office_agency')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'school_office_agency')
                ->toArray();
            
            foreach ($topSchools as $school => $count) {
                $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) . '%' : '0%';
                fputcsv($file, [$school, $count, $percentage]);
            }
            fputcsv($file, []);
            
            // Daily Attendance (Multi-Day)
            if ($seminar->isMultiDay()) {
                fputcsv($file, ['DAILY ATTENDANCE']);
                fputcsv($file, ['Day', 'Date', 'Start Time', 'Venue', 'Checked In', 'Checked Out', 'Check-in Rate', 'Check-out Rate']);
                
                foreach ($seminar->days as $day) {
                    $checkedInCount = AttendeeCheckIn::where('seminar_day_id', $day->id)
                        ->whereNotNull('checked_in_at')
                        ->count();
                    $checkedOutCount = AttendeeCheckIn::where('seminar_day_id', $day->id)
                        ->whereNotNull('checked_out_at')
                        ->count();
                    
                    $checkInRate = $totalRegistrations > 0 ? round(($checkedInCount / $totalRegistrations) * 100, 1) . '%' : '0%';
                    $checkOutRate = $checkedInCount > 0 ? round(($checkedOutCount / $checkedInCount) * 100, 1) . '%' : '0%';
                    
                    fputcsv($file, [
                        'Day ' . $day->day_number,
                        $day->date->format('F j, Y'),
                        $day->start_time ?? 'N/A',
                        $day->venue ?? 'N/A',
                        $checkedInCount,
                        $checkedOutCount,
                        $checkInRate,
                        $checkOutRate
                    ]);
                }
                fputcsv($file, []);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
