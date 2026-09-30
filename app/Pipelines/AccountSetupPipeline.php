<?php

namespace App\Pipelines;

use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\User;
use App\Pipelines\AccountSetup\AssignOrganizerAdminRole;
use App\Pipelines\AccountSetup\AutoApprovePaymentInfo;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;

class AccountSetupPipeline
{
    /**
     * Execute the account setup pipeline after payment info submission.
     *
     * @param  \App\Models\Organizer  $organizer
     * @param  \App\Models\User  $user
     * @param  \App\Models\PaymentInfo  $paymentInfo
     * @return array
     */
    public static function execute(Organizer $organizer, User $user, PaymentInfo $paymentInfo): array
    {
        Log::info('Starting AccountSetupPipeline after payment info submission', [
            'organizer_id' => $organizer->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'payment_info_id' => $paymentInfo->id,
        ]);

        $data = [
            'organizer' => $organizer,
            'user' => $user,
            'payment_info' => $paymentInfo,
        ];

        try {
            $result = app(Pipeline::class)
                ->send($data)
                ->through([
                    AutoApprovePaymentInfo::class, // Auto-approve with default settings (7% charge, account bears fees)
                    AssignOrganizerAdminRole::class, // Auto-assign Organizer Admin role to first user
                ])
                ->thenReturn();

            Log::info('AccountSetupPipeline completed successfully', [
                'organizer_id' => $organizer->id,
                'user_id' => $user->id,
                'payment_info_id' => $paymentInfo->id,
                'role_assigned' => $result['role_assigned'] ?? false,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('AccountSetupPipeline failed', [
                'organizer_id' => $organizer->id,
                'user_id' => $user->id,
                'payment_info_id' => $paymentInfo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
