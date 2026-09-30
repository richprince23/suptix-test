<?php

namespace App\Filament\SystemAdmin\Resources\OrganizerResource\Pages;

use App\Filament\SystemAdmin\Resources\OrganizerResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizer extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = OrganizerResource::class;
}
