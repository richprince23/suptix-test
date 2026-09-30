<?php

namespace App\Filament\Resources\OrganizerSubscriptionResource\Pages;

use App\Filament\Resources\OrganizerSubscriptionResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizerSubscription extends EditRecord
{
    use RedirectToList;
    protected static string $resource = OrganizerSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
