<?php

namespace App\Filament\SystemAdmin\Resources\RoleResource\Pages;

use App\Filament\SystemAdmin\Resources\RoleResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use RedirectToList;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
