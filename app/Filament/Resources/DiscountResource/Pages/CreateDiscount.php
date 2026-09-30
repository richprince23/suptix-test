<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = DiscountResource::class;
}
