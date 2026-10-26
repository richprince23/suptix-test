<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use App\Services\EventCreditService;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    protected PaystackService $paystackService;

    public function __construct(
        PaystackService $paystackService,
        protected EventCreditService $creditService,
    ) {
        $this->paystackService = $paystackService;
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
            'account_number' => 'required|string|min:6|max:17',
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
        if (! auth()->user()->organizers->contains('id', $paymentInfo->organizer_id)) {
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
}
