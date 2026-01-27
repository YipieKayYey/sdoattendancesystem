<?php

namespace App\Filament\Admin\Resources\ArchivedSeminarResource\Pages;

use App\Filament\Admin\Resources\ArchivedSeminarResource;
use App\Models\Seminar;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewArchivedSeminar extends ViewRecord
{
    protected static string $resource = ArchivedSeminarResource::class;

    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        return Seminar::withTrashed()->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('restore')
                ->label('Restore')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->restore();
                    Notification::make()
                        ->title('Seminar restored')
                        ->body('The seminar has been restored successfully.')
                        ->success()
                        ->send();
                    $this->redirect(ArchivedSeminarResource::getUrl('index'));
                }),
        ];
    }
}
