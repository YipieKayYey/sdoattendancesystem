<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function attendeeProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AttendeeProfile::class);
    }

    public function attendees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAttendee(): bool
    {
        return $this->role === 'attendee';
    }

    /**
     * Admin users → admin panel. Attendee users → attendee panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'attendee' => $this->isAttendee(),
            default => false,
        };
    }
}
