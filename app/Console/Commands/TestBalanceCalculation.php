<?php

namespace App\Console\Commands;

use App\Models\PaymentInfo;
use App\Services\PaystackBalanceService;
use Illuminate\Console\Command;

class TestBalanceCalculation extends Command
{
    protected $signature = 'test:balance-calculation {--payment-info-id=}';

    protected $description = 'Test balance calculation with sample Paystack transaction data';

    public function handle()
    {
        $this->info('Testing balance calculation with sample Paystack data...');

        // Sample transaction data from user
        $sampleTransactionData = [
            'id' => 5205478439,
            'domain' => 'test',
            'status' => 'success',
            'reference' => 'SUP_ORD-DV1NFAT5UL_1754226393',
            'receipt_number' => '10101',
            'amount' => 3500,
            'message' => null,
            'gateway_response' => 'Approved',
            'paid_at' => '2025-08-03T13:06:49.000Z',
            'created_at' => '2025-08-03T13:06:33.000Z',
            'channel' => 'mobile_money',
            'currency' => 'GHS',
            'metadata' => [
                'order_id' => '34',
                'order_number' => 'ORD-DV1NFAT5UL',
                'organizer_id' => '3',
                'event_id' => '2',
                'platform_fee' => '175',
                'organizer_amount' => '3325',
                'referrer' => 'http://localhost:8000/',
            ],
            'fees' => 69,
            'fees_split' => [
                'paystack' => 69,
                'integration' => 106,
                'subaccount' => 3325,
                'params' => [
                    'bearer' => 'account',
                    'transaction_charge' => '175',
                    'percentage_charge' => '10',
                ],
            ],
            'subaccount' => [
                'id' => 1391966,
                'subaccount_code' => 'ACCT_f8dyurub2v0bpfk',
                'business_name' => 'RICHARD KWEKU AIKINS',
                'percentage_charge' => 10,
            ],
        ];

        $this->info('Sample Transaction Data:');
        $this->line("Reference: {$sampleTransactionData['reference']}");
        $this->line('Total Amount: GHS '.number_format($sampleTransactionData['amount'] / 100, 2));
        $this->line('Subaccount Amount: GHS '.number_format($sampleTransactionData['fees_split']['subaccount'] / 100, 2));
        $this->line('Integration Amount: GHS '.number_format($sampleTransactionData['fees_split']['integration'] / 100, 2));
        $this->line("Percentage Charge: {$sampleTransactionData['subaccount']['percentage_charge']}%");

        // Test with actual PaymentInfo if provided
        $paymentInfoId = $this->option('payment-info-id');
        if ($paymentInfoId) {
            $paymentInfo = PaymentInfo::find($paymentInfoId);
            if (! $paymentInfo) {
                $this->error("PaymentInfo with ID {$paymentInfoId} not found");

                return 1;
            }
        } else {
            // Find any approved payment info for testing
            $paymentInfo = PaymentInfo::where('status', 'approved')
                ->whereNotNull('paystack_subaccount_code')
                ->first();

            if (! $paymentInfo) {
                $this->error('No approved PaymentInfo found for testing');

                return 1;
            }
        }

        $this->newLine();
        $this->info("Testing with PaymentInfo ID: {$paymentInfo->id}");
        $this->line("Organizer: {$paymentInfo->organizer->name}");
        $this->line("Business Name: {$paymentInfo->business_name}");
        $this->line("Database Percentage Charge: {$paymentInfo->percentage_charge}%");
        $this->line("Current Subaccount Balance: {$paymentInfo->formatted_subaccount_balance}");
        $this->line("Current System Admin Balance: {$paymentInfo->formatted_system_admin_balance}");

        // Test the balance calculation
        $balanceService = new PaystackBalanceService;

        try {
            // Test single transaction processing
            $result = $balanceService->processTransactionForBalance($paymentInfo, $sampleTransactionData);

            $this->newLine();
            $this->info('Balance Calculation Results:');
            $this->line('Total Amount: GHS '.number_format($result['total_amount'], 2));
            $this->line('Subaccount Amount: GHS '.number_format($result['subaccount_amount'], 2));
            $this->line('System Admin Amount: GHS '.number_format($result['system_admin_amount'], 2));
            $this->line('Used Fees Split Data: '.($result['has_fees_split_data'] ? 'Yes' : 'No'));

            // Show updated balances
            $paymentInfo->refresh();
            $this->newLine();
            $this->info('Updated Balances:');
            $this->line("New Subaccount Balance: {$paymentInfo->formatted_subaccount_balance}");
            $this->line("New System Admin Balance: {$paymentInfo->formatted_system_admin_balance}");
            $this->line("Total Transaction Volume: {$paymentInfo->formatted_total_transaction_volume}");
            $this->line("Total Transactions: {$paymentInfo->total_transactions}");

            $this->newLine();
            $this->info('✓ Balance calculation test completed successfully!');

        } catch (\Exception $e) {
            $this->error('Balance calculation test failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
