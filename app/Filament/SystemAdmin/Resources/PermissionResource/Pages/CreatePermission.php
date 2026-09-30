<?php

namespace App\Filament\SystemAdmin\Resources\PermissionResource\Pages;

use App\Filament\SystemAdmin\Resources\PermissionResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = PermissionResource::class;
}
