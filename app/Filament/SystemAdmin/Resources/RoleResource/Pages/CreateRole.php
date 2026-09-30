<?php

namespace App\Filament\SystemAdmin\Resources\RoleResource\Pages;

use App\Filament\SystemAdmin\Resources\RoleResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = RoleResource::class;
}
