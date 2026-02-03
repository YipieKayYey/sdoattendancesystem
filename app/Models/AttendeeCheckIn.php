<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendeeCheckIn extends Model
{
    protected $fillable = [
        'attendee_id',
        'seminar_day_id',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function seminarDay(): BelongsTo
    {
        return $this->belongsTo(SeminarDay::class);
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function isCheckedOut(): bool
    {
        return $this->checked_out_at !== null;
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->checked_in_at) {
            return null;
        }

        $end = $this->checked_out_at ?? now();
        return $this->checked_in_at->diffForHumans($end, true);
    }
}
