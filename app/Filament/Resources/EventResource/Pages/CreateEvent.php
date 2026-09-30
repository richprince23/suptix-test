<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = EventResource::class;

}
