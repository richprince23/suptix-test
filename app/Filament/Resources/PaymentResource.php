<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Finance';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', Filament::getTenant()->id)->whereNotIn('status', ['failed', 'cancelled', 'pending', 'processing']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Information')->schema([
                    Select::make('order_id')
                        ->relationship('order', 'order_number', function (Builder $query) {
                            return $query->whereHas('event', function (Builder $query) {
                                $query->where('organizer_id', Filament::getTenant()->id);
                            });
                        })
                        ->searchable()
                        ->preload(),
                    TextInput::make('amount')
                        ->numeric()
                        ->prefix('GHS')
                        ->required(),
                    Select::make('payment_method')
                        ->options([
                            'credit_card' => 'Credit Card',
                            'debit_card' => 'Debit Card',
                            'paypal' => 'PayPal',
                            'stripe' => 'Stripe',
                            'bank_transfer' => 'Bank Transfer',
                        ])
                        ->required(),
                    TextInput::make('transaction_id')
                        ->maxLength(255),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'completed' => 'Completed',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                        ])
                        ->default('pending')
                        ->required(),
                    DateTimePicker::make('processed_at')
                        ->nullable(),
                    Textarea::make('notes')
                        ->rows(3),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('GHS')
                    ->sortable(),
                TextColumn::make('order.event.title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge(),
                TextColumn::make('transaction_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ]),
                TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'bank_transfer' => 'Bank Transfer',
                    ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Payments'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Selected'),
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('status', 'completed')->count();
    }
}
