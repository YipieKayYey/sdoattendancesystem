<?php

namespace App\Services;

use App\Models\Seminar;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsPdfService
{
    public function generateAnalyticsReport(Seminar $seminar)
    {
        $analytics = app(SeminarAnalyticsService::class)->getAnalyticsData($seminar, topSchoolsLimit: 10);

        // Use date_long for PDF (full date format)
        $dailyAttendance = collect($analytics['daily_attendance'])->map(function ($day) {
            return array_merge($day, ['date' => $day['date_long'] ?? $day['date']]);
        })->all();

        $data = [
            'seminar' => $seminar,
            'total_registrations' => $analytics['total_registrations'],
            'total_checked_in' => $analytics['total_checked_in'],
            'total_checked_out' => $analytics['total_checked_out'],
            'check_in_rate' => $analytics['check_in_rate'],
            'check_out_rate' => $analytics['check_out_rate'],
            'personnel_breakdown' => $analytics['personnel_breakdown'],
            'gender_breakdown' => $analytics['gender_breakdown'],
            'top_schools' => $analytics['top_schools'],
            'daily_attendance' => $dailyAttendance,
            'is_multi_day' => $analytics['is_multi_day'],
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
