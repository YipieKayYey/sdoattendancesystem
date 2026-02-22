<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttendeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'universal_qr_hash',
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
        'position',
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
        'prc_license_expiry' => 'date',
        'signature_consent' => 'boolean',
        'signature_timestamp' => 'datetime',
        'signature_metadata' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (AttendeeProfile $profile) {
            if (empty($profile->universal_qr_hash)) {
                do {
                    $profile->universal_qr_hash = Str::random(16);
                } while (self::where('universal_qr_hash', $profile->universal_qr_hash)->exists());
            }
        });

        static::saving(function (AttendeeProfile $profile) {
            if ($profile->school_id === 'other' || $profile->school_id === '') {
                $profile->school_id = null;
                $profile->school_office_agency = $profile->school_other;
            } elseif ($profile->school_id && is_numeric($profile->school_id)) {
                $profile->school_other = null;
                $school = School::find((int) $profile->school_id);
                $profile->school_office_agency = $school?->name ?? $profile->school_office_agency;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        $name = implode(' ', $parts);

        $suffix = trim((string) ($this->suffix ?? ''));
        if ($suffix !== '') {
            $suffix = ltrim($suffix, " \t\n\r\0\x0B,");
            $name .= ', ' . $suffix;
        }

        return $name ?: '—';
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_image) || !empty($this->signature_upload_path);
    }

    /**
     * Find profile by universal QR hash (for scanner lookup).
     */
    public static function findByUniversalQrHash(string $hash): ?self
    {
        return self::where('universal_qr_hash', $hash)->first();
    }
}
