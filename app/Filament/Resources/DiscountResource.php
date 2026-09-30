<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Marketing';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Discount Information')->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)->formatStateUsing(function ($state) {
                            // replace spaces and return uppercase
                            return strtoupper(str_replace(' ', '-', $state));
                        }),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(500),
                    Select::make('event_id')
                        ->relationship('event', 'title', function (Builder $query) {
                            return $query->where('organizer_id', Filament::getTenant()->id);
                        })
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->helperText('Leave empty to apply to all events'),
                ])->columns(2),

                Section::make('Discount Configuration')->schema([
                    Select::make('type')
                        ->options([
                            'percentage' => 'Percentage',
                            'fixed_amount' => 'Fixed Amount',
                        ])
                        ->required()
                        ->default('percentage')
                        ->reactive(),
                    TextInput::make('value')
                        ->required()
                        ->numeric()
                        ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : 'GHS')
                        ->maxValue(fn ($get) => $get('type') === 'percentage' ? 100 : null),
                    TextInput::make('usage_limit')
                        ->numeric()
                        ->nullable()
                        ->helperText('Leave empty for unlimited usage'),
                    TextInput::make('used_count')
                        ->numeric()
                        ->default(0)
                        ->disabled(),
                ])->columns(2),

                Section::make('Validity Period')->schema([
                    DatePicker::make('valid_from')
                        ->required()
                        ->default(now()),
                    DatePicker::make('valid_until')
                        ->required()
                        ->after('valid_from'),
                    Toggle::make('is_active')
                        ->default(true),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->placeholder('All Events'),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'percentage',
                        'info' => 'fixed_amount',
                    ])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage' ? $state.'%' : 'GHS'.number_format($state, 2)
                    ),
                TextColumn::make('usage_stats')
                    ->label('Usage')
                    ->getStateUsing(fn ($record) => $record->used_count.($record->usage_limit ? '/'.$record->usage_limit : '/∞')
                    ),
                TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed_amount' => 'Fixed Amount',
                    ])
            ,
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                SelectFilter::make('event')
                    ->relationship('event', 'title', function (Builder $query) {
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
            ->defaultSort('created_at', 'desc');
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
}
