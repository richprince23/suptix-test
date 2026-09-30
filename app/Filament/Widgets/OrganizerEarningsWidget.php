<?php

namespace App\Filament\Widgets;

use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrganizerEarningsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentOrganizer = Filament::getTenant();

        if (! $currentOrganizer) {
            return [];
        }

        $paymentInfo = PaymentInfo::where('organizer_id', $currentOrganizer->id)
            ->where('is_active', true)
            // ->whereRaw('status = ?', ['approved'])
            ->first();

        if (! $paymentInfo) {
            return [
                Stat::make('Earnings Setup', 'Payment Info Required')
                    ->description('Submit payment information to start tracking earnings')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),

                Stat::make('Getting Started', 'Setup Process')
                    ->description('1. Submit payment info → 2. Admin approval → 3. Start earning')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('info'),
            ];
        }

        // Use the exact same implementation as the PaymentInfo view
        $subaccountBalance = (float) $paymentInfo->subaccount_balance;
        $systemAdminBalance = (float) $paymentInfo->system_admin_balance;
        $totalTransactionVolume = (float) $paymentInfo->total_transaction_volume;
        $totalBalance = $subaccountBalance + $systemAdminBalance;

        $totalTransactions = $paymentInfo->total_transactions ?? 0;
        $successfulTransactions = $paymentInfo->successful_transactions ?? 0;
        $pendingTransactions = $paymentInfo->pending_transactions ?? 0;

        // Calculate success rate
        $successRate = $totalTransactions > 0
            ? round(($successfulTransactions / $totalTransactions) * 100, 1)
            : 0;

        // Calculate average earning per successful transaction
        $averageEarningPerTransaction = $successfulTransactions > 0
            ? $subaccountBalance / $successfulTransactions
            : 0;

        // Calculate platform fee percentage (what you're paying)
        $platformFeePercentage = $totalTransactionVolume > 0
            ? round(($systemAdminBalance / $totalTransactionVolume) * 100, 2)
            : ($paymentInfo->percentage_charge ?? 0);

        // Calculate your revenue percentage (what you're keeping)
        $yourRevenuePercentage = $totalTransactionVolume > 0
            ? round(($subaccountBalance / $totalTransactionVolume) * 100, 2)
            : (100 - ($paymentInfo->percentage_charge ?? 0));

        $stats = [
            Stat::make('Your Earnings', 'GHS '.number_format($subaccountBalance, 2))
                ->description("From {$successfulTransactions} successful transactions")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Transaction Volume', 'GHS '.number_format($totalTransactionVolume, 2))
                ->description("Total volume from {$totalTransactions} transactions")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Platform Fees Paid', 'GHS '.number_format($systemAdminBalance, 2))
                ->description("{$platformFeePercentage}% of your transaction volume")
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),

            Stat::make('Your Revenue Share', "{$yourRevenuePercentage}%")
                ->description('You keep '.number_format(100 - $platformFeePercentage, 1).'% of transaction volume')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            Stat::make('Transaction Success Rate', "{$successRate}%")
                ->description("{$successfulTransactions} successful, {$pendingTransactions} pending")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 85 ? 'warning' : 'danger')),

            Stat::make('Avg Earning/Transaction', 'GHS '.number_format($averageEarningPerTransaction, 2))
                ->description('Your average revenue per successful transaction')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
        ];

        // Add sync status information
        if ($paymentInfo->subaccount_balance_last_synced) {
            $hoursAgo = $paymentInfo->subaccount_balance_last_synced->diffInHours(now());
            $needsSync = $paymentInfo->needsBalanceSyncFromPaystack();

            if ($needsSync) {
                $syncStatus = 'Sync Recommended';
                $description = 'Last synced '.$paymentInfo->subaccount_balance_last_synced->diffForHumans();
                $color = 'warning';
                $icon = 'heroicon-m-exclamation-triangle';
            } elseif ($hoursAgo < 1) {
                $syncStatus = 'Recently Synced';
                $description = 'Earnings data is up to date';
                $color = 'success';
                $icon = 'heroicon-m-check-circle';
            } else {
                $syncStatus = 'Synced '.$paymentInfo->subaccount_balance_last_synced->diffForHumans();
                $description = 'Earnings data is current';
                $color = 'success';
                $icon = 'heroicon-m-arrow-path';
            }

            $stats[] = Stat::make('Sync Status', $syncStatus)
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color);
        } else {
            $stats[] = Stat::make('Sync Status', 'Never Synced')
                ->description('Contact admin to sync earnings data')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger');
        }

        // Add last transaction info if available
        if ($paymentInfo->last_transaction_at) {
            $daysSinceLastTransaction = $paymentInfo->last_transaction_at->diffInDays(now());
            $recentActivity = $daysSinceLastTransaction <= 7 ? 'Recent activity' : $daysSinceLastTransaction.' days ago';

            $stats[] = Stat::make('Last Transaction', $paymentInfo->last_transaction_at->format('M j, Y'))
                ->description('GHS '.number_format((float) $paymentInfo->last_transaction_amount, 2).' • '.$recentActivity)
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysSinceLastTransaction <= 7 ? 'success' : 'gray');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        // Only show to non-system admins (organizers)
        return auth()->user() && ! auth()->user()->is_system_admin;
    }

    protected function getColumns(): int
    {
        return 3; // Display stats in 3 columns for better layout
    }
}
