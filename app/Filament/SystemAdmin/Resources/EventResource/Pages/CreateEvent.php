<?php

namespace App\Filament\SystemAdmin\Resources\EventResource\Pages;

use App\Filament\SystemAdmin\Resources\EventResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = EventResource::class;
}
