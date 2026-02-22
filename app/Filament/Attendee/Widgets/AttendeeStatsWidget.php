<?php

namespace App\Filament\Attendee\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendeeStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $count = auth()->user()->attendees()->count();

        return [
            Stat::make('Seminars Attended', $count)
                ->description('Total seminars you have participated in')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('success'),
        ];
    }
}
