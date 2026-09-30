<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EventsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $organizerId = Filament::getTenant()->id;

        // Total Events
        $totalEvents = Event::where('organizer_id', $organizerId)->count();

        // Active Events (published or live)
        $activeEvents = Event::where('organizer_id', $organizerId)
            ->whereIn('status', ['published', 'live'])
            ->count();

        // Upcoming Events (future start date)
        $upcomingEvents = Event::where('organizer_id', $organizerId)
            ->where('start_date', '>', Carbon::now())
            ->whereIn('status', ['published', 'live'])
            ->count();

        // Total Tickets Sold
        $totalTicketsSold = Ticket::where('organizer_id', $organizerId)
            ->whereIn('status', ['active', 'used'])
            ->count();

        // This Month's Ticket Sales
        $thisMonthTickets = Ticket::where('organizer_id', $organizerId)
            ->whereIn('status', ['active', 'used'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Last Month's Ticket Sales for comparison
        $lastMonthTickets = Ticket::where('organizer_id', $organizerId)
            ->whereIn('status', ['active', 'used'])
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        // Calculate percentage change
        $ticketChange = $lastMonthTickets > 0
            ? (($thisMonthTickets - $lastMonthTickets) / $lastMonthTickets) * 100
            : ($thisMonthTickets > 0 ? 100 : 0);

        return [
            Stat::make('Total Events', $totalEvents)
                ->description('All events created')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Active Events', $activeEvents)
                ->description('Published & Live events')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Upcoming Events', $upcomingEvents)
                ->description('Future events')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Tickets Sold', number_format($totalTicketsSold))
                ->description('All time ticket sales')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make('This Month\'s Sales', number_format($thisMonthTickets))
                ->description(
                    $ticketChange >= 0
                        ? number_format(abs($ticketChange), 1).'% increase'
                        : number_format(abs($ticketChange), 1).'% decrease'
                )
                ->descriptionIcon(
                    $ticketChange >= 0
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->color($ticketChange >= 0 ? 'success' : 'danger'),
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        
        if (!$user || $user->is_system_admin) {
            return false;
        }

        // Allow users with basic event viewing permissions
        return $user->can('view_events') && $user->can('view_dashboard');
    }
}
