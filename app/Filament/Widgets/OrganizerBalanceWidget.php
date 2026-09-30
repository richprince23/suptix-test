<?php

namespace App\Filament\Widgets;

use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrganizerBalanceWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 5;

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
                Stat::make('Payment Setup', 'Not Configured')
                    ->description('Submit payment information to start earning')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        $stats = [
            Stat::make('Your Balance', $paymentInfo->formatted_subaccount_balance)
                ->description('Available from '.($paymentInfo->successful_transactions ?? 0).' transactions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Platform Fees', $paymentInfo->formatted_system_admin_balance)
                ->description($paymentInfo->percentage_charge.'% of volume ('.$paymentInfo->system_admin_percentage.'%)')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),

            Stat::make('Total Volume', $paymentInfo->formatted_total_transaction_volume)
                ->description('Lifetime transaction volume')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];

        // Enhanced transaction stats
        if ($paymentInfo->total_transactions > 0) {
            $successRate = $paymentInfo->total_transactions > 0
                ? round(($paymentInfo->successful_transactions / $paymentInfo->total_transactions) * 100, 1)
                : 0;

            $stats[] = Stat::make('Transactions', $paymentInfo->total_transactions)
                ->description($paymentInfo->successful_transactions.' successful ('.$successRate.'%)')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 85 ? 'warning' : 'danger'));
        }

        // Enhanced last transaction info
        if ($paymentInfo->last_transaction_at) {
            $daysSinceLastTransaction = $paymentInfo->last_transaction_at->diffInDays(now());
            $recentActivity = $daysSinceLastTransaction <= 7 ? 'Recent activity' : $daysSinceLastTransaction.' days ago';

            $stats[] = Stat::make('Last Transaction', $paymentInfo->last_transaction_at->format('M j'))
                ->description('GHS '.number_format((float) $paymentInfo->last_transaction_amount, 2).' • '.$recentActivity)
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysSinceLastTransaction <= 7 ? 'success' : 'gray');
        }

        // Enhanced balance sync status with action suggestions
        if ($paymentInfo->subaccount_balance_last_synced) {
            $hoursAgo = $paymentInfo->subaccount_balance_last_synced->diffInHours(now());
            $needsSync = $paymentInfo->needsBalanceSyncFromPaystack();

            if ($needsSync) {
                $syncStatus = 'Sync recommended';
                $description = 'Last synced '.$paymentInfo->subaccount_balance_last_synced->diffForHumans();
                $color = 'warning';
                $icon = 'heroicon-m-exclamation-triangle';
            } elseif ($hoursAgo < 1) {
                $syncStatus = 'Recently synced';
                $description = 'Balances are up to date';
                $color = 'success';
                $icon = 'heroicon-m-check-circle';
            } else {
                $syncStatus = 'Synced '.$paymentInfo->subaccount_balance_last_synced->diffForHumans();
                $description = 'Balances are current';
                $color = 'success';
                $icon = 'heroicon-m-arrow-path';
            }

            $stats[] = Stat::make('Balance Status', $syncStatus)
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color);
        } else {
            $stats[] = Stat::make('Balance Status', 'Never synced')
                ->description('Sync needed for accurate balances')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger');
        }

        // Add earnings summary if there's meaningful data
        if ($paymentInfo->total_transaction_volume > 0) {
            $averageEarningPerTransaction = $paymentInfo->successful_transactions > 0
                ? $paymentInfo->subaccount_balance / $paymentInfo->successful_transactions
                : 0;

            $stats[] = Stat::make('Avg Earning', 'GHS '.number_format($averageEarningPerTransaction, 2))
                ->description('Per successful transaction')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user || $user->is_system_admin) {
            return false;
        }

        // Only allow users with financial permissions - most sensitive widget
        return $user->can('view_financial_reports') && $user->can('view_payment_info');
    }
}
