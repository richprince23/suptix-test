<?php

namespace App\Filament\SystemAdmin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class DailyRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Revenue Trend (Current Month)';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Cache daily revenue data for 1 hour
        return Cache::remember('daily_revenue_chart_'.now()->format('Y-m'), 3600, function () {
            return $this->generateDailyRevenueData();
        });
    }

    protected function generateDailyRevenueData(): array
    {
        $currentMonth = now();
        $daysInMonth = $currentMonth->daysInMonth;

        $labels = [];
        $revenueData = [];
        $targetData = [];

        // Calculate daily target based on monthly goal
        $monthlyTarget = $this->estimateMonthlyTarget();
        $dailyTarget = $monthlyTarget / $daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonth->copy()->day($day);
            $labels[] = $date->format('M j');

            // Only get data for past days and today
            if ($date->lte(now())) {
                $dailyRevenue = Order::where('status', 'confirmed')
                    ->whereDate('purchased_at', $date)
                    ->sum('total_amount');

                $revenueData[] = (float) $dailyRevenue;
            } else {
                $revenueData[] = null; // Future days
            }

            $targetData[] = $dailyTarget;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Daily Revenue',
                    'data' => $revenueData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Daily Target',
                    'data' => $targetData,
                    'borderColor' => 'rgb(156, 163, 175)',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [5, 5],
                    'tension' => 0,
                    'pointRadius' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                            if (context.parsed.y === null) return null;
                            return context.dataset.label + ": GHS " + context.parsed.y.toLocaleString();
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Date',
                    ],
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (GHS)',
                    ],
                    'beginAtZero' => true,
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
            'elements' => [
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 6,
                ],
            ],
        ];
    }

    protected function estimateMonthlyTarget(): float
    {
        // Get average revenue from last 3 months to estimate target
        $monthlyRevenues = [];

        for ($i = 1; $i <= 3; $i++) {
            $date = now()->subMonths($i);
            $monthlyRevenue = Order::where('status', 'confirmed')
                ->whereYear('purchased_at', $date->year)
                ->whereMonth('purchased_at', $date->month)
                ->sum('total_amount');

            if ($monthlyRevenue > 0) {
                $monthlyRevenues[] = (float) $monthlyRevenue;
            }
        }

        if (empty($monthlyRevenues)) {
            return 10000; // Default target if no data
        }

        return array_sum($monthlyRevenues) / count($monthlyRevenues);
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
