<?php

namespace App\Filament\Widgets;

use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class EarningsOverTimeWidget extends ChartWidget
{
    protected static ?string $heading = 'Your Earnings Overview';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $currentOrganizer = Filament::getTenant();

        if (! $currentOrganizer) {
            return $this->getEmptyData();
        }

        $paymentInfo = PaymentInfo::where('organizer_id', $currentOrganizer->id)
            ->where('is_active', true)
            ->whereRaw('status = ?', ['approved'])
            ->first();

        if (! $paymentInfo) {
            return $this->getEmptyData();
        }

        // Since we don't have historical balance data, we'll show current distribution
        $subaccountBalance = (float) $paymentInfo->subaccount_balance;
        $systemAdminBalance = (float) $paymentInfo->system_admin_balance;
        $totalVolume = (float) $paymentInfo->total_transaction_volume;

        // Calculate remaining potential earnings (if all pending transactions succeed)
        $pendingAmount = $totalVolume - ($subaccountBalance + $systemAdminBalance);

        return [
            'datasets' => [
                [
                    'label' => 'Earnings Breakdown',
                    'data' => [
                        round($subaccountBalance, 2),
                        round($systemAdminBalance, 2),
                        round($pendingAmount, 2),
                    ],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',  // Green for your earnings
                        'rgb(251, 146, 60)', // Orange for platform fees
                        'rgb(156, 163, 175)', // Gray for pending/processing
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                        'rgb(156, 163, 175)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Your Earnings (GHS '.number_format($subaccountBalance, 2).')',
                'Platform Fees (GHS '.number_format($systemAdminBalance, 2).')',
                'Processing/Pending (GHS '.number_format($pendingAmount, 2).')',
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
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((context.parsed / total) * 100);
                            return context.label + " (" + percentage + "%)";
                        }',
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Revenue Distribution',
                    'font' => [
                        'size' => 16,
                        'weight' => 'bold',
                    ],
                ],
            ],
        ];
    }

    private function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'No Data',
                    'data' => [1],
                    'backgroundColor' => ['#e5e7eb'],
                    'borderColor' => ['#e5e7eb'],
                ],
            ],
            'labels' => ['No earnings data available'],
        ];
    }

    public static function canView(): bool
    {
        // Only show to non-system admins (organizers) who have payment info
        if (! auth()->user() || auth()->user()->is_system_admin) {
            return false;
        }

        $currentOrganizer = Filament::getTenant();
        if (! $currentOrganizer) {
            return false;
        }

        $paymentInfo = PaymentInfo::where('organizer_id', $currentOrganizer->id)
            ->where('is_active', true)
            ->whereRaw('status = ?', ['approved'])
            ->first();

        return $paymentInfo !== null;
    }
}
