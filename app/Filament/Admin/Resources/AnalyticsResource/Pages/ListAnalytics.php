<?php

namespace App\Filament\Admin\Resources\AnalyticsResource\Pages;

use App\Filament\Admin\Resources\AnalyticsResource;
use Filament\Resources\Pages\ListRecords;

class ListAnalytics extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed for analytics
        ];
    }
}
