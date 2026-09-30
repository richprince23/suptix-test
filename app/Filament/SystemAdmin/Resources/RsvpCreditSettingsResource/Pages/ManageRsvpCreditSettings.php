<?php

namespace App\Filament\SystemAdmin\Resources\RsvpCreditSettingsResource\Pages;

use App\Filament\SystemAdmin\Resources\RsvpCreditSettingsResource;
use App\Models\RsvpCreditSettings;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ManageRsvpCreditSettings extends ListRecords
{
    protected static string $resource = RsvpCreditSettingsResource::class;

    public function mount(): void
    {
        // Ensure singleton row exists before listing
        RsvpCreditSettings::current();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Edit Settings'),
        ];
    }
}
