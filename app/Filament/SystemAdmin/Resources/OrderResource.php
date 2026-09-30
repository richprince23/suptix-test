<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales & Commerce';

    protected static ?string $navigationLabel = 'All Orders';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Information')->schema([
                    TextInput::make('order_number')
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('organizer_id')
                        ->relationship('organizer', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('event_id')
                        ->relationship('event', 'title')
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(2),

                Section::make('Buyer Information')->schema([
                    TextInput::make('buyer_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('buyer_email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('buyer_phone')
                        ->tel()
                        ->maxLength(255),
                ])->columns(2),

                Section::make('Order Details')->schema([
                    TextInput::make('total_amount')
                        ->numeric()
                        ->prefix('GHS')
                        ->required(),
                    Select::make('payment_method')
                        ->options([
                            'paystack' => 'Paystack',
                            'credit_card' => 'Credit Card',
                            'debit_card' => 'Debit Card',
                            'bank_transfer' => 'Bank Transfer',
                            'cash' => 'Cash',
                        ])
                        ->required(),
                    Select::make('status')
                        ->label('Order Status')
                        ->options([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required(),
                    DateTimePicker::make('purchased_at')
                        ->label('Purchase Date'),
                ])->columns(2),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Only show confirmed orders - incomplete orders are automatically deleted by cleanup command
        return parent::getEloquentQuery()->where('status', 'confirmed');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('buyer_name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->buyer_email),
                TextColumn::make('total_amount')
                    ->money('GHS')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('payments_status')
                    ->label('Payment Status')
                    ->badge()
                    ->state(function ($record) {
                        $payment = $record->payments()->latest()->first();

                        return $payment ? $payment->status : 'No Payment';
                    })
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => ['failed', 'cancelled'],
                        'info' => 'refunded',
                    ]),
                TextColumn::make('total_tickets')
                    ->label('Tickets')
                    ->state(function ($record) {
                        return $record->tickets()->count();
                    }),
                TextColumn::make('purchased_at')
                    ->label('Purchase Date')
                    ->dateTime()
                    ->sortable(),
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
                SelectFilter::make('status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'paystack' => 'Paystack',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                    ]),
            ])->headerActions([
                ExportAction::make()
                    ->label('Export Orders'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
