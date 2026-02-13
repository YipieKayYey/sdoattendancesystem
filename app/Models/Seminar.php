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
        'is_open',
        'capacity',
        'venue',
        'topic',
        'time',
        'room',
        'survey_form_url',
        'is_multi_day',
    ];

    protected $casts = [
        'date' => 'date',
        'is_open' => 'boolean',
        'is_ended' => 'boolean',
        'is_multi_day' => 'boolean',
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
        
        // Handle Carbon instance from new form fields
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
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
    public function getFormattedTimeAttribute(): ?string
    {
        if (!$this->time) {
            return null;
        }
        
        // Handle both HH:MM and HH:MM:SS formats
        $time = substr($this->time, 0, 5); // Get HH:MM part
        [$hour, $minute] = explode(':', $time);
        
        // Convert to 12-hour format for display
        $period = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        
        return $displayHour . ':' . $minute . ' ' . $period;
    }

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
                do {
                    $seminar->slug = Str::random(8);
                } while (static::where('slug', $seminar->slug)->exists());
            }
            // Set capacity to null for open seminars
            if ($seminar->is_open) {
                $seminar->capacity = null;
            }
        });

        static::updating(function ($seminar) {
            if ($seminar->isDirty('slug') && empty($seminar->slug)) {
                do {
                    $seminar->slug = Str::random(8);
                } while (static::where('slug', $seminar->slug)->where('id', '!=', $seminar->id)->exists());
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
        // First check the database field if it exists
        if (isset($this->attributes['is_multi_day'])) {
            return $this->is_multi_day;
        }
        
        // Fallback to counting days for backward compatibility
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

    public function surveyLinkClicks(): HasMany
    {
        return $this->hasMany(SurveyLinkClick::class);
    }

    public function getSurveyTrackingLinkAttribute(): string
    {
        return route('survey.redirect', ['slug' => $this->slug]);
    }

    public function getSurveyClicksCountAttribute(): int
    {
        return $this->surveyLinkClicks()->count();
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
