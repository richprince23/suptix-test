<?php

namespace App\Filament\SystemAdmin\Resources\NewsletterResource\Pages;

use App\Filament\SystemAdmin\Resources\NewsletterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletter extends CreateRecord
{
    protected static string $resource = NewsletterResource::class;
}
