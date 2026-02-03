<?php

namespace App\Filament\Admin\Resources\AnalyticsResource\Pages;

use App\Filament\Admin\Resources\AnalyticsResource;
use App\Models\Seminar;
use App\Models\Attendee;
use App\Models\AttendeeCheckIn;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnalytics extends ViewRecord
{
    protected static string $resource = AnalyticsResource::class;

    protected static string $view = 'filament.admin.resources.analytics-resource.pages.view-analytics';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export Analytics PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('analytics.export-pdf', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('export_csv')
                ->label('Export Analytics CSV')
                ->icon('heroicon-o-table-cells')
                ->url(fn () => route('analytics.export-csv', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function getAnalyticsData(): array
    {
        $seminar = $this->record;
        
        // Basic stats
        $totalRegistrations = $seminar->attendees()->count();
        $totalCheckedIn = $seminar->attendees()->whereNotNull('checked_in_at')->count();
        $totalCheckedOut = $seminar->attendees()->whereNotNull('checked_out_at')->count();
        
        // Personnel type breakdown
        $personnelBreakdown = $seminar->attendees()
            ->selectRaw('personnel_type, COUNT(*) as count')
            ->groupBy('personnel_type')
            ->pluck('count', 'personnel_type')
            ->toArray();
        
        // Gender breakdown
        $genderBreakdown = $seminar->attendees()
            ->selectRaw('sex, COUNT(*) as count')
            ->whereNotNull('sex')
            ->groupBy('sex')
            ->pluck('count', 'sex')
            ->toArray();
        
        // Top schools
        $topSchools = $seminar->attendees()
            ->selectRaw('school_office_agency, COUNT(*) as count')
            ->whereNotNull('school_office_agency')
            ->groupBy('school_office_agency')
            ->orderByDesc('count')
            ->limit(5)
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
                    'date' => $day->date->format('M j, Y'),
                    'checked_in' => $checkedInCount,
                    'checked_out' => $checkedOutCount,
                ];
            }
        }
        
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
}
