<?php

namespace App\Filament\SystemAdmin\Resources\PaymentInfoResource\Pages;

use App\Filament\SystemAdmin\Resources\PaymentInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentInfo extends ViewRecord
{
    protected static string $resource = PaymentInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
