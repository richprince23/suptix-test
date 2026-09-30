<?php

namespace App\Filament\SystemAdmin\Resources\UserResource\Pages;

use App\Filament\SystemAdmin\Resources\UserResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }
}
