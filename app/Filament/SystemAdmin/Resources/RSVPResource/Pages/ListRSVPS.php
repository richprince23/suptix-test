<?php

namespace App\Filament\SystemAdmin\Resources\RSVPResource\Pages;

use App\Filament\SystemAdmin\Resources\RSVPResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRSVPS extends ListRecords
{
    protected static string $resource = RSVPResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}