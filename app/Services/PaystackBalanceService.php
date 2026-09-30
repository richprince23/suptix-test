<?php

namespace App\Services;

use App\Models\PaymentInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackBalanceService
{
    protected string $secretKey;

    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    /**
     * Sync balances for a specific subaccount by analyzing transactions
     */
    public function syncSubaccountBalances(PaymentInfo $paymentInfo): array
    {
        if (! $paymentInfo->paystack_subaccount_code) {
            throw new \Exception('Subaccount code not found for payment info');
        }

        try {
            // Get all transactions for this subaccount
            $transactions = $this->getSubaccountTransactions($paymentInfo->paystack_subaccount_code);

            // Calculate separate balances using actual split data and database percentage
            $balanceData = $this->calculateSeparateBalances($transactions, $paymentInfo);

            // Update the payment info with new balances
            $paymentInfo->updateSeparateBalances(array_merge($balanceData, [
                'sync_method' => 'paystack_api_sync',
            ]));

            Log::info('Successfully synced subaccount balances', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'balances' => $balanceData,
            ]);

            return $balanceData;

        } catch (\Exception $e) {
            Log::error('Failed to sync subaccount balances', [
                'payment_info_id' => $paymentInfo->id,
                'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get all transactions for a specific subaccount with detailed verification data
     */
    protected function getSubaccountTransactions(string $subaccountCode): array
    {
        $allTransactions = [];
        $page = 1;
        $perPage = 200;

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/transaction", [
                'subaccount' => $subaccountCode,
                'page' => $page,
                'perPage' => $perPage,
                'status' => 'success', // Only get successful transactions
            ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to fetch transactions: '.$response->body());
            }

            $data = $response->json();

            if (! $data['status']) {
                throw new \Exception('Paystack API error: '.($data['message'] ?? 'Unknown error'));
            }

            $transactions = $data['data'];

            // Get detailed verification data for each transaction to get accurate split amounts
            $detailedTransactions = [];
            foreach ($transactions as $transaction) {
                try {
                    $verificationData = $this->getTransactionVerificationData($transaction['reference']);
                    if ($verificationData) {
                        $detailedTransactions[] = $verificationData;
                    } else {
                        // Fallback to basic transaction data if verification fails
                        $detailedTransactions[] = $transaction;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to get verification data for transaction', [
                        'reference' => $transaction['reference'],
                        'error' => $e->getMessage(),
                    ]);
                    // Fallback to basic transaction data
                    $detailedTransactions[] = $transaction;
                }
            }

            $allTransactions = array_merge($allTransactions, $detailedTransactions);

            $page++;

            // Break if we got less than perPage results (last page)
        } while (count($transactions) === $perPage);

        return $allTransactions;
    }

    /**
     * Get detailed transaction verification data including fees_split
     */
    protected function getTransactionVerificationData(string $reference): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (! $data['status']) {
                return null;
            }

            return $data['data'];
        } catch (\Exception $e) {
            Log::error('Failed to verify transaction', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Calculate separate balances using actual Paystack fees_split data
     */
    protected function calculateSeparateBalances(array $transactions, PaymentInfo $paymentInfo): array
    {
        $subaccountBalance = 0;
        $systemAdminBalance = 0;
        $totalVolume = 0;
        $totalTransactions = count($transactions);
        $successfulTransactions = 0;
        $pendingTransactions = 0;
        $lastTransaction = null;
        $splitTransactions = 0; // Track how many transactions have split data

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction['amount'] / 100; // Convert from kobo to cedis
            $totalVolume += $amount;

            // Use actual split amounts from Paystack if available
            if (isset($transaction['fees_split'])) {
                $splitTransactions++;

                // Get actual amounts from Paystack split
                $subaccountAmount = isset($transaction['fees_split']['subaccount'])
                    ? (float) $transaction['fees_split']['subaccount'] / 100 // Convert from kobo
                    : 0;

                $integrationAmount = isset($transaction['fees_split']['integration'])
                    ? (float) $transaction['fees_split']['integration'] / 100 // Convert from kobo
                    : 0;

                Log::info('Using actual Paystack split data', [
                    'reference' => $transaction['reference'],
                    'total_amount' => $amount,
                    'subaccount_amount' => $subaccountAmount,
                    'integration_amount' => $integrationAmount,
                    'fees_split' => $transaction['fees_split'],
                ]);

            } else {
                // Fallback to manual calculation using percentage from database
                $percentageCharge = (float) $paymentInfo->percentage_charge;
                $integrationAmount = $amount * ($percentageCharge / 100);
                $subaccountAmount = $amount - $integrationAmount;

                Log::info('Using fallback calculation for transaction', [
                    'reference' => $transaction['reference'],
                    'total_amount' => $amount,
                    'percentage_charge' => $percentageCharge,
                    'calculated_subaccount_amount' => $subaccountAmount,
                    'calculated_integration_amount' => $integrationAmount,
                ]);
            }

            $subaccountBalance += $subaccountAmount;
            $systemAdminBalance += $integrationAmount;

            // Count transaction statuses
            switch ($transaction['status']) {
                case 'success':
                    $successfulTransactions++;
                    break;
                case 'pending':
                    $pendingTransactions++;
                    break;
            }

            // Track last transaction
            if (! $lastTransaction ||
                Carbon::parse($transaction['created_at'])->isAfter(Carbon::parse($lastTransaction['created_at']))) {
                $lastTransaction = $transaction;
            }
        }

        Log::info('Balance calculation completed', [
            'payment_info_id' => $paymentInfo->id,
            'total_transactions' => $totalTransactions,
            'transactions_with_split_data' => $splitTransactions,
            'subaccount_balance' => round($subaccountBalance, 2),
            'system_admin_balance' => round($systemAdminBalance, 2),
            'total_volume' => round($totalVolume, 2),
        ]);

        return [
            'subaccount_balance' => round($subaccountBalance, 2),
            'system_admin_balance' => round($systemAdminBalance, 2),
            'total_transaction_volume' => round($totalVolume, 2),
            'transaction_counts' => [
                'total' => $totalTransactions,
                'successful' => $successfulTransactions,
                'pending' => $pendingTransactions,
            ],
            'last_transaction' => $lastTransaction ? [
                'created_at' => Carbon::parse($lastTransaction['created_at']),
                'reference' => $lastTransaction['reference'],
                'amount' => (float) $lastTransaction['amount'] / 100,
            ] : null,
            'split_data_coverage' => $totalTransactions > 0 ? round(($splitTransactions / $totalTransactions) * 100, 1) : 0,
        ];
    }

    /**
     * Get subaccount balance directly from Paystack (available and ledger)
     */
    public function getSubaccountBalance(string $subaccountCode): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/subaccount/{$subaccountCode}");

        if (! $response->successful()) {
            throw new \Exception('Failed to fetch subaccount details: '.$response->body());
        }

        $data = $response->json();

        if (! $data['status']) {
            throw new \Exception('Paystack API error: '.($data['message'] ?? 'Unknown error'));
        }

        $subaccount = $data['data'];

        return [
            'available_balance' => isset($subaccount['available_balance']) ? (float) $subaccount['available_balance'] / 100 : 0,
            'ledger_balance' => isset($subaccount['ledger_balance']) ? (float) $subaccount['ledger_balance'] / 100 : 0,
        ];
    }

    /**
     * Sync all approved payment infos
     */
    public function syncAllSubaccountBalances(): array
    {
        $paymentInfos = PaymentInfo::where('status', 'approved')
            ->whereNotNull('paystack_subaccount_code')
            ->where('is_active', true)
            ->get();

        $results = [];

        foreach ($paymentInfos as $paymentInfo) {
            try {
                $balanceData = $this->syncSubaccountBalances($paymentInfo);
                $results[] = [
                    'payment_info_id' => $paymentInfo->id,
                    'organizer_name' => $paymentInfo->organizer->name,
                    'success' => true,
                    'balances' => $balanceData,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'payment_info_id' => $paymentInfo->id,
                    'organizer_name' => $paymentInfo->organizer->name,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single transaction for immediate balance update
     */
    public function processTransactionForBalance(PaymentInfo $paymentInfo, array $transactionData): array
    {
        try {
            // Ensure we have the transaction verification data with fees_split
            $verificationData = null;
            if (isset($transactionData['fees_split'])) {
                $verificationData = $transactionData;
            } elseif (isset($transactionData['reference'])) {
                $verificationData = $this->getTransactionVerificationData($transactionData['reference']);
            }

            if (! $verificationData) {
                throw new \Exception('Could not get transaction verification data');
            }

            // Calculate balance changes for this single transaction
            $balanceChanges = $this->calculateSingleTransactionBalance($verificationData, $paymentInfo);

            // Update payment info balances
            $paymentInfo->updateSeparateBalances([
                'subaccount_balance' => $paymentInfo->subaccount_balance + $balanceChanges['subaccount_amount'],
                'system_admin_balance' => $paymentInfo->system_admin_balance + $balanceChanges['system_admin_amount'],
                'total_transaction_volume' => $paymentInfo->total_transaction_volume + $balanceChanges['total_amount'],
                'transaction_counts' => [
                    'total' => $paymentInfo->total_transactions + 1,
                    'successful' => $paymentInfo->successful_transactions + ($verificationData['status'] === 'success' ? 1 : 0),
                    'pending' => $paymentInfo->pending_transactions + ($verificationData['status'] === 'pending' ? 1 : 0),
                ],
                'last_transaction' => [
                    'created_at' => Carbon::parse($verificationData['created_at']),
                    'reference' => $verificationData['reference'],
                    'amount' => $balanceChanges['total_amount'],
                ],
                'sync_method' => 'single_transaction_processing',
            ]);

            Log::info('Single transaction processed for balance update', [
                'payment_info_id' => $paymentInfo->id,
                'reference' => $verificationData['reference'],
                'balance_changes' => $balanceChanges,
            ]);

            return $balanceChanges;

        } catch (\Exception $e) {
            Log::error('Failed to process single transaction for balance', [
                'payment_info_id' => $paymentInfo->id,
                'transaction_reference' => $transactionData['reference'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate balance changes for a single transaction
     */
    protected function calculateSingleTransactionBalance(array $transactionData, PaymentInfo $paymentInfo): array
    {
        $totalAmount = (float) $transactionData['amount'] / 100; // Convert from kobo

        // Use actual split amounts from Paystack if available
        if (isset($transactionData['fees_split'])) {
            $subaccountAmount = isset($transactionData['fees_split']['subaccount'])
                ? (float) $transactionData['fees_split']['subaccount'] / 100
                : 0;
            $systemAdminAmount = isset($transactionData['fees_split']['integration'])
                ? (float) $transactionData['fees_split']['integration'] / 100
                : 0;
        } else {
            // Fallback to manual calculation using percentage from database
            $percentageCharge = (float) $paymentInfo->percentage_charge;
            $systemAdminAmount = $totalAmount * ($percentageCharge / 100);
            $subaccountAmount = $totalAmount - $systemAdminAmount;
        }

        return [
            'total_amount' => $totalAmount,
            'subaccount_amount' => round($subaccountAmount, 2),
            'system_admin_amount' => round($systemAdminAmount, 2),
            'has_fees_split_data' => isset($transactionData['fees_split']),
        ];
    }

    /**
     * Get system-wide balance summary
     */
    public function getSystemBalanceSummary(): array
    {
        $paymentInfos = PaymentInfo::where('status', 'approved')
            ->where('is_active', true)
            ->get();

        $totalSubaccountBalance = 0;
        $totalSystemAdminBalance = 0;
        $totalTransactionVolume = 0;
        $totalTransactions = 0;
        $activeSubaccounts = 0;

        foreach ($paymentInfos as $paymentInfo) {
            $totalSubaccountBalance += (float) $paymentInfo->subaccount_balance;
            $totalSystemAdminBalance += (float) $paymentInfo->system_admin_balance;
            $totalTransactionVolume += (float) $paymentInfo->total_transaction_volume;
            $totalTransactions += $paymentInfo->total_transactions;

            if ($paymentInfo->paystack_subaccount_code) {
                $activeSubaccounts++;
            }
        }

        return [
            'total_subaccount_balance' => round($totalSubaccountBalance, 2),
            'total_system_admin_balance' => round($totalSystemAdminBalance, 2),
            'total_transaction_volume' => round($totalTransactionVolume, 2),
            'total_balance' => round($totalSubaccountBalance + $totalSystemAdminBalance, 2),
            'total_transactions' => $totalTransactions,
            'active_subaccounts' => $activeSubaccounts,
            'average_transaction_value' => $totalTransactions > 0 ? round($totalTransactionVolume / $totalTransactions, 2) : 0,
            'system_admin_percentage' => $totalTransactionVolume > 0 ? round(($totalSystemAdminBalance / $totalTransactionVolume) * 100, 2) : 0,
        ];
    }
}
