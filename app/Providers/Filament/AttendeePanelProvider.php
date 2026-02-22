<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AttendeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn (): string => view('filament.attendee.hooks.panel-styles')->render())
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => view('filament.admin.hooks.login-logo-css')->render())
            ->id('attendee')
            ->path('attendee')
            ->login(\App\Filament\Pages\Auth\AttendeeLogin::class)
            ->profile(\App\Filament\Pages\Auth\AttendeeEditProfile::class)
            ->revealablePasswords(true)
            ->brandLogo(asset('images/sdologo.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('favicon.ico'))
            ->sidebarCollapsibleOnDesktop()
            ->colors(['primary' => Color::Sky])
            ->discoverPages(in: app_path('Filament/Attendee/Pages'), for: 'App\\Filament\\Attendee\\Pages')
            ->discoverWidgets(in: app_path('Filament/Attendee/Widgets'), for: 'App\\Filament\\Attendee\\Widgets')
            ->pages([
                \App\Filament\Attendee\Pages\Dashboard::class,
            ])
            ->widgets([
                \Filament\Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
