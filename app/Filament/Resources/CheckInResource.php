<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckInResource\Pages;
use App\Models\CheckIn;
use App\Models\Event;
use App\Models\Ticket;
use Filament\Actions\Exports\Exporter;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CheckInResource extends Resource
{
    protected static ?string $model = CheckIn::class;

    protected static ?string $navigationGroup = 'Event Management';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Check-In Records';

    protected static ?string $modelLabel = 'Check-In Record';

    protected static ?string $pluralModelLabel = 'Check-In Records';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return true; // All organizer users can view check-in records
    }

    public static function canCreate(): bool
    {
        return false; // Check-ins are created through the check-in process, not manually
    }

    public static function canEdit(Model $_record): bool
    {
        return false; // Check-ins should not be manually edited
    }

    public static function canDelete(Model $_record): bool
    {
        return false; // Check-ins should not be manually deleted
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ticket_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('event_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('checked_in_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getCheckedInAttendees())
            ->columns([
                Tables\Columns\TextColumn::make('attendee_name')
                    ->label('Attendee Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendee_email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Ticket' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket Number')
                    ->searchable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Checked In At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('checked_in_by_name')
                    ->label('Checked In By')
                    ->placeholder('System'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Event Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(function () {
                        $organizer = Filament::getTenant();

                        if (!$organizer) {
                            return [];
                        }

                        return Event::where('organizer_id', $organizer->id)
                            ->orderBy('start_date', 'desc')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Ticket' => 'Ticket',
                    ]),
                Tables\Filters\Filter::make('checked_in_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('checked_in_at', today()))
                    ->label('Checked in today'),
            ])->headerActions([
                ExportAction::make()
                ->label('Export Attendees'),
            ])
            ->actions([
                // No actions since this is read-only
            ])
            ->bulkActions([
                // No bulk actions since this is read-only
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->emptyStateHeading('No Check-Ins Found')
            ->emptyStateDescription('When attendees check in to your events, they will appear here.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckIns::route('/'),
        ];
    }

    /**
     * Get a query that returns checked-in tickets
     */
    public static function getCheckedInAttendees(): Builder
    {
        $organizer = Filament::getTenant();

        if (!$organizer) {
            return CheckIn::query()->whereRaw('1 = 0'); // Return empty query
        }

        // Build the checked-in tickets query
        $ticketQuery = Ticket::query()
            ->select([
                'tickets.id',
                'tickets.attendee_name',
                'tickets.attendee_email',
                'tickets.event_id',
                'tickets.ticket_number',
                'tickets.used_at as checked_in_at',
                DB::raw("'Ticket' as type"),
                DB::raw('NULL as checked_in_by_name'),
                'tickets.created_at',
                'tickets.updated_at',
            ])
            ->whereNotNull('tickets.used_at')
            ->where('tickets.organizer_id', $organizer->id);

        $unionQuery = $ticketQuery;

        // Create a new query from the union result
        return CheckIn::query()
            ->fromSub($unionQuery, 'check_ins')
            ->join('events', 'events.id', '=', 'check_ins.event_id')
            ->select([
                'check_ins.*',
                'events.title as event_title',
                'events.start_date',
                'events.start_time',
            ]);
    }
}
