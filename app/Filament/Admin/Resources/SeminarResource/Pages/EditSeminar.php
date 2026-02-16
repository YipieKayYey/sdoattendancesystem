<?php

namespace App\Filament\Admin\Resources\SeminarResource\Pages;

use App\Filament\Admin\Resources\SeminarResource;
use App\Models\SeminarDay;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditSeminar extends EditRecord
{
    protected static string $resource = SeminarResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        Log::info('mutateFormDataBeforeFill called for seminar ' . $this->record->id);
        Log::info('Seminar time: ' . ($this->record->time ?? 'null'));
        
        // Handle single-day seminar time
        if (!empty($this->record->time)) {
            $rawTime = $this->record->time;
            Log::info('Processing single-day time: ' . $rawTime);
            
            // Handle both HH:MM and HH:MM:SS formats
            if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $rawTime, $matches)) {
                $hour = (int)$matches[1];
                $minute = $matches[2];
                
                // Convert to 12-hour format
                $period = $hour >= 12 ? 'PM' : 'AM';
                $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
                
                $data['time_hour'] = $displayHour;
                $data['time_minute'] = $minute;
                $data['time_period'] = $period;
                $data['time'] = substr($rawTime, 0, 5); // Store as HH:MM
                
                Log::info('Set time fields: hour=' . $displayHour . ', minute=' . $minute . ', period=' . $period);
            } else {
                $data['time'] = null;
                Log::info('Time format not matched: ' . $rawTime);
            }
        } else {
            $data['time'] = null;
            Log::info('No seminar time found');
        }
        
        // Handle multi-day seminar times
        if (isset($data['days']) && is_array($data['days'])) {
            foreach ($data['days'] as &$day) {
                if (isset($day['id'])) {
                    $seminarDay = SeminarDay::find($day['id']);
                    $seminarDay = \App\Models\SeminarDay::find($day['id']);
                    if ($seminarDay && $seminarDay->start_time) {
                        $startTime = $seminarDay->start_time; // "14:30"
                        
                        // Handle both HH:MM and HH:MM:SS formats
                        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $startTime, $matches)) {
                            $hour = (int)$matches[1];
                            $minute = $matches[2];
                            
                            // Convert to 12-hour format
                            $period = $hour >= 12 ? 'PM' : 'AM';
                            $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
                            
                            $day['start_time_hour'] = $displayHour;
                            $day['start_time_minute'] = $minute;
                            $day['start_time_period'] = $period;
                            $day['start_time'] = substr($startTime, 0, 5); // Store as HH:MM
                        }
                    }
                }
            }
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
                    $day['date'] = $data['date'];
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

    protected function getRedirectUrl(): string
    {
        return SeminarResource::getUrl('view', ['record' => $this->record]);
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
