<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    public string $temporaryPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $temporaryPassword)
    {
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = route('filament.admin.auth.login');

        return (new MailMessage)
            ->subject('Welcome to '.config('app.name').' - Your Account Details')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('An account has been created for you on '.config('app.name').'.')
            ->line('Here are your login credentials:')
            ->line('**Email:** '.$notifiable->email)
            ->line('**Temporary Password:** '.$this->temporaryPassword)
            ->line('For security reasons, you will be required to change your password when you first log in.')
            ->action('Login to Your Account', $loginUrl)
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for joining us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'temporary_password' => $this->temporaryPassword,
            'login_url' => route('filament.admin.auth.login'),
        ];
    }
}
