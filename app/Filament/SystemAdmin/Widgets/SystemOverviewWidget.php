<?php

namespace App\Filament\SystemAdmin\Widgets;

use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalOrganizers = Organizer::count();
        $totalUsers = User::count();
        $totalEvents = Event::count();
        $totalOrders = Order::where('status', 'confirmed')->count();
        $totalRevenue = Order::where('status', 'confirmed')->sum('total_amount');

        return [
            Stat::make('Total Organizers', $totalOrganizers)
                ->description('Active organizations')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Total Users', $totalUsers)
                ->description('System users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Total Events', $totalEvents)
                ->description('All events created')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Total Orders', $totalOrders)
                ->description('Orders processed')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),

            Stat::make('Total Revenue', 'GHS '.number_format($totalRevenue, 2))
                ->description('System-wide revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
