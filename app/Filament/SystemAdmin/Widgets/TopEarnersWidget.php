<?php

namespace App\Filament\SystemAdmin\Widgets;

use App\Models\PaymentInfo;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopEarnersWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Revenue Generators';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Get top earning organizers based on total transaction volume (show only non-deleted for current leaderboard)
                PaymentInfo::query()
                    ->where('is_active', true)
                    ->whereNotNull('paystack_subaccount_code')
                    ->where('total_transaction_volume', '>', 0)
                    ->with(['organizer'])
                    ->orderBy('total_transaction_volume', 'desc')
                    ->limit(10)
            )
            ->filters([
                // Filter for only approved payment infos
                Tables\Filters\Filter::make('approved_only')
                    ->query(function ($query) {
                        // Since we can't filter by the computed status in SQL, we'll handle this differently
                        // For now, we'll show all active records and rely on the limit
                        return $query;
                    })
                    ->default(true),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_transaction_volume')
                    ->label('Total Volume')
                    ->formatStateUsing(fn ($state) => 'GHS '.number_format((float) $state, 2))
                    ->sortable()
                    ->description(fn (PaymentInfo $record) => number_format($record->total_transactions).' transactions'),

                Tables\Columns\TextColumn::make('subaccount_balance')
                    ->label('Organizer Earnings')
                    ->formatStateUsing(fn ($state) => 'GHS '.number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('system_admin_balance')
                    ->label('Platform Fees')
                    ->formatStateUsing(fn ($state) => 'GHS '.number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentage_charge')
                    ->label('Fee Rate')
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('successful_transactions')
                    ->label('Success Rate')
                    ->formatStateUsing(function (PaymentInfo $record) {
                        if ($record->total_transactions == 0) {
                            return '0%';
                        }
                        $rate = ($record->successful_transactions / $record->total_transactions) * 100;

                        return number_format($rate, 1).'%';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_transaction_at')
                    ->label('Last Activity')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('No transactions'),

                Tables\Columns\BadgeColumn::make('performance_tier')
                    ->label('Tier')
                    ->getStateUsing(function (PaymentInfo $record) {
                        $volume = (float) $record->total_transaction_volume;
                        if ($volume >= 50000) {
                            return 'Platinum';
                        }
                        if ($volume >= 20000) {
                            return 'Gold';
                        }
                        if ($volume >= 5000) {
                            return 'Silver';
                        }
                        if ($volume >= 1000) {
                            return 'Bronze';
                        }

                        return 'Starter';
                    })
                    ->colors([
                        'primary' => 'Platinum',
                        'warning' => 'Gold',
                        'info' => 'Silver',
                        'secondary' => 'Bronze',
                        'gray' => 'Starter',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-m-eye')
                    ->url(fn (PaymentInfo $record) => \App\Filament\SystemAdmin\Resources\PaymentInfoResource::getUrl('view', ['record' => $record])
                    ),
            ])
            ->defaultSort('total_transaction_volume', 'desc')
            ->striped()
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
