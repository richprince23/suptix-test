<?php

namespace App\Filament\SystemAdmin\Resources\DemoRequestResource\Pages;

use App\Filament\SystemAdmin\Resources\DemoRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemoRequest extends EditRecord
{
    protected static string $resource = DemoRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
