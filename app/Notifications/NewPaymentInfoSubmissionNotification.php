<?php

namespace App\Notifications;

use App\Models\PaymentInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewPaymentInfoSubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PaymentInfo $paymentInfo
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail']; // SMS removed to reduce costs - admin can check email regularly
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Payment Information Submission - Suptix')
            ->greeting('New Payment Info Submission!')
            ->line('A new payment information submission requires your review.')
            ->line('**Submission Details:**')
            ->line('• **Organizer:** ' . $this->paymentInfo->organizer->name)
            ->line('• **Business Name:** ' . $this->paymentInfo->business_name)
            ->line('• **Account Number:** ' . $this->paymentInfo->account_number)
            ->line('• **Account Name:** ' . $this->paymentInfo->account_name)
            ->line('• **Bank:** ' . $this->paymentInfo->settlement_bank)
            ->line('• **Payment Channel:** ' . $this->paymentInfo->payment_channel)
            ->line('• **Submitted At:** ' . ($this->paymentInfo->submitted_at ? $this->paymentInfo->submitted_at->format('Y-m-d H:i:s') : 'N/A'))
            ->line('• **Status:** ' . ucfirst($this->paymentInfo->status))
            ->line('Please review and approve/reject this payment information in the admin panel.')
            ->action('Review in Admin Panel', config('app.url') . '/system-admin/payment-infos/' . $this->paymentInfo->id)
            ->line('Thank you for maintaining the platform!');
    }

    public function toSms(object $notifiable): string
    {
        return "💳 New Payment Info Submission!\n\n" .
               "Organizer: {$this->paymentInfo->organizer->name}\n" .
               "Business: {$this->paymentInfo->business_name}\n" .
               "Bank: {$this->paymentInfo->settlement_bank}\n" .
               "Account: {$this->paymentInfo->account_number}\n" .
               "Time: " . ($this->paymentInfo->submitted_at ? $this->paymentInfo->submitted_at->format('M j, Y H:i') : 'N/A') . "\n\n" .
               "Review needed in admin panel.";
    }
}