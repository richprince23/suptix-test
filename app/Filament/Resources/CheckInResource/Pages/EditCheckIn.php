<?php

namespace App\Filament\Resources\CheckInResource\Pages;

use App\Filament\Resources\CheckInResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckIn extends EditRecord
{
    use RedirectToList;

    protected static string $resource = CheckInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
