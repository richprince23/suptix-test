<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class SystemAdmin
{
    use Notifiable;

    public function __construct(
        protected string $email = 'richardkns7@gmail.com',
        protected string $phone = '0242728115'
    ) {
        //
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function routeNotificationForSms(): string
    {
        return $this->phone;
    }

    public function getKey(): string
    {
        return 'system-admin';
    }
}
