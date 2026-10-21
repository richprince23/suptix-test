<?php

namespace App\Filament\SystemAdmin\Resources\ContactResource\Pages;

use App\Filament\SystemAdmin\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
