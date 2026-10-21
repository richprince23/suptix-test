<?php

namespace App\Filament\Resources\RSVPResource\Pages;

use App\Filament\Resources\RSVPResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRSVP extends ViewRecord
{
    protected static string $resource = RSVPResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}