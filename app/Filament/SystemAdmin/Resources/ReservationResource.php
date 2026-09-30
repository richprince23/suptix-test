<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Sales & Commerce';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'All Reservations';

    protected static ?string $modelLabel = 'Reservation';

    protected static ?string $pluralModelLabel = 'All Reservations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reservation Information')
                    ->schema([
                        Select::make('organizer_id')
                            ->relationship('organizer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Organizer'),

                        Select::make('event_id')
                            ->relationship('event', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Event'),

                        Select::make('reservation_type_id')
                            ->relationship('reservationType', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Reservation Type'),

                        TextInput::make('reservation_number')
                            ->disabled()
                            ->label('Reservation Number')
                            ->helperText('Auto-generated on save'),
                    ])->columns(2),

                Forms\Components\Section::make('Guest Information')
                    ->schema([
                        TextInput::make('guest_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('John Doe'),

                        TextInput::make('guest_email')
                            ->required()
                            ->email()
                            ->placeholder('john@example.com'),

                        TextInput::make('guest_phone')
                            ->tel()
                            ->placeholder('0240123456'),

                        TextInput::make('party_size')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('2')
                            ->label('Party Size'),

                        DateTimePicker::make('reserved_at')
                            ->required()
                            ->label('Reservation Date & Time'),

                        Select::make('status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed',
                            ])
                            ->default('pending'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Textarea::make('special_requests')
                            ->placeholder('Any special requests from the guest')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->placeholder('Internal notes about this reservation')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('guest_name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                TextColumn::make('reservationType.name')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->placeholder('General'),

                TextColumn::make('party_size')
                    ->label('Party Size')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reserved_at')
                    ->label('Reservation Time')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                    }),

                TextColumn::make('guest_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('guest_phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organizer')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('today_reservations')
                    ->label('Today\'s Reservations')
                    ->query(fn (Builder $query): Builder => $query->whereDate('reserved_at', today())),

                Tables\Filters\Filter::make('upcoming_reservations')
                    ->label('Upcoming Reservations')
                    ->query(fn (Builder $query): Builder => $query->where('reserved_at', '>', now())),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reserved_at', 'desc');
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->whereIn('status', ['pending', 'confirmed'])->count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
