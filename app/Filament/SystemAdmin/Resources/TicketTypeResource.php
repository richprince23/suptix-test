<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\TicketTypeResource\Pages;
use App\Models\Event;
use App\Models\TicketType;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketTypeResource extends Resource
{
    protected static ?string $model = TicketType::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Events & Venues';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'All Ticket Types';

    protected static ?string $modelLabel = 'Ticket Type';

    protected static ?string $pluralModelLabel = 'All Ticket Types';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Type Information')
                    ->schema([
                        Select::make('organizer_id')
                            ->relationship('organizer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Organizer'),

                        Select::make('event_id')->options(function(Get $get){
                            $organizer_id = $get('organizer_id');
                            if (!$organizer_id){
                                return null;
                            }
                            return Event::where('organizer_id', $organizer_id)->pluck('title', 'id')->toArray();
                        })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Event'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., General Admission, VIP, Early Bird'),

                        Textarea::make('description')
                            ->placeholder('Describe what this ticket type includes')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Availability')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('GHS')
                            ->step(0.01)
                            ->placeholder('0.00'),

                        TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('100')
                            ->label('Available Quantity'),

                        DateTimePicker::make('sales_start')
                            ->label('Sales Start Date')
                            ->helperText('Leave empty to start sales immediately'),

                        DateTimePicker::make('sales_end')
                            ->label('Sales End Date')
                            ->helperText('Leave empty for no end date'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('price')
                    ->money('GHS')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Available')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sold_tickets_count')
                    ->label('Sold')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_capacity')
                    ->label('Total Capacity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sales_start')
                    ->label('Sales Start')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Immediate')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sales_end')
                    ->label('Sales End')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No end date')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
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

                Tables\Filters\Filter::make('available_now')
                    ->label('Available for Purchase')
                    ->query(fn (Builder $query): Builder => $query->available()),

                Tables\Filters\Filter::make('sales_ended')
                    ->label('Sales Ended')
                    ->query(fn (Builder $query): Builder => $query->where('sales_end', '<', now())),
            ])
            ->headerActions([
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTicketTypes::route('/'),
            'create' => Pages\CreateTicketType::route('/create'),
            'view' => Pages\ViewTicketType::route('/{record}'),
            'edit' => Pages\EditTicketType::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
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
