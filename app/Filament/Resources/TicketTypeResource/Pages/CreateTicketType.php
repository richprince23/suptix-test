<?php

namespace App\Filament\Resources\TicketTypeResource\Pages;

use App\Filament\Resources\TicketTypeResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketType extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = TicketTypeResource::class;
}
