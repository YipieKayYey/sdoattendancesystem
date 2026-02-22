<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Attendee\Pages\ViewProfile;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AttendeeEditProfile extends \Filament\Pages\Auth\EditProfile
{
    public function mount(): void
    {
        if (auth()->user()->attendeeProfile) {
            $this->redirect(ViewProfile::getUrl());
            return;
        }
        parent::mount();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/edit-profile.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation')
            ->required(fn (Get $get): bool => filled($get('current_password')))
            ->helperText('Required when changing password.');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/edit-profile.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn (Get $get): bool => filled($get('password')))
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('current_password')
            ->label('Current password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->dehydrated(false)
            ->required(fn (Get $get): bool => filled($get('password')))
            ->rule('current_password:web')
            ->live(debounce: 500)
            ->helperText('Enter your current password to verify your identity before changing it.');
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }

    public function save(): void
    {
        parent::save();
        $this->data['current_password'] = null;
    }
}
