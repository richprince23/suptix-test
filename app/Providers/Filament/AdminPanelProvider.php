<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Tenancy\RegisterOrganizer;
use App\Filament\Widgets\EventsOverviewWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\SalesOverviewWidget;
use App\Filament\Widgets\TicketsSoldChartWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use App\Models\Organizer;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Yebor974\Filament\RenewPassword\RenewPasswordPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->tenant(Organizer::class)
            ->tenantRegistration(RegisterOrganizer::class)
            ->id('admin')
            ->path('admin')
            ->brandName('Organizer Admin')
            ->brandLogo(asset('suptix.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('favicon.png'))
            ->login()
            ->registration()
            // ->emailVerification()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::Pink,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                EventsOverviewWidget::class,
                SalesOverviewWidget::class,
                TicketsSoldChartWidget::class,
                RecentOrdersWidget::class,
                UpcomingEventsWidget::class,
                QuickActionsWidget::class,
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            ])
            ->plugin(
                RenewPasswordPlugin::make()
                    ->forceRenewPassword()
                    ->timestampColumn()
            )->navigationGroups([
                'Event Management',
                'Sales',
                'Marketing',
                'Finance',
                'Organization',
            ])
            ;
    }
}
