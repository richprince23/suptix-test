<?php

namespace App\Filament\SystemAdmin\Resources\EventReportResource\Pages;

use App\Filament\SystemAdmin\Resources\EventReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventReport extends EditRecord
{
    protected static string $resource = EventReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}