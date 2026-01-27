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
    ];

    protected $casts = [
        'date' => 'date',
        'is_open' => 'boolean',
    ];

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
