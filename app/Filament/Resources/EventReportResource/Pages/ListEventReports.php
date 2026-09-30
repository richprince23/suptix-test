<?php

namespace App\Filament\Resources\EventReportResource\Pages;

use App\Filament\Resources\EventReportResource;
use App\Models\Organizer;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListEventReports extends ListRecords
{
    protected static string $resource = EventReportResource::class;

    protected function getHeaderActions(): array
    {
        $orgs = Organizer::where('id', Filament::getTenant()->id)->first();
        $count = $orgs->events()->count();

        return [

            Actions\CreateAction::make()->hidden($count < 1),
        ];
    }

}
