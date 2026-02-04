<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeminarDay extends Model
{
    protected static function boot()
    {
        parent::boot();

        // Handle time conversion when seminar day is created/updated
        static::saving(function ($seminarDay) {
            // Handle time conversion from form fields
            if (isset($seminarDay->start_time_hour, $seminarDay->start_time_minute, $seminarDay->start_time_period)) {
                $hour = (int)$seminarDay->start_time_hour;
                $minute = $seminarDay->start_time_minute;
                $period = $seminarDay->start_time_period;
                
                // Convert to 24-hour format
                if ($period === 'PM' && $hour !== 12) {
                    $hour += 12;
                } elseif ($period === 'AM' && $hour === 12) {
                    $hour = 0;
                }
                
                // Store as HH:MM format (no seconds)
                $seminarDay->start_time = sprintf('%02d:%02d', $hour, $minute);
                
                // Clean up temporary fields
                unset($seminarDay->start_time_hour, $seminarDay->start_time_minute, $seminarDay->start_time_period);
            }
        });

        // Handle time conversion for form filling
        static::retrieved(function ($seminarDay) {
            if ($seminarDay->start_time) {
                // Convert 24-hour to 12-hour for form display
                $time = substr($seminarDay->start_time, 0, 5); // Get HH:MM part
                [$hour, $minute] = explode(':', $time);
                
                $period = $hour >= 12 ? 'PM' : 'AM';
                $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
                
                // Set form attributes
                $seminarDay->start_time_hour = $displayHour;
                $seminarDay->start_time_minute = $minute;
                $seminarDay->start_time_period = $period;
            }
        });
    }
    protected $fillable = [
        'seminar_id',
        'day_number',
        'date',
        'start_time',
        'start_time_hour',
        'start_time_minute',
        'start_time_period',
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
        
        // Handle both HH:MM and HH:MM:SS formats
        $time = substr($this->start_time, 0, 5); // Get HH:MM part
        [$hour, $minute] = explode(':', $time);
        
        // Convert to 12-hour format for display
        $period = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        
        return $displayHour . ':' . $minute . ' ' . $period;
    }

    public function getAttendeesCountAttribute(): int
    {
        return $this->checkIns()->whereNotNull('checked_in_at')->count();
    }
}
