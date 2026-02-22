<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class AttendeeLogin extends BaseLogin
{
    public function getTitle(): string | Htmlable
    {
        return 'Attendee Login';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Attendee Login';
    }
}
