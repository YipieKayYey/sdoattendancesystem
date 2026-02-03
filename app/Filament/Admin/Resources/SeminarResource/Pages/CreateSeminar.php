<?php

namespace App\Filament\Admin\Resources\SeminarResource\Pages;

use App\Filament\Admin\Resources\SeminarResource;
use App\Models\SeminarDay;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSeminar extends CreateRecord
{
    protected static string $resource = SeminarResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
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
    
    protected function afterCreate(): void
    {
        // If single-day seminar and no days created, create Day 1
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
}
