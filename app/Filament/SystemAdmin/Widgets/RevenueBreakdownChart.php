<?php

namespace App\Filament\SystemAdmin\Widgets;

use App\Models\Order;
use App\Models\PaymentInfo;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class RevenueBreakdownChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Breakdown by Source';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '400px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Cache chart data for 10 minutes
        return Cache::remember('revenue_breakdown_chart', 600, function () {
            return $this->generateChartData();
        });
    }

    protected function generateChartData(): array
    {
        // Get monthly revenue data for the last 12 months
        $monthlyData = [];
        $labels = [];
        $adminRevenue = [];
        $organizerRevenue = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            // Get orders for this month
            $monthlyOrders = Order::where('status', 'confirmed')
                ->whereYear('purchased_at', $date->year)
                ->whereMonth('purchased_at', $date->month)
                ->sum('total_amount');

            // Calculate estimated splits (using average platform fee)
            $averagePlatformFee = $this->getAveragePlatformFee();
            $adminEarnings = $monthlyOrders * ($averagePlatformFee / 100);
            $organizerEarnings = $monthlyOrders - $adminEarnings;

            $adminRevenue[] = round($adminEarnings, 2);
            $organizerRevenue[] = round($organizerEarnings, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'System Admin Revenue',
                    'data' => $adminRevenue,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.8)', // Orange
                    'borderColor' => 'rgb(251, 146, 60)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Organizer Revenue',
                    'data' => $organizerRevenue,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)', // Green
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": GHS " + context.parsed.y.toLocaleString();
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (GHS)',
                    ],
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "GHS " + value.toLocaleString();
                        }',
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }

    protected function getAveragePlatformFee(): float
    {
        $averageFee = PaymentInfo::withTrashed()
            ->whereNotNull('paystack_subaccount_code')
            ->avg('percentage_charge');

        return $averageFee ?: 7.0; // Default to 7% if no data
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
