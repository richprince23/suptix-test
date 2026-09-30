<?php

namespace App\Filament\SystemAdmin\Resources\OrganizerResource\Pages;

use App\Filament\SystemAdmin\Resources\OrganizerResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizer extends EditRecord
{
    use RedirectToList;

    protected static string $resource = OrganizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
