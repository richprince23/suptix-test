<?php

namespace App\Filament\Widgets;

use App\Models\PaymentInfo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingPaymentSubmissionsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingCount = PaymentInfo::where('status', 'pending')->count();
        $rejectedCount = PaymentInfo::where('status', 'rejected')->count();

        // Get approved payment infos using raw status field
        $approvedInfos = PaymentInfo::where('is_active', true)
            ->whereRaw('status = ?', ['approved'])
            ->get();

        $approvedCount = $approvedInfos->count();

        // Enhanced statistics for balance tracking
        $approvedWithSubaccounts = $approvedInfos
            ->whereNotNull('paystack_subaccount_code')
            ->count();

        $approvedWithBalances = $approvedInfos
            ->where('subaccount_balance', '>', 0)
            ->count();

        $needingSyncCount = $approvedInfos
            ->whereNotNull('paystack_subaccount_code')
            ->filter(function ($info) {
                return $info->needsBalanceSyncFromPaystack();
            })
            ->count();

        $stats = [
            Stat::make('Pending Submissions', $pendingCount)
                ->description('Awaiting admin review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(\App\Filament\Resources\PaymentInfoResource::getUrl('index')),

            Stat::make('Approved Accounts', $approvedCount)
                ->description($approvedWithSubaccounts.' with Paystack subaccounts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Active Earners', $approvedWithBalances)
                ->description('Accounts with positive balances')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];

        // Add rejected count if there are any
        if ($rejectedCount > 0) {
            $stats[] = Stat::make('Rejected Submissions', $rejectedCount)
                ->description('Need revision or resubmission')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger');
        }

        // Add sync status if there are accounts needing sync
        if ($needingSyncCount > 0) {
            $stats[] = Stat::make('Sync Required', $needingSyncCount)
                ->description('Accounts need balance sync')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        // Only show to system admins
        return auth()->user()?->is_system_admin ?? false;
    }
}
