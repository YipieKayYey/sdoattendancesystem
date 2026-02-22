<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\AttendeeProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<string, mixed>|null */
    protected ?array $pendingProfileData = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['role'] ?? '') === 'attendee') {
            $this->pendingProfileData = $this->extractProfileData($data);
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->pendingProfileData !== null && $this->record->role === 'attendee') {
            AttendeeProfile::create(array_merge($this->pendingProfileData, [
                'user_id' => $this->record->id,
            ]));
            $this->pendingProfileData = null;
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
