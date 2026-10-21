<?php

namespace App\Filament\Resources\RSVPResource\Pages;

use App\Filament\Resources\RSVPResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateRSVP extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = RSVPResource::class;
}
