<?php

namespace App\Filament\Resources\PaymentInfoResource\Pages;

use App\Filament\Resources\PaymentInfoResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPaymentInfo extends EditRecord
{
    use RedirectToList;

    protected static string $resource = PaymentInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Payment Information')
                ->modalDescription('This will permanently delete your payment information and remove the associated Paystack subaccount. You will no longer be able to receive payments. Are you sure you want to continue?')
                ->action(function () {
                    $record = $this->getRecord();
                    $subaccountCode = $record->paystack_subaccount_code;
                    $businessName = $record->business_name;
                    
                    // Soft delete the record (this will trigger Paystack deletion via model observer)
                    $record->delete();
                    
                    Notification::make()
                        ->title('Payment Information Deleted')
                        ->body("Payment info for '{$businessName}' has been deleted" . 
                               ($subaccountCode ? " and Paystack subaccount '{$subaccountCode}' has been removed." : "."))
                        ->success()
                        ->send();
                    
                    // Redirect to list page
                    return redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}
