<?php

namespace App\Pipelines\AccountSetup;

use App\Models\PaymentInfo;
use Closure;
use Illuminate\Support\Facades\Log;

class AutoApprovePaymentInfo
{
    /**
     * Handle the pipeline step to set default settings and auto-approve PaymentInfo.
     *
     * Sets:
     * - 7% as default system transaction charge
     * - Main account as bearer of transaction fees
     * - Auto-approves the payment info
     *
     * @param  array  $data
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(array $data, Closure $next)
    {
        $paymentInfo = $data['payment_info'] ?? null;
        $user = $data['user'] ?? null;

        if (!$paymentInfo instanceof PaymentInfo) {
            Log::warning('AutoApprovePaymentInfo pipeline: No PaymentInfo found', [
                'has_payment_info' => !is_null($paymentInfo),
            ]);

            return $next($data);
        }

        try {
            // Set default settings and auto-approve
            $paymentInfo->update([
                'percentage_charge' => 7.00, // 7% default system charge
                'transaction_fee_payer' => 'account', // Main account bears transaction fees
                'approved_by' => null, // System auto-approval
                'approved_at' => now(),
                'metadata' => array_merge($paymentInfo->metadata ?? [], [
                    'auto_approved' => true,
                    'auto_approved_at' => now()->toISOString(),
                    'auto_approved_reason' => 'Automated account setup via pipeline',
                    'default_percentage_charge' => 7.00,
                    'default_transaction_fee_payer' => 'account',
                ]),
            ]);

            Log::info('PaymentInfo auto-approved with default settings via pipeline', [
                'payment_info_id' => $paymentInfo->id,
                'organizer_id' => $paymentInfo->organizer_id,
                'user_id' => $user?->id,
                'percentage_charge' => 7.00,
                'transaction_fee_payer' => 'account',
            ]);

            $data['payment_info'] = $paymentInfo->fresh();
            $data['auto_approved'] = true;

        } catch (\Exception $e) {
            Log::error('Failed to auto-approve PaymentInfo in pipeline', [
                'payment_info_id' => $paymentInfo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $data['auto_approval_error'] = $e->getMessage();
        }

        return $next($data);
    }
}
