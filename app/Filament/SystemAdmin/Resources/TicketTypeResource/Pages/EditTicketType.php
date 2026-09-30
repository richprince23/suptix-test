<?php

namespace App\Filament\SystemAdmin\Resources\TicketTypeResource\Pages;

use App\Filament\SystemAdmin\Resources\TicketTypeResource;
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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
