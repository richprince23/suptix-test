<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SystemAdminMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SystemAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('system-admin')
            ->path('system-admin')
            ->brandName('Suptix System Admin')
            ->brandLogo(asset('favicon.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('favicon.png'))
            ->login()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::Purple,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/SystemAdmin/Resources'), for: 'App\\Filament\\SystemAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/SystemAdmin/Pages'), for: 'App\\Filament\\SystemAdmin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SystemAdmin/Widgets'), for: 'App\\Filament\\SystemAdmin\\Widgets')
            ->widgets([
                \App\Filament\SystemAdmin\Widgets\SystemOverviewWidget::class,
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
            ->authMiddleware([
                Authenticate::class,
                SystemAdminMiddleware::class,
            ])
            ->authGuard('system-admin')
            ->authPasswordBroker('users')
            ->navigationGroups([
                'System Overview',
                'Organizations',
                'Events & Venues',
                'Sales & Commerce',
                'User Management',
                'System Settings',
            ]);
    }
}
