<?php

namespace App\Filament\SystemAdmin\Resources\DemoRequestResource\Pages;

use App\Filament\SystemAdmin\Resources\DemoRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDemoRequest extends ViewRecord
{
    protected static string $resource = DemoRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
