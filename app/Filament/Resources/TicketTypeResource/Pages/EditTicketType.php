<?php

namespace App\Filament\Resources\TicketTypeResource\Pages;

use App\Filament\Resources\TicketTypeResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketType extends EditRecord
{
    use RedirectToList;

    protected static string $resource = TicketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
