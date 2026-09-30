<?php

namespace App\Console\Commands;

use App\Models\Organizer;
use App\Models\PaymentInfo;
use Illuminate\Console\Command;

class TestEarningsWidget extends Command
{
    protected $signature = 'test:earnings-widget {organizer_id?}';

    protected $description = 'Test the earnings widget calculation logic';

    public function handle()
    {
        $organizerId = $this->argument('organizer_id');

        if ($organizerId) {
            $organizer = Organizer::find($organizerId);
            if (! $organizer) {
                $this->error("Organizer with ID {$organizerId} not found.");

                return 1;
            }
        } else {
            $organizer = Organizer::first();
            if (! $organizer) {
                $this->error('No organizers found in the system.');

                return 1;
            }
            $this->info("Using organizer: {$organizer->name} (ID: {$organizer->id})");
        }

        $paymentInfo = PaymentInfo::where('organizer_id', $organizer->id)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->first();

        if (! $paymentInfo) {
            $this->warn("No approved payment info found for organizer: {$organizer->name}");
            $this->info('The widget would show: "Payment Info Required"');

            return 0;
        }

        $this->info("Testing earnings calculation for: {$organizer->name}");
        $this->line('');

        // Use the exact same logic as the widget
        $subaccountBalance = (float) $paymentInfo->subaccount_balance;
        $systemAdminBalance = (float) $paymentInfo->system_admin_balance;
        $totalTransactionVolume = (float) $paymentInfo->total_transaction_volume;
        $totalBalance = $subaccountBalance + $systemAdminBalance;

        $totalTransactions = $paymentInfo->total_transactions ?? 0;
        $successfulTransactions = $paymentInfo->successful_transactions ?? 0;

        $successRate = $totalTransactions > 0
            ? round(($successfulTransactions / $totalTransactions) * 100, 1)
            : 0;

        $averageEarningPerTransaction = $successfulTransactions > 0
            ? $subaccountBalance / $successfulTransactions
            : 0;

        $platformFeePercentage = $totalTransactionVolume > 0
            ? round(($systemAdminBalance / $totalTransactionVolume) * 100, 2)
            : ($paymentInfo->percentage_charge ?? 0);

        $yourRevenuePercentage = $totalTransactionVolume > 0
            ? round(($subaccountBalance / $totalTransactionVolume) * 100, 2)
            : (100 - ($paymentInfo->percentage_charge ?? 0));

        // Display the results
        $this->table(
            ['Metric', 'Value', 'Description'],
            [
                ['Your Earnings', 'GHS '.number_format($subaccountBalance, 2), "From {$successfulTransactions} successful transactions"],
                ['Transaction Volume', 'GHS '.number_format($totalTransactionVolume, 2), "Total volume from {$totalTransactions} transactions"],
                ['Platform Fees Paid', 'GHS '.number_format($systemAdminBalance, 2), "{$platformFeePercentage}% of transaction volume"],
                ['Your Revenue Share', "{$yourRevenuePercentage}%", 'Percentage you keep from transactions'],
                ['Transaction Success Rate', "{$successRate}%", "{$successfulTransactions} successful, ".($totalTransactions - $successfulTransactions).' failed/pending'],
                ['Avg Earning/Transaction', 'GHS '.number_format($averageEarningPerTransaction, 2), 'Your average revenue per successful transaction'],
            ]
        );

        $this->line('');
        $this->info('✅ Widget calculation test completed successfully!');

        // Show sync status
        if ($paymentInfo->subaccount_balance_last_synced) {
            $this->info("Last synced: {$paymentInfo->subaccount_balance_last_synced->diffForHumans()}");
        } else {
            $this->warn('⚠️  Balance has never been synced');
        }

        return 0;
    }
}
