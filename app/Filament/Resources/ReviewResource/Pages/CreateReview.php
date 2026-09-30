<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = ReviewResource::class;
}
