<?php

namespace App\Filament\SystemAdmin\Resources\RoleResource\Pages;

use App\Filament\SystemAdmin\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
