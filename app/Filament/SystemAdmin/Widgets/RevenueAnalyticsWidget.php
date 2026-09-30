<?php

namespace App\Filament\SystemAdmin\Widgets;

use App\Models\Event;
use App\Models\Order;
use App\Models\PaymentInfo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class RevenueAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // Cache analytics data for 15 minutes
        $analytics = Cache::remember('revenue_analytics_data', 900, function () {
            return $this->calculateAdvancedMetrics();
        });

        return [
            Stat::make('Revenue Per Event', 'GHS '.number_format($analytics['revenue_per_event'], 2))
                ->description('Average revenue generated per event')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Revenue Per Organizer', 'GHS '.number_format($analytics['revenue_per_organizer'], 2))
                ->description('Average revenue per active organizer')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Platform Commission Rate', $analytics['effective_commission_rate'].'%')
                ->description('Weighted average across all transactions')
                ->descriptionIcon('heroicon-m-percent-badge')
                ->color('warning'),

            Stat::make('Top Performer Revenue', 'GHS '.number_format($analytics['top_organizer_revenue'], 2))
                ->description('Highest earning organizer this month')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('Revenue Growth Rate', $analytics['revenue_growth_rate'])
                ->description('Quarterly growth trend')
                ->descriptionIcon($analytics['growth_icon'])
                ->color($analytics['growth_color']),

            Stat::make('Payment Success Rate', $analytics['payment_success_rate'].'%')
                ->description('Successful payment completion rate')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($analytics['payment_success_rate'] > 95 ? 'success' : 'warning'),
        ];
    }

    protected function calculateAdvancedMetrics(): array
    {
        // Get all payment infos (including soft deleted for accurate financial reporting)
        $paymentInfos = PaymentInfo::withTrashed()
            ->whereNotNull('paystack_subaccount_code')
            ->with('organizer')
            ->get();

        // Basic calculations
        $totalRevenue = $paymentInfos->sum('total_transaction_volume');
        $totalEvents = Event::count();
        $activeOrganizers = $paymentInfos->where('total_transactions', '>', 0)->where('deleted_at', null)->count();

        // Revenue per event
        $revenuePerEvent = $totalEvents > 0 ? $totalRevenue / $totalEvents : 0;

        // Revenue per organizer
        $revenuePerOrganizer = $activeOrganizers > 0 ? $totalRevenue / $activeOrganizers : 0;

        // Effective commission rate (weighted average)
        $totalVolume = $paymentInfos->sum('total_transaction_volume');
        $totalCommission = $paymentInfos->sum('system_admin_balance');
        $effectiveCommissionRate = $totalVolume > 0 ? round(($totalCommission / $totalVolume) * 100, 1) : 0;

        // Top performer this month
        $topOrganizerRevenue = $this->getTopOrganizerRevenueThisMonth();

        // Revenue growth rate
        $growthMetrics = $this->calculateRevenueGrowthRate();

        // Payment success rate
        $paymentSuccessRate = $this->calculatePaymentSuccessRate();

        return [
            'revenue_per_event' => $revenuePerEvent,
            'revenue_per_organizer' => $revenuePerOrganizer,
            'effective_commission_rate' => $effectiveCommissionRate,
            'top_organizer_revenue' => $topOrganizerRevenue,
            'revenue_growth_rate' => $growthMetrics['rate'],
            'growth_icon' => $growthMetrics['icon'],
            'growth_color' => $growthMetrics['color'],
            'payment_success_rate' => $paymentSuccessRate,
        ];
    }

    protected function getTopOrganizerRevenueThisMonth(): float
    {
        $currentMonth = now();

        $topRevenue = Order::join('organizers', 'orders.organizer_id', '=', 'organizers.id')
            ->where('orders.status', 'confirmed')
            ->whereYear('orders.purchased_at', $currentMonth->year)
            ->whereMonth('orders.purchased_at', $currentMonth->month)
            ->groupBy('organizers.id')
            ->selectRaw('SUM(orders.total_amount) as organizer_revenue')
            ->orderBy('organizer_revenue', 'desc')
            ->first();

        return $topRevenue ? (float) $topRevenue->organizer_revenue : 0;
    }

    protected function calculateRevenueGrowthRate(): array
    {
        // Compare this quarter vs last quarter
        $currentQuarter = $this->getQuarterlyRevenue(0); // Current quarter
        $lastQuarter = $this->getQuarterlyRevenue(1); // Last quarter
        $previousQuarter = $this->getQuarterlyRevenue(2); // Quarter before last

        if ($lastQuarter == 0) {
            if ($currentQuarter > 0) {
                return [
                    'rate' => '100%+ growth',
                    'icon' => 'heroicon-m-arrow-trending-up',
                    'color' => 'success',
                ];
            }

            return [
                'rate' => 'No data',
                'icon' => 'heroicon-m-minus',
                'color' => 'gray',
            ];
        }

        $growthRate = (($currentQuarter - $lastQuarter) / $lastQuarter) * 100;

        // Determine trend over multiple quarters for future analytics expansion
        $trend = 'stable';
        if ($previousQuarter > 0) {
            $previousGrowth = (($lastQuarter - $previousQuarter) / $previousQuarter) * 100;
            if ($growthRate > $previousGrowth) {
                $trend = 'accelerating';
            } elseif ($growthRate < $previousGrowth) {
                $trend = 'decelerating';
            }
        }

        // Note: $trend variable is available for future analytics features

        if ($growthRate > 0) {
            return [
                'rate' => '+'.number_format($growthRate, 1).'% QoQ',
                'icon' => 'heroicon-m-arrow-trending-up',
                'color' => 'success',
            ];
        } elseif ($growthRate < 0) {
            return [
                'rate' => number_format($growthRate, 1).'% QoQ',
                'icon' => 'heroicon-m-arrow-trending-down',
                'color' => 'danger',
            ];
        } else {
            return [
                'rate' => '0% QoQ',
                'icon' => 'heroicon-m-minus',
                'color' => 'gray',
            ];
        }
    }

    protected function getQuarterlyRevenue(int $quartersAgo): float
    {
        $quarterStart = now()->subQuarters($quartersAgo)->startOfQuarter();
        $quarterEnd = now()->subQuarters($quartersAgo)->endOfQuarter();

        return Order::where('status', 'confirmed')
            ->whereBetween('purchased_at', [$quarterStart, $quarterEnd])
            ->sum('total_amount');
    }

    protected function calculatePaymentSuccessRate(): float
    {
        $totalAttempts = Order::count();
        $successfulPayments = Order::where('status', 'confirmed')->count();

        if ($totalAttempts == 0) {
            return 0;
        }

        return round(($successfulPayments / $totalAttempts) * 100, 1);
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
