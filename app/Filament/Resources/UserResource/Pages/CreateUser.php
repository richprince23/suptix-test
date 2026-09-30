<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Notifications\NewUserCredentials;
use App\Traits\RedirectToList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public $temporaryPassword;

    use RedirectToList;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a secure temporary password
        $this->temporaryPassword = Str::password(12);

        // Set the password and force renewal flag
        $data['password'] = bcrypt($this->temporaryPassword);
        $data['force_renew_password'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send notification to the new user with their temporary credentials
        $user = $this->record;

        try {
            $user->notify(new NewUserCredentials($this->temporaryPassword));

            Notification::make()
                ->title('User created successfully')
                ->body("User {$user->name} has been created and notified with temporary login credentials.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('User created but notification failed')
                ->body("User {$user->name} was created but the email notification could not be sent. Temporary password: {$this->temporaryPassword}")
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
