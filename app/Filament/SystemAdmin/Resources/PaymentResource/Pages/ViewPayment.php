<?php

namespace App\Filament\SystemAdmin\Resources\PaymentResource\Pages;

use App\Filament\SystemAdmin\Resources\PaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
