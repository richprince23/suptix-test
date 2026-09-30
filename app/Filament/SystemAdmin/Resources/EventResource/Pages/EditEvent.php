<?php

namespace App\Filament\SystemAdmin\Resources\EventResource\Pages;

use App\Filament\SystemAdmin\Resources\EventResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    use RedirectToList;

    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
