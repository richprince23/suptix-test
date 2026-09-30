<?php

namespace App\Filament\Resources\OrganizerSubscriptionResource\Pages;

use App\Filament\Resources\OrganizerSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizerSubscriptions extends ListRecords
{
    protected static string $resource = OrganizerSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
