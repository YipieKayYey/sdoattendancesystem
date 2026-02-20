<?php

namespace App\Services;

use App\Models\Seminar;

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

        $analytics = app(SeminarAnalyticsService::class)->getAnalyticsData($seminar, topSchoolsLimit: 10);

        $callback = function () use ($seminar, $analytics) {
            $file = fopen('php://output', 'w');

            $totalRegistrations = $analytics['total_registrations'];
            $totalCheckedIn = $analytics['total_checked_in'];
            $totalCheckedOut = $analytics['total_checked_out'];

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
            fputcsv($file, ['Total Registrations', $totalRegistrations, '100.0%']);
            fputcsv($file, ['Checked In', $totalCheckedIn,
                $totalRegistrations > 0 ? round(($totalCheckedIn / $totalRegistrations) * 100, 1) . '%' : '0%']);
            fputcsv($file, ['Checked Out', $totalCheckedOut,
                $totalCheckedIn > 0 ? round(($totalCheckedOut / $totalCheckedIn) * 100, 1) . '%' : '0%']);
            fputcsv($file, []);

            // Personnel Type Breakdown
            fputcsv($file, ['PERSONNEL TYPE BREAKDOWN']);
            fputcsv($file, ['Personnel Type', 'Count', 'Percentage']);
            foreach ($analytics['personnel_breakdown'] as $type => $count) {
                $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) . '%' : '0%';
                fputcsv($file, [ucfirst($type), $count, $percentage]);
            }
            fputcsv($file, []);

            // Gender Breakdown
            fputcsv($file, ['GENDER DISTRIBUTION']);
            fputcsv($file, ['Gender', 'Count', 'Percentage']);
            foreach ($analytics['gender_breakdown'] as $gender => $count) {
                $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) . '%' : '0%';
                fputcsv($file, [ucfirst($gender), $count, $percentage]);
            }
            fputcsv($file, []);

            // Top Schools (unified: schools.name, school_other, or school_office_agency)
            fputcsv($file, ['TOP PARTICIPATING SCHOOLS/OFFICES']);
            fputcsv($file, ['School/Office/Agency', 'Count', 'Percentage']);
            if (empty($analytics['top_schools'])) {
                fputcsv($file, ['No school/office data available', '-', '-']);
            }
            foreach ($analytics['top_schools'] as $school => $count) {
                $percentage = $totalRegistrations > 0 ? round(($count / $totalRegistrations) * 100, 1) . '%' : '0%';
                fputcsv($file, [$school, $count, $percentage]);
            }
            fputcsv($file, []);

            // Daily Attendance (Multi-Day)
            if ($analytics['is_multi_day'] && ! empty($analytics['daily_attendance'])) {
                fputcsv($file, ['DAILY ATTENDANCE']);
                fputcsv($file, ['Day', 'Date', 'Start Time', 'Venue', 'Checked In', 'Checked Out', 'Check-in Rate', 'Check-out Rate']);
                foreach ($analytics['daily_attendance'] as $day) {
                    $checkInRate = $totalRegistrations > 0 ? round(($day['checked_in'] / $totalRegistrations) * 100, 1) . '%' : '0%';
                    $checkOutRate = $day['checked_in'] > 0 ? round(($day['checked_out'] / $day['checked_in']) * 100, 1) . '%' : '0%';
                    fputcsv($file, [
                        'Day ' . $day['day'],
                        $day['date_long'] ?? $day['date'],
                        $day['start_time'] ?? 'N/A',
                        $day['venue'] ?? 'N/A',
                        $day['checked_in'],
                        $day['checked_out'],
                        $checkInRate,
                        $checkOutRate,
                    ]);
                }
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
