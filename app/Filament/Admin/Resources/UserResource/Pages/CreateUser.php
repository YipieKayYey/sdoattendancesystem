<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\AttendeeProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->role === 'attendee') {
            AttendeeProfile::create(['user_id' => $this->record->id]);
        }
    }
}
