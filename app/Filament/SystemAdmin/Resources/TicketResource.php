<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Sales & Commerce';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false; // Readonly resource
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Only show active tickets - cancelled tickets are automatically deleted with incomplete orders
        return parent::getEloquentQuery()->where('status', '!=', 'cancelled');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('organizer_id')
                    ->relationship('organizer', 'name')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('ticket_number')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('ticket_type_id')
                    ->relationship('ticketType', 'name')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('attendee_name')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('attendee_email')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('price')
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('GHS')
                    ->numeric(),
                TextInput::make('status')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('used_at')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('cancelled_at')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                    
                \Filament\Forms\Components\Section::make('Additional Information')
                    ->schema(function (?Ticket $record) {
                        if (! $record) {
                            return [];
                        }

                        $responses = $record->responses()->with('field')->get();
                        if ($responses->isEmpty()) {
                            return [];
                        }

                        return $responses->map(function ($response) {
                            return \Filament\Forms\Components\Placeholder::make('custom_field_' . $response->id)
                                ->label($response->field?->label ?? 'Custom Field')
                                ->content($response->value);
                        })->toArray();
                    })
                    ->visible(function (?Ticket $record) {
                        if (! $record) {
                            return false;
                        }
                        return $record->responses()->exists();
                    })
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('ticketType.name')
                    ->label('Ticket Type')
                    ->searchable(),
                TextColumn::make('attendee_name')
                    ->label('Attendee')
                    ->searchable()
                    ->description(fn ($record) => $record->attendee_email),
                TextColumn::make('price')
                    ->money('GHS')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'used',
                        'danger' => 'cancelled',
                        'gray' => 'pending',
                    ]),
                TextColumn::make('used_at')
                    ->label('Used Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('cancelled_at')
                    ->label('Cancelled Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizer')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'used' => 'Used',
                        'cancelled' => 'Cancelled',
                        'pending' => 'Pending',
                    ]),
                SelectFilter::make('ticketType')
                    ->relationship('ticketType', 'name')
                    ->searchable()
                    ->preload(),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
