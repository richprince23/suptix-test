<?php

namespace App\Filament\SystemAdmin\Resources\PermissionResource\Pages;

use App\Filament\SystemAdmin\Resources\PermissionResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    use RedirectToList;

    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
