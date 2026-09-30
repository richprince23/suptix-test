<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Bookings';

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Reservation Information')->schema([
                    TextInput::make('reservation_number')
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('event_id')
                        ->relationship('event', 'title', function (Builder $query) {
                            return $query->where('organizer_id', Filament::getTenant()->id);
                        })
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('reservation_type_id')
                        ->relationship('reservationType', 'name', function (Builder $query) {
                            return $query->where('organizer_id', Filament::getTenant()->id);
                        })
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(2),

                Section::make('Guest Information')->schema([
                    TextInput::make('guest_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('guest_email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('guest_phone')
                        ->tel()
                        ->maxLength(20),
                    TextInput::make('party_size')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                ])->columns(2),

                Section::make('Reservation Details')->schema([
                    DateTimePicker::make('reserved_at')
                        ->required(),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'seated' => 'Seated',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'no_show' => 'No Show',
                        ])
                        ->default('pending')
                        ->required(),
                    Textarea::make('special_requests')
                        ->rows(3)
                        ->maxLength(500),
                    Textarea::make('notes')
                        ->rows(3)
                        ->maxLength(500),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('guest_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guest_email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('party_size')
                    ->label('Party Size')
                    ->sortable(),
                TextColumn::make('reservationType.name')
                    ->label('Type')
                    ->sortable(),
                TextColumn::make('reserved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'seated',
                        'primary' => 'completed',
                        'danger' => ['cancelled', 'no_show'],
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'seated' => 'Seated',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                    ])
            ,
                SelectFilter::make('event')
                    ->relationship('event', 'title', function (Builder $query) {
                        return $query->where('organizer_id', Filament::getTenant()->id);
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('reservation_type')
                    ->relationship('reservationType', 'name', function (Builder $query) {
                        return $query->where('organizer_id', Filament::getTenant()->id);
                    })
                    ->searchable()
                    ->preload(),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Data'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reserved_at', 'asc');
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
        return static::getEloquentQuery()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }
}
