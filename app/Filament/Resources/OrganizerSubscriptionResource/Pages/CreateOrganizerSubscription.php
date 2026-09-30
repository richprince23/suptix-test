<?php

namespace App\Filament\Resources\OrganizerSubscriptionResource\Pages;

use App\Filament\Resources\OrganizerSubscriptionResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizerSubscription extends CreateRecord
{
    use RedirectToList;
    protected static string $resource = OrganizerSubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organizer_id'] = Filament::getTenant()->id;
        return $data;
    }
}
