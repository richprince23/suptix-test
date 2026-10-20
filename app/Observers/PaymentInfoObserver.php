<?php

namespace App\Observers;

use App\Jobs\AssignOrganizerAdminRole;
use App\Models\PaymentInfo;
use App\Models\SystemAdmin;
use App\Notifications\NewPaymentInfoSubmissionNotification;
use Illuminate\Support\Facades\Log;

class PaymentInfoObserver
{
    /**
     * Handle the PaymentInfo "created" event.
     */
    public function created(PaymentInfo $paymentInfo): void
    {
        // Notify system admin when payment info is submitted
        try {
            $systemAdmin = new SystemAdmin();
            $systemAdmin->notify(new NewPaymentInfoSubmissionNotification($paymentInfo));

            Log::info('System admin notified of new payment info submission', [
                'payment_info_id' => $paymentInfo->id,
                'organizer_id' => $paymentInfo->organizer_id,
                'business_name' => $paymentInfo->business_name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify system admin of payment info submission', [
                'error' => $e->getMessage(),
                'payment_info_id' => $paymentInfo->id,
                'organizer_id' => $paymentInfo->organizer_id,
            ]);
        }

        // Trigger automated account setup pipeline after payment info submission
        try {
            $organizer = $paymentInfo->organizer;
            $user = $paymentInfo->submittedBy;

            if ($organizer && $user) {
                \App\Pipelines\AccountSetupPipeline::execute($organizer, $user, $paymentInfo);

                Log::info('Account setup pipeline triggered after payment info submission', [
                    'payment_info_id' => $paymentInfo->id,
                    'organizer_id' => $organizer->id,
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to trigger account setup pipeline after payment info submission', [
                'payment_info_id' => $paymentInfo->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the PaymentInfo "updated" event.
     */
    public function updated(PaymentInfo $paymentInfo): void
    {
        // Check if the status was changed to 'approved'
        // Read the stored status: the model's status accessor returns a computed value
        // ('inactive', 'pending_creation', ...) that is never 'approved'
        $storedStatus = $paymentInfo->getAttributes()['status'] ?? null;

        if ($paymentInfo->wasChanged('status') && $storedStatus === 'approved') {
            Log::info('PaymentInfo approved, dispatching role assignment job', [
                'payment_info_id' => $paymentInfo->id,
                'organizer_id' => $paymentInfo->organizer_id,
                'old_status' => $paymentInfo->getOriginal('status'),
                'new_status' => $storedStatus,
            ]);

            // Dispatch the job to assign organizer admin role
            AssignOrganizerAdminRole::dispatch($paymentInfo);
        }
    }
}
