<?php

namespace App\Services;

use App\Mail\OrderEmail;
use App\Mail\TicketEmail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentInfo;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaystackService
{
    private string $secretKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key', env('PAYSTACK_SECRET_KEY'));
        $this->baseUrl = 'https://api.paystack.co';
    }

    /**
     * Create a Paystack subaccount for an organizer
     */
    public function createSubaccount(PaymentInfo $paymentInfo): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/subaccount', [
                'business_name' => $paymentInfo->business_name,
                'settlement_bank' => $paymentInfo->bank_code,
                'account_number' => $paymentInfo->account_number,
                'percentage_charge' => $paymentInfo->percentage_charge,
                'description' => "Subaccount for {$paymentInfo->organizer->name}",
                'primary_contact_email' => $paymentInfo->organizer->email,
                'primary_contact_name' => $paymentInfo->organizer->name,
                'primary_contact_phone' => $paymentInfo->organizer->phone,
                'metadata' => $paymentInfo->metadata ?: [],
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                // Update payment info with Paystack response
                $paymentInfo->update([
                    'paystack_subaccount_code' => $data['data']['subaccount_code'],
                    'subaccount_created_at' => now(),
                    'paystack_response' => $data['data'],
                    'is_verified' => $data['data']['is_verified'] ?? false,
                ]);

                Log::info('Paystack subaccount created successfully', [
                    'organizer_id' => $paymentInfo->organizer_id,
                    'subaccount_code' => $data['data']['subaccount_code'],
                ]);

                return [
                    'success' => true,
                    'data' => $data['data'],
                    'message' => 'Subaccount created successfully',
                ];
            }

            Log::error('Failed to create Paystack subaccount', [
                'organizer_id' => $paymentInfo->organizer_id,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to create subaccount',
                'data' => $data,
            ];

        } catch (Exception $e) {
            Log::error('Exception creating Paystack subaccount', [
                'organizer_id' => $paymentInfo->organizer_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while creating the subaccount',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get subaccount details from Paystack
     */
    public function getSubaccount(string $subaccountCode): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
            ])->get($this->baseUrl."/subaccount/{$subaccountCode}");

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'data' => $data['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to fetch subaccount',
            ];

        } catch (Exception $e) {
            Log::error('Exception fetching Paystack subaccount', [
                'subaccount_code' => $subaccountCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while fetching subaccount details',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get balance for a subaccount
     */
    public function getSubaccountBalance(string $subaccountCode): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
            ])->get($this->baseUrl.'/balance', [
                'subaccount' => $subaccountCode,
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                // Convert pesewas to naira/cedi
                Log::info($data['data'][0]);
                $availableBalance = $data['data'][0]['balance'] / 100;
                $ledgerBalance = $data['data'][0]['balance'] / 100;

                return [
                    'success' => true,
                    'balance' => $availableBalance,
                    // 'ledger_balance' => $ledgerBalance,
                    'currency' => $data['data'][0]['currency'] ?? 'GHS',
                    'raw_data' => $data['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to fetch balance',
            ];

        } catch (Exception $e) {
            Log::error('Exception fetching Paystack balance', [
                'subaccount_code' => $subaccountCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while fetching balance',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update subaccount balance in database
     */
    public function syncSubaccountBalance(PaymentInfo $paymentInfo): bool
    {
        if (! $paymentInfo->paystack_subaccount_code) {
            return false;
        }

        $balanceResult = $this->getSubaccountBalance($paymentInfo->paystack_subaccount_code);

        if ($balanceResult['success']) {
            $paymentInfo->updateBalance(
                $balanceResult['balance'],
                $balanceResult['balance']
            );

            Log::info('Subaccount balance synced', [
                'organizer_id' => $paymentInfo->organizer_id,
                'available_balance' => $balanceResult['balance'],
                'ledger_balance' => $balanceResult['balance'],
            ]);

            return true;
        }

        Log::warning('Failed to sync subaccount balance', [
            'organizer_id' => $paymentInfo->organizer_id,
            'message' => $balanceResult['message'],
        ]);

        return false;
    }

    /**
     * Get list of supported banks
     */
    public function getBanks(string $country = 'ghana'): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
            ])->get($this->baseUrl.'/bank', [
                'country' => $country,
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'banks' => collect($data['data'])->map(function ($bank) {
                        return [
                            'code' => $bank['code'],
                            'name' => $bank['name'],
                            'slug' => $bank['slug'] ?? null,
                        ];
                    })->toArray(),
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to fetch banks',
            ];

        } catch (Exception $e) {
            Log::error('Exception fetching banks', [
                'country' => $country,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while fetching banks',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify bank account number
     */
    public function verifyBankAccount(string $accountNumber, string $bankCode): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
            ])->get($this->baseUrl.'/bank/resolve', [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'account_name' => $data['data']['account_name'],
                    'account_number' => $data['data']['account_number'],
                    'bank_id' => $data['data']['bank_id'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to verify account',
            ];

        } catch (Exception $e) {
            Log::error('Exception verifying bank account', [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying account',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update subaccount details
     */
    public function updateSubaccount(string $subaccountCode, array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->put($this->baseUrl."/subaccount/{$subaccountCode}", $data);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status']) {
                return [
                    'success' => true,
                    'data' => $responseData['data'],
                    'message' => 'Subaccount updated successfully',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to update subaccount',
            ];

        } catch (Exception $e) {
            Log::error('Exception updating Paystack subaccount', [
                'subaccount_code' => $subaccountCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while updating the subaccount',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initialize a payment transaction with split payment
     */
    public function initializePayment(Order $order): array
    {
        try {
            $organizer = $order->organizer;
            $paymentInfo = $organizer->paymentInfo;

            if (! $paymentInfo || ! $paymentInfo->paystack_subaccount_code) {
                return [
                    'success' => false,
                    'message' => 'Organizer payment information not properly configured',
                ];
            }

            // Use the subaccount's individual percentage_charge instead of global config
            $platformFeePercentage = $paymentInfo->percentage_charge;
            // Paystack expects whole pesewas for both the amount and the charge
            $totalAmount = (int) round($order->total_amount * 100);
            $platformFee = (int) round(($totalAmount * $platformFeePercentage) / 100);
            $organizerAmount = $totalAmount - $platformFee;

            $paymentData = [
                'email' => $order->buyer_email,
                'amount' => $totalAmount,
                'currency' => 'GHS',
                'reference' => $this->generatePaymentReference($order),
                'callback_url' => route('payment.callback'),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'organizer_id' => $organizer->id,
                    'event_id' => $order->event_id,
                    'platform_fee' => $platformFee,
                    'organizer_amount' => $organizerAmount,
                ],
                'subaccount' => $paymentInfo->paystack_subaccount_code,
                'transaction_charge' => $platformFee,
                // 'bearer' => 'subaccount', // Subaccount bears the transaction charge
                // 'bearer' => 'account', // Subaccount bears the transaction charge
                'bearer' => $paymentInfo->transaction_fee_payer, // Subaccount bears the transaction charge
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/transaction/initialize', $paymentData);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                // Create payment record

                Log::info($data['data']);
                $payment = Payment::create([
                    'organizer_id' => $organizer->id,
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'payment_method' => 'paystack',
                    // 'payment_method' => $data['data']['channel'],
                    'transaction_id' => $data['data']['reference'],
                    'status' => 'pending',
                    'notes' => 'Payment initialized via Paystack',
                ]);

                // Update order with payment reference
                $order->update([
                    'payment_reference' => $data['data']['reference'],
                    // 'payment_method' => $data['data']['channel'],
                    'payment_method' => 'paystack',
                ]);

                Log::info('Payment initialized successfully', [
                    'order_id' => $order->id,
                    'reference' => $data['data']['reference'],
                    'amount' => $totalAmount,
                    'platform_fee' => $platformFee,
                    'organizer_amount' => $organizerAmount,
                ]);

                return [
                    'success' => true,
                    'data' => [
                        'authorization_url' => $data['data']['authorization_url'],
                        'access_code' => $data['data']['access_code'],
                        'reference' => $data['data']['reference'],
                        'payment_id' => $payment->id,
                    ],
                    'message' => 'Payment initialized successfully',
                ];
            }

            Log::error('Failed to initialize payment', [
                'order_id' => $order->id,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to initialize payment',
                'data' => $data,
            ];

        } catch (Exception $e) {
            Log::error('Exception initializing payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while initializing payment',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment transaction
     */
    public function verifyPayment(string $reference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
            ])->get($this->baseUrl."/transaction/verify/{$reference}");

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'data' => $data['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to verify payment',
            ];

        } catch (Exception $e) {
            Log::error('Exception verifying payment', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying payment',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process successful payment
     */
    public function processSuccessfulPayment(array $paymentData): array
    {
        try {
            return DB::transaction(function () use ($paymentData) {
                $reference = $paymentData['reference'];
                $metadata = $paymentData['metadata'] ?? [];

                // Find the order and payment
                $order = Order::where('payment_reference', $reference)->first();
                if (! $order) {
                    throw new Exception("Order not found for reference: {$reference}");
                }

                $payment = Payment::where('transaction_id', $reference)->first();
                if (! $payment) {
                    throw new Exception("Payment record not found for reference: {$reference}");
                }

                // The gateway event must belong to this order (when it says which one)
                if (isset($metadata['order_id']) && (int) $metadata['order_id'] !== $order->id) {
                    throw new Exception("Gateway metadata names order {$metadata['order_id']}, not order {$order->id}");
                }

                // The amount paid must equal the order total (both in pesewas)
                $expectedPesewas = (int) round($order->total_amount * 100);
                if (! isset($paymentData['amount']) || (int) round($paymentData['amount']) !== $expectedPesewas) {
                    throw new Exception('Paid amount '.($paymentData['amount'] ?? 'missing')." does not match the order total of {$expectedPesewas} pesewas");
                }

                // Check if payment is already processed to prevent duplicate processing
                if ($payment->status === 'completed' && $order->status === 'confirmed') {
                    Log::info('Payment already processed, skipping duplicate processing', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Payment already processed',
                        'data' => [
                            'order' => $order,
                            'payment' => $payment,
                        ],
                    ];
                }

                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'notes' => 'Payment completed successfully via Paystack',
                ]);

                // Update order status
                $order->update([
                    'status' => 'confirmed',
                    'purchased_at' => now(),
                ]);

                // Activate all tickets and make sure there is one purchase record per ticket
                $existingPurchases = $order->purchases()->count();

                foreach ($order->tickets as $index => $ticket) {
                    // Only update if not already active
                    if ($ticket->status !== 'active') {
                        $ticket->update([
                            'status' => 'active',
                        ]);
                    }

                    // Create the missing purchase records only (a replay adds none)
                    if ($index >= $existingPurchases) {
                        // user_id is null for guest purchases, linked via the order relationship
                        $order->purchases()->create([
                            'organizer_id' => $order->organizer_id,
                            'user_id' => null,
                            'amount' => $ticket->price,
                            'status' => 'completed',
                            'purchased_at' => now(),
                        ]);
                    }
                }

                // Record transaction with actual Paystack data for accurate balance tracking
                $paymentInfo = $order->organizer->paymentInfo;
                if ($paymentInfo && $paymentInfo->paystack_subaccount_code) {
                    try {
                        // Convert amount from kobo to cedis for recording
                        $transactionAmount = (float) $paymentData['amount'] / 100;

                        // Prepare transaction data for balance recording
                        $transactionRecordData = [
                            'amount' => $transactionAmount,
                            'reference' => $paymentData['reference'],
                            'status' => 'success',
                            'created_at' => $paymentData['created_at'] ?? now(),
                        ];

                        // Include fees_split if available for accurate balance calculation
                        if (isset($paymentData['fees_split'])) {
                            $transactionRecordData['fees_split'] = $paymentData['fees_split'];
                        }

                        // Record the transaction with accurate split data
                        $paymentInfo->recordTransaction($transactionRecordData);

                        Log::info('Transaction recorded with balance update', [
                            'payment_info_id' => $paymentInfo->id,
                            'reference' => $paymentData['reference'],
                            'amount' => $transactionAmount,
                            'has_fees_split' => isset($paymentData['fees_split']),
                            'new_subaccount_balance' => $paymentInfo->fresh()->subaccount_balance,
                            'new_system_admin_balance' => $paymentInfo->fresh()->system_admin_balance,
                        ]);

                    } catch (\Exception $e) {
                        Log::error('Failed to record transaction for balance tracking', [
                            'payment_info_id' => $paymentInfo->id,
                            'reference' => $paymentData['reference'],
                            'error' => $e->getMessage(),
                        ]);

                        // Fallback to old sync method if recording fails
                        $this->syncSubaccountBalance($paymentInfo);
                    }
                }

                // Send confirmation emails automatically (only if not already sent)
                $this->sendPaymentConfirmationEmails($order);

                // Send SMS notification if phone number is provided
                if ($order->buyer_phone) {
                    try {
                        $order->notify(new \App\Notifications\OrderConfirmationSms($order));
                        Log::info('Order confirmation SMS queued', [
                            'order_id' => $order->id,
                            'phone' => $order->buyer_phone,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to queue order confirmation SMS', [
                            'order_id' => $order->id,
                            'phone' => $order->buyer_phone,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Log::info('Payment processed successfully', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'amount' => $paymentData['amount'] / 100,
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => [
                        'order' => $order,
                        'payment' => $payment,
                    ],
                ];
            });

        } catch (Exception $e) {
            Log::error('Exception processing successful payment', [
                'reference' => $paymentData['reference'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while processing the payment',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate unique payment reference
     */
    private function generatePaymentReference(Order $order): string
    {
        return 'SUP_'.$order->order_number.'_'.time().'_'.strtolower(\Illuminate\Support\Str::random(6));
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $computedSignature = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($signature, $computedSignature);
    }

    /**
     * Send payment confirmation emails after successful payment
     */
    private function sendPaymentConfirmationEmails(Order $order): void
    {
        try {
            // Check if notification emails have already been sent by looking at the database
            // We'll check if we have already sent emails recently for this order
            $recentEmailLog = Log::getLogger()->getHandlers();

            // Simple check: if order was purchased more than 1 minute ago, emails were likely already sent
            if ($order->purchased_at && $order->purchased_at->diffInMinutes(now()) > 1) {
                Log::info('Payment confirmation emails likely already sent for this order', [
                    'order_id' => $order->id,
                    'purchased_at' => $order->purchased_at,
                    'time_diff_minutes' => $order->purchased_at->diffInMinutes(now()),
                ]);

                return;
            }

            // Load necessary relationships for email templates
            $order->load([
                'event.organizer',
                'tickets.ticketType',
                'discount',
            ]);

            // Send order confirmation email to buyer
            Mail::to($order->buyer_email)->sendNow(new OrderEmail($order));

            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'buyer_email' => $order->buyer_email,
            ]);

            // Send individual ticket emails to attendees (only for active tickets with email)
            $ticketEmailsSent = 0;
            foreach ($order->tickets as $ticket) {
                if ($ticket->status === 'active' && $ticket->attendee_email) {
                    try {
                        Mail::to($ticket->attendee_email)->sendNow(new TicketEmail($ticket, $order));
                        $ticketEmailsSent++;

                        Log::info('Ticket email sent', [
                            'ticket_id' => $ticket->id,
                            'ticket_number' => $ticket->ticket_number,
                            'attendee_email' => $ticket->attendee_email,
                        ]);
                    } catch (Exception $e) {
                        Log::warning('Failed to send ticket email', [
                            'ticket_id' => $ticket->id,
                            'ticket_number' => $ticket->ticket_number,
                            'attendee_email' => $ticket->attendee_email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            Log::info('Payment confirmation emails completed', [
                'order_id' => $order->id,
                'order_email_sent' => true,
                'ticket_emails_sent' => $ticketEmailsSent,
                'total_tickets' => $order->tickets->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send payment confirmation emails', [
                'order_id' => $order->id,
                'buyer_email' => $order->buyer_email,
                'error' => $e->getMessage(),
            ]);

            // Don't throw the exception as this shouldn't fail the payment process
            // The payment has already been processed successfully
        }
    }
}
