<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['created_at'] = $this->record->created_at;
        $data['updated_at'] = $this->record->updated_at;

        $profile = $this->record->attendeeProfile;
        if ($profile) {
            $data['personnel_type'] = $profile->personnel_type;
            $data['first_name'] = $profile->first_name;
            $data['middle_name'] = $profile->middle_name;
            $data['last_name'] = $profile->last_name;
            $data['suffix'] = $profile->suffix;
            $data['sex'] = $profile->sex;
            $data['school_id'] = $profile->school_id ?? ($profile->school_other ? 'other' : null);
            $data['school_other'] = $profile->school_other;
            $data['mobile_phone'] = $profile->mobile_phone;
            $data['position'] = $profile->position;
            $data['prc_license_no'] = $profile->prc_license_no;
            $data['prc_license_expiry'] = $profile->prc_license_expiry;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
