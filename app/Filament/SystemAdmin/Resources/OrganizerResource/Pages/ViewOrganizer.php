<?php

namespace App\Filament\SystemAdmin\Resources\OrganizerResource\Pages;

use App\Filament\SystemAdmin\Resources\OrganizerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrganizer extends ViewRecord
{
    protected static string $resource = OrganizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
