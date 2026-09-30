<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $organizerId = Filament::getTenant()->id;

        // Get organizer's payment info to show actual revenue (subaccount balance)
        $paymentInfo = PaymentInfo::where('organizer_id', $organizerId)
            ->where('is_active', true)
            // ->whereRaw('status = ?', ['approved'])
            ->first();

        if (! $paymentInfo) {
            // If no payment info, show order-based stats with notice
            return [
                Stat::make('Revenue Setup', 'Payment Info Required')
                    ->description('Submit payment information to track actual earnings')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),

                Stat::make('Orders This Month', Order::where('organizer_id', $organizerId)
                    ->where('status', 'confirmed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count())
                    ->description('Confirmed orders')
                    ->descriptionIcon('heroicon-m-shopping-bag')
                    ->color('info'),

                Stat::make('Pending Orders', Order::where('organizer_id', $organizerId)
                    ->where('status', 'pending')
                    ->count())
                    ->description('Awaiting confirmation')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),
            ];
        }

        // Calculate actual organizer revenue based on subaccount balance and transactions
        $totalOrganizerRevenue = $paymentInfo->subaccount_balance ?? 0;

        // Since we don't have monthly breakdown of subaccount balance,
        // we'll use transaction counts for monthly comparisons
        $thisMonthSuccessfulTransactions = $paymentInfo->successful_transactions ?? 0;
        $totalTransactions = $paymentInfo->total_transactions ?? 0;

        // Calculate average earning per transaction
        $averageEarningPerTransaction = $thisMonthSuccessfulTransactions > 0
            ? $totalOrganizerRevenue / $thisMonthSuccessfulTransactions
            : 0;

        // Get order stats for comparison
        $thisMonthOrders = Order::where('organizer_id', $organizerId)
            ->where('status', 'confirmed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $lastMonthOrders = Order::where('organizer_id', $organizerId)
            ->where('status', 'confirmed')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        // Calculate order change percentage
        $orderChange = $lastMonthOrders > 0
            ? (($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100
            : ($thisMonthOrders > 0 ? 100 : 0);

        $pendingOrders = Order::where('organizer_id', $organizerId)
            ->where('status', 'pending')
            ->count();

        // Calculate success rate
        $successRate = $totalTransactions > 0
            ? round(($thisMonthSuccessfulTransactions / $totalTransactions) * 100, 1)
            : 0;

        return [
            Stat::make('Your Total Earnings', 'GHS '.number_format($totalOrganizerRevenue, 2))
                ->description('Actual revenue from '.$thisMonthSuccessfulTransactions.' successful transactions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('This Month\'s Orders', $thisMonthOrders)
                ->description(
                    $orderChange >= 0
                        ? number_format(abs($orderChange), 1).'% increase from last month'
                        : number_format(abs($orderChange), 1).'% decrease from last month'
                )
                ->descriptionIcon(
                    $orderChange >= 0
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->color($orderChange >= 0 ? 'success' : 'danger'),

            Stat::make('Avg Earning/Transaction', 'GHS '.number_format($averageEarningPerTransaction, 2))
                ->description('Your average revenue per successful transaction')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Transaction Success Rate', $successRate.'%')
                ->description($thisMonthSuccessfulTransactions.' of '.$totalTransactions.' transactions successful')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 85 ? 'warning' : 'danger')),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('Platform Commission', 'GHS '.number_format($paymentInfo->system_admin_balance ?? 0, 2))
                ->description($paymentInfo->percentage_charge.'% platform fee from your sales')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user || $user->is_system_admin) {
            return false;
        }

        // Only allow users with financial permissions
        return $user->can('view_financial_reports') || $user->can('view_payments');
    }
}
