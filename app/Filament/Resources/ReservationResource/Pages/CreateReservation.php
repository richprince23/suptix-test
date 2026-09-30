<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = ReservationResource::class;
}
