<?php

namespace App\Filament\SystemAdmin\Resources\PaymentInfoResource\Pages;

use App\Filament\SystemAdmin\Resources\PaymentInfoResource;
use App\Services\PaystackService;
use App\Traits\RedirectToList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentInfo extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = PaymentInfoResource::class;

    protected function afterCreate(): void
    {
        // Optionally auto-create Paystack subaccount
        $paystackService = app(PaystackService::class);
        $result = $paystackService->createSubaccount($this->record);

        if ($result['success']) {
            Notification::make()
                ->title('Payment Setup Complete')
                ->body('Your Paystack subaccount has been created successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Payment Info Saved')
                ->body('Payment information saved. You can create the Paystack subaccount from the list view.')
                ->warning()
                ->send();
        }
    }
}
