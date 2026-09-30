<?php

namespace App\Filament\SystemAdmin\Resources\DiscountResource\Pages;

use App\Filament\SystemAdmin\Resources\DiscountResource;
use App\Traits\RedirectToList;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscount extends EditRecord
{
    use RedirectToList;
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
