<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'tenant' => Filament::getTenant(),
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        
        if (!$user || $user->is_system_admin) {
            return false;
        }

        // Allow users with basic creation permissions for common tasks
        return $user->can('view_dashboard') && (
            $user->can('create_events') || 
            $user->can('create_venues') || 
            $user->can('create_discounts')
        );
    }
}
