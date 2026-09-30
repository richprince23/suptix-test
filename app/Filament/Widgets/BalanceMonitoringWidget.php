<?php

namespace App\Filament\Widgets;

use App\Models\PaymentInfo;
use Filament\Widgets\ChartWidget;

class BalanceMonitoringWidget extends ChartWidget
{
    protected static ?string $heading = 'Balance Distribution';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get balance distribution data
        $paymentInfos = PaymentInfo::where('is_active', true)
            ->whereRaw('status = ?', ['approved'])
            ->select(['subaccount_balance', 'system_admin_balance', 'total_transaction_volume', 'organizer_id'])
            ->with('organizer:id,name')
            ->get();

        if ($paymentInfos->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'No Data',
                        'data' => [0],
                        'backgroundColor' => ['#e5e7eb'],
                    ],
                ],
                'labels' => ['No active accounts'],
            ];
        }

        // Prepare data for pie chart showing balance distribution
        $totalSubaccountBalance = $paymentInfos->sum('subaccount_balance');
        $totalSystemAdminBalance = $paymentInfos->sum('system_admin_balance');

        return [
            'datasets' => [
                [
                    'label' => 'Balance Distribution',
                    'data' => [
                        round($totalSubaccountBalance, 2),
                        round($totalSystemAdminBalance, 2),
                    ],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)', // Green for organizer balances
                        'rgb(251, 146, 60)', // Orange for system admin balances
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Organizer Balances (GHS '.number_format($totalSubaccountBalance, 2).')',
                'System Admin Balances (GHS '.number_format($totalSystemAdminBalance, 2).')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed + " (" + Math.round((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100) + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_system_admin ?? false;
    }
}
