<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    protected PaystackService $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Handle Paystack webhooks
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify the webhook signature
        $paystackSignature = $request->header('x-paystack-signature');
        $computedSignature = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret_key'));

        if (! hash_equals($paystackSignature, $computedSignature)) {
            Log::warning('Invalid Paystack webhook signature', [
                'signature' => $paystackSignature,
                'computed' => $computedSignature,
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Paystack webhook received', [
            'event' => $event,
            'data' => $data,
        ]);

        try {
            switch ($event) {
                case 'subaccount.updated':
                    $this->handleSubaccountUpdated($data);
                    break;

                case 'charge.success':
                    $this->handleChargeSuccess($data);
                    break;

                case 'transfer.success':
                case 'transfer.failed':
                    $this->handleTransferEvent($data, $event);
                    break;

                default:
                    Log::info('Unhandled Paystack webhook event', ['event' => $event]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Error processing Paystack webhook', [
                'event' => $event,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Get banks for a specific country
     */
    public function getBanks(Request $request): JsonResponse
    {
        $country = $request->input('country', 'ghana');
        $result = $this->paystackService->getBanks($country);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'banks' => $result['banks'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Verify bank account details
     */
    public function verifyAccount(Request $request): JsonResponse
    {
        $request->validate([
            'account_number' => 'required|string|min:80|max:17',
            'bank_code' => 'required|string',
        ]);

        $result = $this->paystackService->verifyBankAccount(
            $request->input('account_number'),
            $request->input('bank_code')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'account_name' => $result['account_name'],
                'account_number' => $result['account_number'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Sync balance for a specific payment info
     */
    public function syncBalance(PaymentInfo $paymentInfo): JsonResponse
    {
        // Check if user has access to this payment info
        if ($paymentInfo->organizer_id !== auth()->user()->currentTenant?->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $synced = $this->paystackService->syncSubaccountBalance($paymentInfo);

        if ($synced) {
            return response()->json([
                'success' => true,
                'available_balance' => $paymentInfo->fresh()->formatted_available_balance,
                'ledger_balance' => $paymentInfo->fresh()->formatted_ledger_balance,
                'last_updated' => $paymentInfo->fresh()->balance_last_updated->diffForHumans(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to sync balance',
        ], 400);
    }

    /**
     * Handle subaccount updated webhook
     */
    protected function handleSubaccountUpdated(array $data): void
    {
        $subaccountCode = $data['subaccount_code'] ?? null;

        if (! $subaccountCode) {
            return;
        }

        $paymentInfo = PaymentInfo::where('paystack_subaccount_code', $subaccountCode)->first();

        if (! $paymentInfo) {
            Log::warning('Received webhook for unknown subaccount', ['code' => $subaccountCode]);

            return;
        }

        // Update verification status
        if (isset($data['is_verified'])) {
            $paymentInfo->update([
                'is_verified' => $data['is_verified'],
                'verified_at' => $data['is_verified'] ? now() : null,
                'paystack_response' => $data,
            ]);

            Log::info('Subaccount verification status updated', [
                'organizer_id' => $paymentInfo->organizer_id,
                'subaccount_code' => $subaccountCode,
                'is_verified' => $data['is_verified'],
            ]);
        }
    }

    /**
     * Handle successful charge webhook
     */
    protected function handleChargeSuccess(array $data): void
    {
        $subaccountCode = $data['subaccount']['subaccount_code'] ?? null;

        if (! $subaccountCode) {
            return;
        }

        $paymentInfo = PaymentInfo::where('paystack_subaccount_code', $subaccountCode)->first();

        if ($paymentInfo) {
            // Sync balance after successful payment
            $this->paystackService->syncSubaccountBalance($paymentInfo);

            Log::info('Balance synced after charge success', [
                'organizer_id' => $paymentInfo->organizer_id,
                'amount' => $data['amount'] / 100,
            ]);
        }
    }

    /**
     * Handle transfer events
     */
    protected function handleTransferEvent(array $data, string $event): void
    {
        $recipientCode = $data['recipient']['recipient_code'] ?? null;

        if (! $recipientCode) {
            return;
        }

        // Find payment info by recipient code or other identifier
        // This would require storing recipient codes in the payment_infos table

        Log::info('Transfer event processed', [
            'event' => $event,
            'recipient_code' => $recipientCode,
            'amount' => $data['amount'] / 100,
        ]);
    }
}
