<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Seminar;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeSeminars = Seminar::withoutTrashed()->count();
        $archivedSeminars = Seminar::onlyTrashed()->count();

        return [
            Stat::make('Active Seminars', $activeSeminars)
                ->description('Currently active seminars')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 4]),
            Stat::make('Archived Seminars', $archivedSeminars)
                ->description('Archived seminars')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('warning')
                ->chart([3, 2, 1, 2, 3, 2, 1]),
        ];
    }
}
