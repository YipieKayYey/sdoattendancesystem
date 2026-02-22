<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\AttendeeProfile;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
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

    protected function afterSave(): void
    {
        if ($this->record->role !== 'attendee') {
            return;
        }

        $data = $this->form->getState();
        $profileData = $this->extractProfileData($data);

        $profile = $this->record->attendeeProfile;
        if ($profile) {
            $profile->update($profileData);
        } else {
            AttendeeProfile::create(array_merge($profileData, ['user_id' => $this->record->id]));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractProfileData(array $data): array
    {
        $profileFields = [
            'personnel_type', 'first_name', 'middle_name', 'last_name', 'suffix',
            'sex', 'school_id', 'school_other', 'school_office_agency', 'mobile_phone',
            'position', 'prc_license_no', 'prc_license_expiry',
        ];
        $profile = [];
        foreach ($profileFields as $key) {
            if (array_key_exists($key, $data)) {
                $profile[$key] = $data[$key];
            }
        }
        if (($profile['school_id'] ?? null) === 'other') {
            $profile['school_id'] = null;
            $profile['school_office_agency'] = $profile['school_other'] ?? null;
        } elseif (!empty($profile['school_id']) && $profile['school_id'] !== 'other') {
            $school = \App\Models\School::find((int) $profile['school_id']);
            $profile['school_office_agency'] = $school?->name ?? $profile['school_office_agency'] ?? null;
        }
        return $profile;
    }
}
