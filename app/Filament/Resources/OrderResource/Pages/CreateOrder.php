<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Mail\OrderEmail;
use App\Traits\RedirectToList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateOrder extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set admin fields for orders created through dashboard
        $data['created_by_user_id'] = auth()->id();
        $data['is_admin_order'] = true;
        $data['payment_method'] = 'cash';

        // Add admin notes if not provided
        if (empty($data['admin_notes'])) {
            $user = auth()->user();
            $data['admin_notes'] = "Order created by admin user: {$user->name} ({$user->email}) on behalf of customer.";
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        $order->load(['event', 'tickets', 'organizer']);

        $emailSent = false;
        $smsSent = false;

        // Send order confirmation email for admin-created orders
        try {
            Mail::to($order->buyer_email)->sendNow(new OrderEmail($order));
            $emailSent = true;

            Log::info('Order confirmation email sent for admin-created order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_email' => $order->buyer_email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order email for admin-created order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_email' => $order->buyer_email,
                'error' => $e->getMessage(),
            ]);
        }

        // Send SMS notification if phone number is provided
        if ($order->buyer_phone) {
            try {
                $order->notify(new \App\Notifications\OrderConfirmationSms($order));
                $smsSent = true;

                Log::info('Order confirmation SMS queued for admin-created order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'buyer_phone' => $order->buyer_phone,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to queue order confirmation SMS for admin-created order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'buyer_phone' => $order->buyer_phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Show appropriate notification based on what was sent
        if ($emailSent && ($smsSent || ! $order->buyer_phone)) {
            $message = "Order {$order->order_number} created successfully.";
            $message .= " Confirmation email sent to {$order->buyer_email}.";
            if ($order->buyer_phone && $smsSent) {
                $message .= " SMS notification sent to {$order->buyer_phone}.";
            }

            Notification::make()
                ->title('Order Created & Notifications Sent')
                ->body($message)
                ->success()
                ->send();
        } else {
            $message = "Order {$order->order_number} created successfully, but ";
            $issues = [];

            if (! $emailSent) {
                $issues[] = 'email notification failed';
            }
            if ($order->buyer_phone && ! $smsSent) {
                $issues[] = 'SMS notification failed';
            }

            $message .= implode(' and ', $issues).'. You can resend notifications manually.';

            Notification::make()
                ->title('Order Created with Notification Issues')
                ->body($message)
                ->warning()
                ->send();
        }
    }
}
