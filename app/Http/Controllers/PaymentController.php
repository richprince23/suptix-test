<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private PaystackService $paystackService) {}

    /**
     * Initialize payment for an order
     */
    public function initialize(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with(['organizer.paymentInfo', 'event'])->findOrFail($request->order_id);

        // Check if order is already paid or confirmed
        if ($order->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid for',
            ], 400);
        }

        // Check if organizer has payment info configured
        if (! $order->organizer->paymentInfo || ! $order->organizer->paymentInfo->paystack_subaccount_code) {
            return response()->json([
                'success' => false,
                'message' => 'Payment is not available for this event. Please contact the organizer.',
            ], 400);
        }

        $result = $this->paystackService->initializePayment($order);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Handle payment callback from Paystack
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()->route('payment.failed')
                ->with('error', 'Invalid payment reference');
        }

        // Verify payment with Paystack
        $verificationResult = $this->paystackService->verifyPayment($reference);

        if (! $verificationResult['success']) {
            Log::error('Payment verification failed', [
                'reference' => $reference,
                'message' => $verificationResult['message'],
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Payment verification failed');
        }

        $paymentData = $verificationResult['data'];

        // Check if payment was successful
        if ($paymentData['status'] !== 'success') {
            return redirect()->route('payment.failed')
                ->with('error', 'Payment was not successful');
        }

        // Process the successful payment
        $processResult = $this->paystackService->processSuccessfulPayment($paymentData);

        if ($processResult['success']) {
            $order = $processResult['data']['order'];

            return redirect()->route('payment.success', ['order' => $order->order_number])
                ->with('success', 'Payment completed successfully!');
        }

        Log::error('Failed to process successful payment', [
            'reference' => $reference,
            'message' => $processResult['message'],
        ]);

        return redirect()->route('payment.failed')
            ->with('error', 'Payment processing failed');
    }

    /**
     * Show payment success page
     */
    public function success(Request $request): Response
    {
        $orderNumber = $request->route('order');
        $order = Order::with(['event', 'tickets.ticketType', 'organizer'])
            ->where('order_number', $orderNumber)
            ->where('status', 'confirmed')
            ->firstOrFail();

        return Inertia::render('Payment/Success', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'buyer_email' => $order->buyer_email,
                'buyer_phone' => $order->buyer_phone,
                'purchased_at' => $order->purchased_at,
                'event' => [
                    'id' => $order->event->id,
                    'name' => $order->event->title,
                    'code' => $order->event->code,
                    'start_date' => $order->event->start_date,
                    'start_time' => $order->event->start_time,
                ],
                'tickets' => $order->tickets->groupBy('ticket_type_id')->map(function ($tickets) {
                    $firstTicket = $tickets->first();

                    return [
                        'id' => $firstTicket->ticket_type_id,
                        'name' => $firstTicket->ticketType->name,
                        'quantity' => $tickets->count(),
                        'unit_price' => $firstTicket->price,
                        'total_price' => $tickets->sum('price'),
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Show payment failed page
     */
    public function failed(): Response
    {
        return Inertia::render('Payment/Failed', [
        ]);
    }

    /**
     * Verify payment status
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        $reference = $request->reference;
        $result = $this->paystackService->verifyPayment($reference);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Handle Paystack webhooks
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify webhook signature
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (! $this->paystackService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid webhook signature received');

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $request->json()->all();
        $eventType = $event['event'] ?? null;

        Log::info('Paystack webhook received', [
            'event' => $eventType,
            'data' => $event['data'] ?? [],
        ]);

        switch ($eventType) {
            case 'charge.success':
                return $this->handleChargeSuccess($event['data']);

            case 'charge.dispute.create':
                return $this->handleChargeDispute($event['data']);

            case 'transfer.success':
                return $this->handleTransferSuccess($event['data']);

            case 'transfer.failed':
                return $this->handleTransferFailed($event['data']);

            default:
                Log::info('Unhandled webhook event', ['event' => $eventType]);

                return response()->json(['message' => 'Event not handled'], 200);
        }
    }

    /**
     * Handle successful charge webhook
     */
    private function handleChargeSuccess(array $data): JsonResponse
    {
        $reference = $data['reference'] ?? null;

        if (! $reference) {
            Log::error('Charge success webhook missing reference');

            return response()->json(['message' => 'Missing reference'], 400);
        }

        // Find payment and update status if not already processed
        $payment = Payment::where('transaction_id', $reference)->first();

        if (! $payment) {
            Log::warning('Payment not found for webhook reference', ['reference' => $reference]);

            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Only process if payment is still pending
        if ($payment->status === 'pending') {
            // Get complete transaction verification data for accurate balance calculation
            $transactionData = $data;

            // If webhook data doesn't include fees_split, fetch it from verification endpoint
            if (! isset($data['fees_split'])) {
                $verificationResult = $this->paystackService->verifyPayment($reference);
                if ($verificationResult['success']) {
                    $transactionData = $verificationResult['data'];

                    Log::info('Fetched complete transaction data for webhook processing', [
                        'reference' => $reference,
                        'has_fees_split' => isset($transactionData['fees_split']),
                        'subaccount_code' => $transactionData['subaccount']['subaccount_code'] ?? 'none',
                    ]);
                } else {
                    Log::warning('Could not get verification data for webhook, using webhook data', [
                        'reference' => $reference,
                        'verification_error' => $verificationResult['message'],
                    ]);
                }
            }

            $processResult = $this->paystackService->processSuccessfulPayment($transactionData);

            if ($processResult['success']) {
                Log::info('Payment processed successfully via webhook', [
                    'reference' => $reference,
                    'payment_id' => $payment->id,
                    'has_fees_split_data' => isset($transactionData['fees_split']),
                ]);
            } else {
                Log::error('Failed to process payment via webhook', [
                    'reference' => $reference,
                    'message' => $processResult['message'],
                ]);
            }
        } else {
            Log::info('Payment already processed, skipping webhook processing', [
                'reference' => $reference,
                'payment_id' => $payment->id,
                'current_status' => $payment->status,
            ]);
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    /**
     * Handle charge dispute webhook
     */
    private function handleChargeDispute(array $data): JsonResponse
    {
        $reference = $data['transaction']['reference'] ?? null;

        if ($reference) {
            $payment = Payment::where('transaction_id', $reference)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'disputed',
                    'notes' => 'Payment disputed - '.($data['reason'] ?? 'No reason provided'),
                ]);

                Log::warning('Payment disputed', [
                    'reference' => $reference,
                    'payment_id' => $payment->id,
                    'reason' => $data['reason'] ?? 'No reason provided',
                ]);
            }
        }

        return response()->json(['message' => 'Dispute handled'], 200);
    }

    /**
     * Handle transfer success webhook
     */
    private function handleTransferSuccess(array $data): JsonResponse
    {
        Log::info('Transfer successful', ['data' => $data]);

        return response()->json(['message' => 'Transfer success handled'], 200);
    }

    /**
     * Handle transfer failed webhook
     */
    private function handleTransferFailed(array $data): JsonResponse
    {
        Log::warning('Transfer failed', ['data' => $data]);

        return response()->json(['message' => 'Transfer failure handled'], 200);
    }
}
