<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\HubtelSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderConfirmationSms extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['sms'];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms($notifiable): string
    {
        return $this->buildSmsMessage();
    }

    /**
     * Build the SMS message content
     */
    private function buildSmsMessage(): string
    {
        $eventTitle = $this->order->event->title ?? 'Event';
        $orderNumber = $this->order->order_number;
        $totalAmount = number_format($this->order->total_amount, 2);
        $ticketCount = $this->order->tickets()->count();

        // Format event date and time
        $eventDate = '';
        if ($this->order->event?->start_date) {
            $eventDate = date('M j, Y', strtotime($this->order->event->start_date));
            if ($this->order->event?->start_time) {
                $eventDate .= ' at '.date('g:i A', strtotime($this->order->event->start_time));
            }
        }

        $message = "Suptix Order Confirmed!\n\n";
        $message .= "Event: {$eventTitle}\n";
        $message .= "Order: {$orderNumber}\n";
        $message .= "Tickets: {$ticketCount}\n";
        $message .= "Amount: GHS {$totalAmount}\n";

        if ($eventDate) {
            $message .= "Date: {$eventDate}\n";
        }

        $message .= "\nYour tickets will be sent via email. ";
        $message .= 'Download your tickets at: '.url("/orders/{$orderNumber}");
        $message .= "\n\nThank you for choosing Suptix!";

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'phone' => $this->order->buyer_phone,
            'event_title' => $this->order->event->title ?? 'Event',
        ];
    }
}
