<?php

namespace App\Filament\Resources\DemoRequestResource\Pages;

use App\Filament\Resources\DemoRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDemoRequest extends CreateRecord
{
    protected static string $resource = DemoRequestResource::class;
}
