<?php

namespace App\Filament\SystemAdmin\Resources\PaymentInfoResource\Pages;

use App\Filament\SystemAdmin\Resources\PaymentInfoResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentInfo extends EditRecord
{
    use RedirectToList;

    protected static string $resource = PaymentInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
