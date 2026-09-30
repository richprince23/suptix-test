<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\DiscountResource\Pages;
use App\Models\Discount;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Sales & Commerce';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'All Discounts';

    protected static ?string $modelLabel = 'Discount';

    protected static ?string $pluralModelLabel = 'All Discounts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Discount Information')
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
                            ->searchable()
                            ->preload()
                            ->label('Event')
                            ->helperText('Leave empty for organizer-wide discount'),

                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('EARLYBIRD2024')
                            ->label('Discount Code'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Early Bird Special'),

                        Textarea::make('description')
                            ->placeholder('Description of the discount')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Discount Details')
                    ->schema([
                        Select::make('type')
                            ->required()
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->live()
                            ->label('Discount Type'),

                        TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('type') === 'fixed' ? 'GHS' : '')
                            ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : '')
                            ->placeholder(fn ($get) => $get('type') === 'percentage' ? '10' : '50.00')
                            ->label('Discount Value'),

                        TextInput::make('usage_limit')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('100')
                            ->label('Usage Limit')
                            ->helperText('Leave empty for unlimited uses'),

                        TextInput::make('used_count')
                            ->numeric()
                            ->disabled()
                            ->default(0)
                            ->label('Times Used'),

                        DatePicker::make('valid_from')
                            ->label('Valid From')
                            ->helperText('Leave empty to start immediately'),

                        DatePicker::make('valid_until')
                            ->label('Valid Until')
                            ->helperText('Leave empty for no expiry'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Toggle to enable/disable this discount'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->placeholder('All Events'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'info',
                    }),

                TextColumn::make('value')
                    ->label('Discount')
                    ->formatStateUsing(function ($record) {
                        return $record->type === 'percentage'
                            ? $record->value . '%'
                            : 'GHS ' . number_format($record->value, 2);
                    }),

                TextColumn::make('used_count')
                    ->label('Used')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->numeric()
                    ->sortable()
                    ->placeholder('∞'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->date()
                    ->sortable()
                    ->placeholder('Immediate')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date()
                    ->sortable()
                    ->placeholder('No expiry')
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

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ]),
                    Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->native(false),

                    Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now())),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('is_active', true)->count();
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
