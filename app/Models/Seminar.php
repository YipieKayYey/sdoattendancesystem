<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Seminar extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'date',
        'capacity',
        'is_open',
        'is_ended',
        'venue',
        'topic',
        'time',
        'room',
    ];

    protected $casts = [
        'date' => 'date',
        'is_open' => 'boolean',
        'is_ended' => 'boolean',
        // DO NOT cast time - keep it as string to avoid Carbon parsing issues
    ];

    /**
     * Set the time attribute - ensure it's always stored as HH:MM:SS
     */
    public function setTimeAttribute($value)
    {
        if (empty($value) || $value === null || $value === '') {
            $this->attributes['time'] = null;
            return;
        }
        
        // If it's a Carbon/DateTime instance, extract time
        if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
            $this->attributes['time'] = $value->format('H:i:s');
            return;
        }
        
        // If it's a string, validate and normalize
        if (is_string($value)) {
            $value = trim($value);
            
            // Reject date formats immediately
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $value)) {
                $this->attributes['time'] = null;
                return;
            }
            
            // Accept HH:MM format
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $matches)) {
                $hour = (int)$matches[1];
                $minute = (int)$matches[2];
                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                    $this->attributes['time'] = sprintf('%02d:%02d:00', $hour, $minute);
                    return;
                }
            }
            
            // Accept HH:MM:SS format
            if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $matches)) {
                $hour = (int)$matches[1];
                $minute = (int)$matches[2];
                $second = (int)$matches[3];
                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 && $second >= 0 && $second <= 59) {
                    $this->attributes['time'] = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
                    return;
                }
            }
        }
        
        // If we get here, set to null
        $this->attributes['time'] = null;
    }

    /**
     * Get the time attribute - always return as string or null
     */
    public function getTimeAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // If somehow it's a Carbon instance, convert to string
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format('H:i:s');
        }
        
        // Return as-is (should be string)
        return $value;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($seminar) {
            if (empty($seminar->slug)) {
                $seminar->slug = Str::slug($seminar->title);
            }
            // Set capacity to null for open seminars
            if ($seminar->is_open) {
                $seminar->capacity = null;
            }
        });

        static::updating(function ($seminar) {
            if ($seminar->isDirty('title') && empty($seminar->slug)) {
                $seminar->slug = Str::slug($seminar->title);
            }
            // Set capacity to null for open seminars
            if ($seminar->is_open) {
                $seminar->capacity = null;
            }
        });
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(SeminarDay::class)->orderBy('day_number');
    }

    public function isMultiDay(): bool
    {
        return $this->days()->count() > 1;
    }

    public function getDaysCountAttribute(): int
    {
        return $this->days()->count();
    }

    public function getDayForDate($date): ?SeminarDay
    {
        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }
        return $this->days()->whereDate('date', $date)->first();
    }

    public function getCurrentDay(): ?SeminarDay
    {
        return $this->getDayForDate(now());
    }

    public function getRegistrationUrlAttribute(): string
    {
        return route('register', ['slug' => $this->slug]);
    }

    public function getRegisteredCountAttribute(): int
    {
        return $this->attendees()->count();
    }

    public function isFull(): bool
    {
        // Open seminars or seminars with null capacity never fill up
        if ($this->is_open || $this->capacity === null) {
            return false;
        }
        return $this->registered_count >= $this->capacity;
    }
}
