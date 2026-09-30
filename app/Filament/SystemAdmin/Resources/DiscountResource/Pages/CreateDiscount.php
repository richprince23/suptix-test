<?php

namespace App\Filament\SystemAdmin\Resources\DiscountResource\Pages;

use App\Filament\SystemAdmin\Resources\DiscountResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    use RedirectToList;
    protected static string $resource = DiscountResource::class;
}
