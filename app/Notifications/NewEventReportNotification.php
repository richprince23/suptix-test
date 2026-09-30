<?php

namespace App\Notifications;

use App\Models\EventReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEventReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EventReport $eventReport
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail']; // SMS removed to reduce costs - email is sufficient for non-urgent reports
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Issue Report for Your Event - Suptix')
            ->greeting('Event Issue Reported!')
            ->line('A new issue has been reported for one of your events.')
            ->line('**Report Details:**')
            ->line('• **Event:** ' . $this->eventReport->event->name)
            ->line('• **Report Type:** ' . ucwords(str_replace('_', ' ', $this->eventReport->report_type)))
            ->line('• **Priority:** ' . ucfirst($this->eventReport->priority))
            ->line('• **Subject:** ' . $this->eventReport->subject)
            ->line('• **Reporter:** ' . $this->eventReport->reporter)
            ->line('• **Reporter Email:** ' . $this->eventReport->reporter_email_address)
            ->line('• **Reported At:** ' . $this->eventReport->created_at->format('Y-m-d H:i:s'))
            ->line('')
            ->line('**Description:**')
            ->line($this->eventReport->description)
            ->line('')
            ->line('Please review this report and take appropriate action in your organizer dashboard.')
            ->action('View Report in Dashboard', $this->getDashboardUrl())
            ->line('Thank you for maintaining high-quality events on Suptix!');
    }

    public function toSms(object $notifiable): string
    {
        return "🚨 Event Report Alert!\n\n" .
               "Event: {$this->eventReport->event->name}\n" .
               "Type: " . ucwords(str_replace('_', ' ', $this->eventReport->report_type)) . "\n" .
               "Priority: " . ucfirst($this->eventReport->priority) . "\n" .
               "Reporter: {$this->eventReport->reporter}\n" .
               "Time: {$this->eventReport->created_at->format('M j, Y H:i')}\n\n" .
               "Check your organizer dashboard for details.";
    }

    private function getDashboardUrl(): string
    {
        return config('app.url') . '/organizer/event-reports/' . $this->eventReport->id;
    }
}