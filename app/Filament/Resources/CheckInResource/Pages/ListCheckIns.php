<?php

namespace App\Filament\Resources\CheckInResource\Pages;

use App\Filament\Resources\CheckInResource;
use Filament\Resources\Pages\ListRecords;

class ListCheckIns extends ListRecords
{
    protected static string $resource = CheckInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action since check-ins are created through the check-in process
        ];
    }

    public function getTitle(): string
    {
        return 'Check-In Records';
    }

    public function getHeading(): string
    {
        return 'Check-In Records';
    }

    public function getSubheading(): string
    {
        return 'View all checked-in attendees across your events';
    }
}
