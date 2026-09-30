<?php

namespace App\Filament\SystemAdmin\Resources\ReservationResource\Pages;

use App\Filament\SystemAdmin\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}