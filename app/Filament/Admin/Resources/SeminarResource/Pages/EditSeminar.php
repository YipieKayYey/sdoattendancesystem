<?php

namespace App\Filament\Admin\Resources\SeminarResource\Pages;

use App\Filament\Admin\Resources\SeminarResource;
use App\Models\SeminarDay;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSeminar extends EditRecord
{
    protected static string $resource = SeminarResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // CRITICAL: Prevent any Carbon parsing of time field
        // Get raw value directly from database to avoid any casting
        $rawTime = $this->record->getRawOriginal('time');
        
        if ($rawTime) {
            $rawTime = trim((string)$rawTime);
            // Reject date formats immediately
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $rawTime) || preg_match('/^\d{4}-\d{2}-\d{2}/', $rawTime)) {
                $data['time'] = null;
            }
            // Extract HH:MM from HH:MM:SS
            elseif (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $rawTime, $matches)) {
                $data['time'] = sprintf('%02d:%02d', $matches[1], $matches[2]);
            }
            // Validate it's a time format
            elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $rawTime)) {
                $data['time'] = $rawTime;
            } else {
                $data['time'] = null;
            }
        } else {
            $data['time'] = null;
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // For multi-day seminars, if date is not set, use the first day's date
        if (!empty($data['is_multi_day']) && empty($data['date']) && !empty($data['days'])) {
            $firstDay = $data['days'][0] ?? null;
            if ($firstDay && !empty($firstDay['date'])) {
                $data['date'] = $firstDay['date'];
            }
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // If single-day seminar and no days exist, create Day 1
        $data = $this->form->getState();
        if (empty($data['is_multi_day']) && $this->record->days()->count() === 0) {
            SeminarDay::create([
                'seminar_id' => $this->record->id,
                'day_number' => 1,
                'date' => $this->record->date,
                'start_time' => $this->record->time,
                'venue' => $this->record->venue,
                'topic' => $this->record->topic,
                'room' => $this->record->room,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->delete();
                    Notification::make()
                        ->title('Seminar archived')
                        ->body('The seminar has been archived successfully.')
                        ->success()
                        ->send();
                    $this->redirect(SeminarResource::getUrl('index'));
                }),
        ];
    }
}
