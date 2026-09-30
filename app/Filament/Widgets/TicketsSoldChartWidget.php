<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TicketsSoldChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Tickets Sold Over Time';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $organizerId = Filament::getTenant()->id;

        // Get data for the last 12 months
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');

            $ticketCount = Ticket::where('organizer_id', $organizerId)
                ->whereIn('status', ['active', 'used'])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $labels[] = $monthName;
            $data[] = $ticketCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tickets Sold',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
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

    public static function canView(): bool
    {
        $user = auth()->user();
        
        if (!$user || $user->is_system_admin) {
            return false;
        }

        // Allow users with analytics permissions
        return $user->can('view_analytics') && $user->can('view_tickets');
    }
}
