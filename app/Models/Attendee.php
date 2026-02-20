<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendee extends Model
{
    protected $fillable = [
        'seminar_id',
        'name',
        'email',
        'position',
        'ticket_hash',
        'checked_in_at',
        'checked_out_at',
        'personnel_type',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'school_id',
        'school_other',
        'school_office_agency',
        'mobile_phone',
        'prc_license_no',
        'prc_license_expiry',
        'signature_consent',
        'signature_image',
        'signature_upload_path',
        'signature_timestamp',
        'signature_hash',
        'signature_metadata',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'prc_license_expiry' => 'date',
        'signature_consent' => 'boolean',
        'signature_timestamp' => 'datetime',
        'signature_metadata' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (Attendee $attendee) {
            // Normalize school fields when "other" is chosen
            if ($attendee->school_id === 'other' || $attendee->school_id === '') {
                $attendee->school_id = null;
                $attendee->school_office_agency = $attendee->school_other;
            } elseif ($attendee->school_id && is_numeric($attendee->school_id)) {
                $attendee->school_other = null;
                $school = \App\Models\School::find((int) $attendee->school_id);
                $attendee->school_office_agency = $school?->name ?? $attendee->school_office_agency;
            }

            if ($attendee->isDirty(['first_name', 'middle_name', 'last_name', 'suffix'])) {
                $parts = array_filter([
                    $attendee->first_name,
                    $attendee->middle_name,
                    $attendee->last_name,
                ]);
                $name = implode(' ', $parts);
                $suffix = trim((string) ($attendee->suffix ?? ''));
                if ($suffix !== '') {
                    $name .= ', ' . ltrim($suffix, " \t\n\r\0\x0B,");
                }
                $attendee->name = $name ?: ($attendee->getOriginal('name') ?? '');
            }
        });
    }

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getSchoolOfficeAgencyDisplayAttribute(): string
    {
        if ($this->school_id && $this->school) {
            return $this->school->name;
        }
        if (!empty($this->school_other)) {
            return $this->school_other;
        }
        return $this->school_office_agency ?? '—';
    }

    public function checkIns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendeeCheckIn::class);
    }

    public function isCheckedIn(): bool
    {
        // Check new structure first
        if ($this->checkIns()->whereNotNull('checked_in_at')->exists()) {
            return true;
        }
        // Fallback to old structure
        return $this->checked_in_at !== null;
    }

    public function isCheckedOut(): bool
    {
        // Check new structure first
        if ($this->checkIns()->whereNotNull('checked_out_at')->exists()) {
            return true;
        }
        // Fallback to old structure
        return $this->checked_out_at !== null;
    }

    public function getCheckInForDay($seminarDayId): ?AttendeeCheckIn
    {
        return $this->checkIns()->where('seminar_day_id', $seminarDayId)->first();
    }

    public function isCheckedInForDay($seminarDayId): bool
    {
        return $this->checkIns()
            ->where('seminar_day_id', $seminarDayId)
            ->whereNotNull('checked_in_at')
            ->exists();
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        $name = implode(' ', $parts) ?: $this->name;

        $suffix = trim((string) ($this->suffix ?? ''));
        if ($suffix !== '') {
            // Normalize: remove any leading comma to avoid ", ,"
            $suffix = ltrim($suffix, " \t\n\r\0\x0B,");
            $name .= ', ' . $suffix;
        }

        return $name;
    }

    public function isTeaching(): bool
    {
        return $this->personnel_type === 'teaching';
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_image) || !empty($this->signature_upload_path);
    }

    public function generateSignatureHash(): string
    {
        $data = $this->signature_image . json_encode($this->signature_metadata) . config('app.key');
        return hash('sha256', $data);
    }

    public function validateSignatureHash(): bool
    {
        if (empty($this->signature_hash)) {
            return false;
        }
        return $this->signature_hash === $this->generateSignatureHash();
    }
}
