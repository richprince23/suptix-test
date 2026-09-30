<?php

namespace App\Notifications;

use App\Models\Organizer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrganizerSignupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $organizer;
    public function __construct(
         Organizer $organizer
    ) {
        $this->organizer = $organizer;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'sms'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Organizer Registration - Suptix')
            ->greeting('New Organizer Signup!')
            ->line('A new organizer has registered on the Suptix platform.')
            ->line('**Organizer Details:**')
            ->line('• **Name:** ' . $this->organizer->name)
            ->line('• **Email:** ' . $this->organizer->email)
            ->line('• **Phone:** ' . $this->organizer->phone)
            ->line('• **Location:** ' . $this->organizer->city . ', ' . $this->organizer->state)
            ->line('• **Registration Time:** ' . $this->organizer->created_at->format('Y-m-d H:i:s'))
            ->line('Please review this new organizer registration in the admin panel.')
            ->action('View in Admin Panel', config('app.url') . '/system-admin')
            ->line('Thank you for monitoring the platform!');
    }

    public function toSms(object $notifiable): string
    {
        return "🎉 New Organizer Signup!\n\n" .
               "Name: {$this->organizer->name}\n" .
               "Email: {$this->organizer->email}\n" .
               "Phone: {$this->organizer->phone}\n" .
               "Location: {$this->organizer->city}, {$this->organizer->state}\n" .
               "Time: {$this->organizer->created_at->format('M j, Y H:i')}\n\n" .
               "Check admin panel for details.";
    }
}
