<?php

namespace App\Filament\Resources\CheckInResource\Pages;

use App\Filament\Resources\CheckInResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckIn extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = CheckInResource::class;
}
