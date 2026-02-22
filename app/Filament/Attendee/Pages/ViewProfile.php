<?php

namespace App\Filament\Attendee\Pages;

use Filament\Actions;
use Filament\Pages\Page;

class ViewProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'My Profile';

    protected static string $view = 'filament.attendee.pages.view-profile';

    public function getProfile()
    {
        return auth()->user()->attendeeProfile;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Profile')
                ->icon('heroicon-o-pencil-square')
                ->url(EditProfile::getUrl()),
        ];
    }
}
