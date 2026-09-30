<?php

namespace App\Filament\SystemAdmin\Resources\OrderResource\Pages;

use App\Filament\SystemAdmin\Resources\OrderResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    use RedirectToList;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
