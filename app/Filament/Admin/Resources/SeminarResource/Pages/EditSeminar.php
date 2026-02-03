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
        // For multi-day seminars, ensure primary date syncs with Day 1
        if (!empty($data['is_multi_day']) && !empty($data['days'])) {
            // Find Day 1 and sync its date with primary date
            foreach ($data['days'] as &$day) {
                if (isset($day['day_number']) && $day['day_number'] == 1) {
                    if (!empty($data['date'])) {
                        $day['date'] = $data['date'];
                    } elseif (!empty($day['date'])) {
                        $data['date'] = $day['date'];
                    }
                    break;
                }
            }
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        $data = $this->form->getState();
        
        // For multi-day seminars, ensure Day 1 date is always synced with primary date
        if (!empty($data['is_multi_day'])) {
            $day1 = $this->record->days()->where('day_number', 1)->first();
            if ($day1) {
                $day1->update(['date' => $this->record->date]);
            }
        }
        
        // If single-day seminar and no days exist, create Day 1
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
