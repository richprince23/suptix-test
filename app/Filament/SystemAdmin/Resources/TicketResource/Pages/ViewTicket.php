<?php

namespace App\Filament\SystemAdmin\Resources\TicketResource\Pages;

use App\Filament\SystemAdmin\Resources\TicketResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
