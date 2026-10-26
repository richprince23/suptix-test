<?php

namespace App\Filament\Resources\RSVPResource\Pages;

use App\Filament\Resources\RSVPResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRSVP extends EditRecord
{
    use RedirectToList;

    protected static string $resource = RSVPResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
