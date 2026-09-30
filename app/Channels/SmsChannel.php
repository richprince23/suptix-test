<?php

namespace App\Channels;

use App\Services\HubtelSmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function __construct(
        private HubtelSmsService $hubtelSmsService
    ) {
        //
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        // Get the SMS message content
        $message = $notification->toSms($notifiable);

        // Get the phone number from the notifiable
        $phoneNumber = $notifiable->routeNotificationForSms($notification);
        
        if (! $phoneNumber) {
            Log::warning('SMS notification skipped: no phone number for notifiable', [
                'notifiable' => get_class($notifiable),
                'notification' => get_class($notification),
            ]);
            return;
        }

        // Send the SMS if Hubtel is configured
        if ($this->hubtelSmsService->isConfigured()) {
            $result = $this->hubtelSmsService->sendSms($phoneNumber, $message);
            
            if (! $result['success']) {
                Log::error('SMS notification failed to send', [
                    'phone' => $phoneNumber,
                    'error' => $result['error'] ?? 'Unknown error',
                    'notification' => get_class($notification),
                ]);
            }
        } else {
            // Fallback to logging if SMS service is not configured
            Log::info('SMS notification (service not configured)', [
                'phone' => $phoneNumber,
                'message' => $message,
                'notification' => get_class($notification),
            ]);
        }
    }
}
