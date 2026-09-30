<?php

namespace App\Filament\Widgets;

use App\Services\PaystackBalanceService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class BalanceSummaryWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Cache the balance summary for 5 minutes to avoid too many API calls
        $balanceSummary = Cache::remember('system_balance_summary', 300, function () {
            $balanceService = new PaystackBalanceService;

            return $balanceService->getSystemBalanceSummary();
        });

        // Get additional real-time stats from database
        $realtimeStats = Cache::remember('realtime_balance_stats', 60, function () {
            $paymentInfos = \App\Models\PaymentInfo::where('is_active', true)
                ->whereRaw('status = ?', ['approved'])
                ->get();

            $needingSyncCount = $paymentInfos->filter(function ($info) {
                return $info->needsBalanceSyncFromPaystack();
            })->count();

            $lastSyncTimes = $paymentInfos->whereNotNull('subaccount_balance_last_synced')
                ->pluck('subaccount_balance_last_synced')
                ->filter();

            $oldestSync = $lastSyncTimes->min();
            $newestSync = $lastSyncTimes->max();

            return [
                'accounts_needing_sync' => $needingSyncCount,
                'oldest_sync' => $oldestSync,
                'newest_sync' => $newestSync,
                'total_active_accounts' => $paymentInfos->count(),
            ];
        });

        $stats = [
            Stat::make('Total System Balance', 'GHS '.number_format($balanceSummary['total_balance'], 2))
                ->description('Combined subaccount and admin balances')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Organizer Balances', 'GHS '.number_format($balanceSummary['total_subaccount_balance'], 2))
                ->description('Total organizer subaccount balances')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('System Admin Earnings', 'GHS '.number_format($balanceSummary['total_system_admin_balance'], 2))
                ->description($balanceSummary['system_admin_percentage'].'% of total volume')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),

            Stat::make('Transaction Volume', 'GHS '.number_format($balanceSummary['total_transaction_volume'], 2))
                ->description($balanceSummary['total_transactions'].' total transactions')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Active Subaccounts', $balanceSummary['active_subaccounts'].'/'.$realtimeStats['total_active_accounts'])
                ->description('With Paystack subaccounts')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($balanceSummary['active_subaccounts'] === $realtimeStats['total_active_accounts'] ? 'success' : 'warning'),

            Stat::make('Avg Transaction Value', 'GHS '.number_format($balanceSummary['average_transaction_value'], 2))
                ->description('Average per transaction')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray'),
        ];

        // Add sync status stats if there are accounts needing sync
        if ($realtimeStats['accounts_needing_sync'] > 0) {
            $stats[] = Stat::make('Sync Status', $realtimeStats['accounts_needing_sync'].' need sync')
                ->description('Accounts with outdated balances')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger');
        } elseif ($realtimeStats['newest_sync']) {
            $stats[] = Stat::make('Sync Status', 'All synced')
                ->description('Last: '.$realtimeStats['newest_sync']->diffForHumans())
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        // Only show to system admins
        return auth()->user() && auth()->user()->is_system_admin;
    }
}
