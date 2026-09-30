<?php

namespace App\Filament\SystemAdmin\Resources;

use App\Filament\SystemAdmin\Resources\PaymentResource\Pages;
use App\Models\Payment;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Sales & Commerce';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false; // Readonly resource
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Only show successful payments - failed/pending payments are automatically deleted by cleanup command
        return parent::getEloquentQuery()->whereNotIn('status', ['failed', 'cancelled', 'pending', 'processing']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('organizer_id')
                    ->relationship('organizer', 'name')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('amount')
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('GHS')
                    ->numeric(),
                TextInput::make('payment_method')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('transaction_id')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('status')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('processed_at')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.event.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('amount')
                    ->money('GHS')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->limit(20),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => ['failed', 'cancelled'],
                        'info' => 'refunded',
                    ]),
                TextColumn::make('processed_at')
                    ->label('Processed Date')
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
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'paystack' => 'Paystack',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                    ]),
            ]) ->headerActions([
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
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
