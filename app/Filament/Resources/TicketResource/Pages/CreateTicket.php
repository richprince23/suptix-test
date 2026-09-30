<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = TicketResource::class;
}
