<?php

namespace App\Filament\SystemAdmin\Resources\OrderResource\Pages;

use App\Filament\SystemAdmin\Resources\OrderResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = OrderResource::class;
}
