<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::where('organizer_id', Filament::getTenant()->id)
                    ->with(['event', 'tickets'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Click to copy'),

                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->event->title),

                Tables\Columns\TextColumn::make('buyer_email')
                    ->label('Customer')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('total_tickets')
                    ->label('Tickets')
                    ->getStateUsing(fn ($record) => $record->tickets()->whereIn('status', ['active', 'used'])->count())
                    ->icon('heroicon-m-ticket'),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Gross Amount')
                    ->getStateUsing(fn ($record) => 'GHS '.number_format($record->total_amount, 2))
                    ->tooltip('Total order amount before platform fees')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('your_earnings')
                    ->label('Your Earnings')
                    ->getStateUsing(function ($record) {
                        $organizerId = Filament::getTenant()->id;
                        $paymentInfo = PaymentInfo::where('organizer_id', $organizerId)
                            ->where('is_active', true)
                            ->whereRaw('status = ?', ['approved'])
                            ->first();

                        if (! $paymentInfo) {
                            return 'Setup Required';
                        }

                        // Calculate organizer's share based on their percentage
                        $platformFeeRate = $paymentInfo->percentage_charge / 100;
                        $organizerShare = $record->total_amount * (1 - $platformFeeRate);

                        return 'GHS '.number_format($organizerShare, 2);
                    })
                    ->tooltip('Your revenue after platform fees')
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'processing',
                        'danger' => ['cancelled', 'refunded'],
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ordered')
                    ->dateTime('M j, H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.admin.resources.orders.view', [
                        'tenant' => Filament::getTenant(),
                        'record' => $record,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No recent orders')
            ->emptyStateDescription('Orders will appear here once customers start purchasing tickets.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->heading('Recent Orders')
            ->description('Latest 10 orders from customers')
            ->headerActions([
                Tables\Actions\Action::make('view_all')
                    ->label('View All Orders')
                    ->icon('heroicon-m-arrow-right')
                    ->url(fn () => route('filament.admin.resources.orders.index', [
                        'tenant' => Filament::getTenant(),
                    ]))
                    ->color('primary'),
            ]);
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        
        if (!$user || $user->is_system_admin) {
            return false;
        }

        // Allow users with order viewing permissions or financial access
        return $user->can('view_orders') && ($user->can('view_payments') || $user->can('view_financial_reports'));
    }
}
