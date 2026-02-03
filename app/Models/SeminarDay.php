<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeminarDay extends Model
{
    protected $fillable = [
        'seminar_id',
        'day_number',
        'date',
        'start_time',
        'venue',
        'topic',
        'room',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(AttendeeCheckIn::class);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedTimeAttribute(): ?string
    {
        if (!$this->start_time) {
            return null;
        }
        // Convert HH:MM:SS to HH:MM for display
        return substr($this->start_time, 0, 5);
    }

    public function getAttendeesCountAttribute(): int
    {
        return $this->checkIns()->whereNotNull('checked_in_at')->count();
    }
}
