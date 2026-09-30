<?php

namespace App\Filament\Resources\PaymentInfoResource\Pages;

use App\Filament\Resources\PaymentInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentInfo extends ViewRecord
{
    protected static string $resource = PaymentInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
