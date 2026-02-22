<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends BaseLogin
{
    public function getTitle(): string | Htmlable
    {
        return 'Admin Login';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Admin Login';
    }
}
