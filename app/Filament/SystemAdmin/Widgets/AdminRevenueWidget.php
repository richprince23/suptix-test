<?php

namespace App\Filament\SystemAdmin\Widgets;

use App\Models\Order;
use App\Models\PaymentInfo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminRevenueWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Cache revenue data for 5 minutes to improve performance
        $revenueData = Cache::remember('admin_revenue_data', 300, function () {
            return $this->calculateRevenueMetrics();
        });

        return [
            Stat::make('Total Platform Revenue', 'GHS '.number_format($revenueData['total_platform_revenue'], 2))
                ->description('All-time platform earnings')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($revenueData['monthly_platform_revenue']),

            Stat::make('System Admin Earnings', 'GHS '.number_format($revenueData['system_admin_earnings'], 2))
                ->description($revenueData['admin_percentage'].'% of total volume')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),

            Stat::make('Organizer Earnings', 'GHS '.number_format($revenueData['organizer_earnings'], 2))
                ->description('Total organizer payouts')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('This Month Revenue', 'GHS '.number_format($revenueData['current_month_revenue'], 2))
                ->description($revenueData['month_over_month_change'])
                ->descriptionIcon($revenueData['month_trend_icon'])
                ->color($revenueData['month_trend_color']),

            Stat::make('Average Order Value', 'GHS '.number_format($revenueData['average_order_value'], 2))
                ->description('Based on '.number_format($revenueData['total_orders']).' orders')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray'),

            Stat::make('Active Revenue Streams', $revenueData['active_organizers'])
                ->description($revenueData['total_organizers'].' total organizers')
                ->descriptionIcon('heroicon-m-users')
                ->color($revenueData['active_organizers'] > 0 ? 'success' : 'danger'),
        ];
    }

    protected function calculateRevenueMetrics(): array
    {
        // Get all payment info records with balances (including soft deleted for accurate financial reporting)
        $paymentInfos = PaymentInfo::withTrashed()
            ->whereNotNull('paystack_subaccount_code')
            ->get();

        // Calculate totals
        $systemAdminEarnings = $paymentInfos->sum('system_admin_balance');
        $organizerEarnings = $paymentInfos->sum('subaccount_balance');
        $totalTransactionVolume = $paymentInfos->sum('total_transaction_volume');
        $totalPlatformRevenue = $systemAdminEarnings + $organizerEarnings;

        // Calculate admin percentage
        $adminPercentage = $totalTransactionVolume > 0
            ? round(($systemAdminEarnings / $totalTransactionVolume) * 100, 1)
            : 0;

        // Get order statistics
        $totalOrders = Order::where('status', 'confirmed')->count();
        $totalOrderValue = Order::where('status', 'confirmed')->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalOrderValue / $totalOrders : 0;

        // Get current month revenue
        $currentMonthRevenue = $this->getCurrentMonthRevenue();
        $lastMonthRevenue = $this->getLastMonthRevenue();

        // Calculate month-over-month change
        $monthOverMonthChange = $this->calculateMonthOverMonthChange($currentMonthRevenue, $lastMonthRevenue);

        // Get organizer statistics
        $totalOrganizers = \App\Models\Organizer::count();
        $activeOrganizers = $paymentInfos->where('total_transactions', '>', 0)->where('deleted_at', null)->count();

        // Get monthly revenue trend for chart (last 12 months)
        $monthlyPlatformRevenue = $this->getMonthlyRevenueChart();

        return [
            'total_platform_revenue' => $totalPlatformRevenue,
            'system_admin_earnings' => $systemAdminEarnings,
            'organizer_earnings' => $organizerEarnings,
            'admin_percentage' => $adminPercentage,
            'current_month_revenue' => $currentMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'month_over_month_change' => $monthOverMonthChange['text'],
            'month_trend_icon' => $monthOverMonthChange['icon'],
            'month_trend_color' => $monthOverMonthChange['color'],
            'average_order_value' => $averageOrderValue,
            'total_orders' => $totalOrders,
            'active_organizers' => $activeOrganizers,
            'total_organizers' => $totalOrganizers,
            'monthly_platform_revenue' => $monthlyPlatformRevenue,
        ];
    }

    protected function getCurrentMonthRevenue(): float
    {
        return Order::where('status', 'confirmed')
            ->whereYear('purchased_at', now()->year)
            ->whereMonth('purchased_at', now()->month)
            ->sum('total_amount');
    }

    protected function getLastMonthRevenue(): float
    {
        $lastMonth = now()->subMonth();

        return Order::where('status', 'confirmed')
            ->whereYear('purchased_at', $lastMonth->year)
            ->whereMonth('purchased_at', $lastMonth->month)
            ->sum('total_amount');
    }

    protected function calculateMonthOverMonthChange(float $current, float $last): array
    {
        if ($last == 0) {
            if ($current > 0) {
                return [
                    'text' => '100% increase from last month',
                    'icon' => 'heroicon-m-arrow-trending-up',
                    'color' => 'success',
                ];
            }

            return [
                'text' => 'No change from last month',
                'icon' => 'heroicon-m-minus',
                'color' => 'gray',
            ];
        }

        $percentageChange = (($current - $last) / $last) * 100;

        if ($percentageChange > 0) {
            return [
                'text' => '+'.number_format($percentageChange, 1).'% from last month',
                'icon' => 'heroicon-m-arrow-trending-up',
                'color' => 'success',
            ];
        } elseif ($percentageChange < 0) {
            return [
                'text' => number_format($percentageChange, 1).'% from last month',
                'icon' => 'heroicon-m-arrow-trending-down',
                'color' => 'danger',
            ];
        } else {
            return [
                'text' => 'No change from last month',
                'icon' => 'heroicon-m-minus',
                'color' => 'gray',
            ];
        }
    }

    protected function getMonthlyRevenueChart(): array
    {
        $monthlyData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $revenue = Order::where('status', 'confirmed')
                ->whereYear('purchased_at', $date->year)
                ->whereMonth('purchased_at', $date->month)
                ->sum('total_amount');

            $monthlyData[] = (float) $revenue;
        }

        return $monthlyData;
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
