<?php

namespace App\Filament\SystemAdmin\Resources\RSVPResource\Pages;

use App\Filament\SystemAdmin\Resources\RSVPResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRSVP extends EditRecord
{
    protected static string $resource = RSVPResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}