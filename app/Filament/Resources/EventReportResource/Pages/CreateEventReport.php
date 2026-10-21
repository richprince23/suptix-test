<?php

namespace App\Filament\Resources\EventReportResource\Pages;

use App\Filament\Resources\EventReportResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEventReport extends CreateRecord
{
    use RedirectToList;
    protected static string $resource = EventReportResource::class;
}
