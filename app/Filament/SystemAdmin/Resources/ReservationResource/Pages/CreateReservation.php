<?php

namespace App\Filament\SystemAdmin\Resources\ReservationResource\Pages;

use App\Filament\SystemAdmin\Resources\ReservationResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    use RedirectToList;
    protected static string $resource = ReservationResource::class;
}
