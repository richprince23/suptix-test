<?php

namespace App\Filament\Widgets;

use App\Models\PaymentInfo;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class OrganizerPaymentStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.organizer-payment-status';

    protected static ?int $sort = 1;

    public function getViewData(): array
    {
        $currentOrganizer = Filament::getTenant();
        $paymentInfo = null;

        if ($currentOrganizer) {
            $paymentInfo = PaymentInfo::where('organizer_id', $currentOrganizer->id)
                ->latest()
                ->first();
        }

        // Calculate additional status information
        $hasEarnings = $paymentInfo && $paymentInfo->subaccount_balance > 0;
        $hasTransactions = $paymentInfo && $paymentInfo->total_transactions > 0;
        $needsSync = $paymentInfo && $paymentInfo->needsBalanceSyncFromPaystack();
        $isFullySetup = $paymentInfo && $paymentInfo->status === 'approved' && $paymentInfo->paystack_subaccount_code;

        return [
            'paymentInfo' => $paymentInfo,
            'hasPaymentInfo' => $paymentInfo !== null,
            'isApproved' => $paymentInfo?->status === 'approved',
            'isPending' => $paymentInfo?->status === 'pending',
            'isRejected' => $paymentInfo?->status === 'rejected',
            'hasEarnings' => $hasEarnings,
            'hasTransactions' => $hasTransactions,
            'needsSync' => $needsSync,
            'isFullySetup' => $isFullySetup,
            'lastSyncAgo' => $paymentInfo?->subaccount_balance_last_synced?->diffForHumans(),
        ];
    }

    public static function canView(): bool
    {
        // Only show to non-system admins (organizers)
        return auth()->user() && ! auth()->user()->is_system_admin;
    }
}
