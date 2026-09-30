<?php

namespace App\Filament\Resources\PaymentInfoResource\Pages;

use App\Filament\Resources\PaymentInfoResource;
use App\Traits\RedirectToList;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreatePaymentInfo extends CreateRecord
{
    use RedirectToList;

    protected static string $resource = PaymentInfoResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->disabled(fn () => empty($this->data['account_name']) || !($this->data['paystack_verified'] ?? false)),
            // $this->getCreateAnotherFormAction()
            //     ->disabled(fn () => empty($this->data['account_name']) || !($this->data['paystack_verified'] ?? false)),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Log the incoming data for debugging
        Log::info('Payment info form data before create:', $data);

        // Validate that account has been verified
        if (empty($data['account_name']) || ! isset($data['paystack_verified']) || ! $data['paystack_verified']) {

            Log::warning('Payment info submission failed validation:', [
                'account_name_empty' => empty($data['account_name']),
                'paystack_verified_isset' => isset($data['paystack_verified']),
                'paystack_verified_value' => $data['paystack_verified'] ?? 'not set',
            ]);

            $this->halt();

            \Filament\Notifications\Notification::make()
                ->title('Account Verification Required')
                ->body('Please verify your account details before submitting.')
                ->danger()
                ->send();

            return $data;
        }

        // Automatically set fields for new submissions
        $data['status'] = 'pending';
        $data['submitted_at'] = now();
        $data['submitted_by'] = auth()->id();

        // Make sure these fields are preserved from verification
        if (! isset($data['paystack_verified'])) {
            $data['paystack_verified'] = true;
        }

        Log::info('Payment info data after processing:', $data);

        return $data;
    }

    // public static function canCreate(): bool
    // {
    // return false;
    // }

    protected function afterCreate(): void
    {
        // Send notifications to system admin after creation
        $this->notifyAdminOfNewSubmission();
    }

    private function notifyAdminOfNewSubmission(): void
    {
        try {
            $paymentInfo = $this->record;

            // Send SMS notification (log for now)
            Log::info('SMS notification should be sent', [
                'phone' => '+233242728115',
                'message' => "New payment info submission from {$paymentInfo->organizer->name}. Business: {$paymentInfo->business_name}. Please review in admin panel.",
            ]);

            // Send Email notification
            $adminEmail = config('mail.admin_email', 'admin@suptix.live');

            Mail::send([], [], function ($message) use ($paymentInfo, $adminEmail) {
                $message->to($adminEmail)
                    ->subject('New Payment Information Submission - Suptix')
                    ->html("
                        <h2>New Payment Information Submission</h2>
                        <p><strong>Organizer:</strong> {$paymentInfo->organizer->name}</p>
                        <p><strong>Submitted By:</strong> {$paymentInfo->submittedBy->name} ({$paymentInfo->submittedBy->email})</p>
                        <p><strong>Business Name:</strong> {$paymentInfo->business_name}</p>
                        <p><strong>Account Number:</strong> {$paymentInfo->account_number}</p>
                        <p><strong>Account Name:</strong> {$paymentInfo->account_name}</p>
                        <p><strong>Bank:</strong> {$paymentInfo->settlement_bank}</p>
                        <p><strong>Payment Channel:</strong> {$paymentInfo->payment_channel}</p>
                        <p><strong>Submitted:</strong> {$paymentInfo->submitted_at->format('Y-m-d H:i:s')}</p>

                        <p>Please review this submission in the admin panel.</p>
                    ");
            });

            // Mark as notified
            $paymentInfo->update([
                'admin_notified' => true,
                'admin_notified_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify admin of payment info submission', [
                'error' => $e->getMessage(),
                'payment_info_id' => $this->record->id,
            ]);
        }
    }
}
