<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EventStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $organizerId = Filament::getTenant()->id;

        // Draft Events
        $draftEvents = Event::where('organizer_id', $organizerId)
            ->where('status', 'draft')
            ->count();

        // Published Events
        $publishedEvents = Event::where('organizer_id', $organizerId)
            ->where('status', 'published')
            ->count();

        // Live Events
        $liveEvents = Event::where('organizer_id', $organizerId)
            ->where('status', 'live')
            // ->where('start_date', '=', Carbon::now())
            ->count();

        // Past Events
        $pastEvents = Event::where('organizer_id', $organizerId)
            // ->where('end_date', '<', Carbon::now())
            ->where('status', '=', 'past')
            ->count();

        return [
            Stat::make('Draft Events', $draftEvents)
                ->description('Unpublished events')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Published Events', $publishedEvents)
                ->description('Ready for ticket sales')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

                Stat::make('Live Events', $liveEvents)
                ->description('Currently happening')
                ->descriptionIcon('heroicon-m-signal')
                ->color('success'),

            Stat::make('Past Events', $pastEvents)
                ->description('Completed events')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('gray'),
        ];
    }
}
