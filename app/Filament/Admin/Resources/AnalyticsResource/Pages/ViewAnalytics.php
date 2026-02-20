<?php

namespace App\Filament\Admin\Resources\AnalyticsResource\Pages;

use App\Filament\Admin\Resources\AnalyticsResource;
use App\Services\SeminarAnalyticsService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnalytics extends ViewRecord
{
    protected static string $resource = AnalyticsResource::class;

    protected static string $view = 'filament.admin.resources.analytics-resource.pages.view-analytics';

    protected ?array $analyticsData = null;

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
        if ($this->analyticsData === null) {
            $this->analyticsData = app(SeminarAnalyticsService::class)
                ->getAnalyticsData($this->record, topSchoolsLimit: 5);
        }

        return $this->analyticsData;
    }
}
