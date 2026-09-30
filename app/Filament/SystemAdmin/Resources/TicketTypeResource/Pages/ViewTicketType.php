<?php

namespace App\Filament\SystemAdmin\Resources\TicketTypeResource\Pages;

use App\Filament\SystemAdmin\Resources\TicketTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTicketType extends ViewRecord
{
    protected static string $resource = TicketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}