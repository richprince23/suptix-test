<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = PaymentResource::class;
}
