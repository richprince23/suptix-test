<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::where('organizer_id', Filament::getTenant()->id)
                    ->where('start_date', '>', Carbon::now())
                    ->whereIn('status', ['published', 'live'])
                    ->with(['ticketTypes'])
                    ->orderBy('start_date')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('title')
                    ->label('Event')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->title)
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('venue_name')
                    ->label('Venue')
                    ->searchable()
                    ->limit(30)
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Time')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('tickets_sold')
                    ->label('Sold')
                    ->getStateUsing(fn ($record) => $record->tickets()->whereIn('status', ['active', 'used'])->count())
                    ->icon('heroicon-m-ticket'),

                Tables\Columns\TextColumn::make('total_capacity')
                    ->label('Capacity')
                    ->getStateUsing(function ($record) {
                        // Calculate total original capacity across all ticket types
                        return $record->ticketTypes->sum(function ($ticketType) {
                            return $ticketType->total_capacity;
                        });
                    })
                    ->icon('heroicon-m-users'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'published',
                        'info' => 'live',
                        'warning' => 'draft',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('days_until')
                    ->label('In')
                    ->getStateUsing(fn ($record) => (int) Carbon::now()->diffInDays($record->start_date).' days')
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.admin.resources.events.view', [
                        'tenant' => Filament::getTenant(),
                        'record' => $record,
                    ])),
            ])
            ->emptyStateHeading('No upcoming events')
            ->emptyStateDescription('Create and publish events to see them here.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->heading('Upcoming Events')
            ->description('Next 8 scheduled events')
            ->headerActions([
                Tables\Actions\Action::make('create_event')
                    ->label('Create Event')
                    ->icon('heroicon-m-plus')
                    ->url(fn () => route('filament.admin.resources.events.create', [
                        'tenant' => Filament::getTenant(),
                    ]))
                    ->color('success'),

                Tables\Actions\Action::make('view_all')
                    ->label('View All Events')
                    ->icon('heroicon-m-arrow-right')
                    ->url(fn () => route('filament.admin.resources.events.index', [
                        'tenant' => Filament::getTenant(),
                    ]))
                    ->color('primary'),
            ]);
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
