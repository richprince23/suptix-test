<?php

namespace App\Console\Commands;

use App\Models\PaymentInfo;
use App\Services\PaystackBalanceService;
use Illuminate\Console\Command;

class SyncPaystackBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paystack:sync-balances
                            {--organizer= : Sync balance for specific organizer ID}
                            {--force : Force sync even if recently updated}
                            {--all : Sync all approved accounts regardless of last sync time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Paystack subaccount balances with separate tracking for organizer and system admin balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Paystack balance sync with separate balance tracking...');

        $query = PaymentInfo::query()
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('paystack_subaccount_code');

        // Filter by specific organizer if provided
        if ($organizerId = $this->option('organizer')) {
            $query->where('organizer_id', $organizerId);
        }

        // Filter by accounts that need updating unless forced or all flag is used
        if (! $this->option('force') && ! $this->option('all')) {
            $query->where(function ($q) {
                $q->whereNull('subaccount_balance_last_synced')
                    ->orWhere('subaccount_balance_last_synced', '<', now()->subHours(6));
            });
        }

        $paymentInfos = $query->with('organizer')->get();

        if ($paymentInfos->isEmpty()) {
            $this->info('No payment accounts need balance updates.');

            return Command::SUCCESS;
        }

        $this->info("Found {$paymentInfos->count()} account(s) to sync.");

        $balanceService = new PaystackBalanceService;
        $successCount = 0;
        $failCount = 0;
        $totalSubaccountBalance = 0;
        $totalSystemAdminBalance = 0;
        $totalVolume = 0;

        $progressBar = $this->output->createProgressBar($paymentInfos->count());
        $progressBar->start();

        foreach ($paymentInfos as $paymentInfo) {
            try {
                $balanceData = $balanceService->syncSubaccountBalances($paymentInfo);

                $successCount++;
                $totalSubaccountBalance += $balanceData['subaccount_balance'];
                $totalSystemAdminBalance += $balanceData['system_admin_balance'];
                $totalVolume += $balanceData['total_transaction_volume'];

                if ($this->getOutput()->isVerbose()) {
                    $this->line("  ✓ {$paymentInfo->organizer->name}: Sub GHS ".number_format($balanceData['subaccount_balance'], 2).
                              ' | Admin GHS '.number_format($balanceData['system_admin_balance'], 2));
                }
            } catch (\Exception $e) {
                $failCount++;
                if ($this->getOutput()->isVerbose()) {
                    $this->line("  ✗ Error syncing {$paymentInfo->organizer->name}: {$e->getMessage()}");
                }
            }

            $progressBar->advance();

            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Balance sync completed!');
        $this->info("Successfully synced: {$successCount}");

        if ($failCount > 0) {
            $this->warn("Failed to sync: {$failCount}");
        }

        if ($successCount > 0) {
            $this->newLine();
            $this->info('Balance Summary:');
            $this->line('Total Organizer Balances: GHS '.number_format($totalSubaccountBalance, 2));
            $this->line('Total System Admin Balances: GHS '.number_format($totalSystemAdminBalance, 2));
            $this->line('Total Transaction Volume: GHS '.number_format($totalVolume, 2));
            $this->line('Combined Balance: GHS '.number_format($totalSubaccountBalance + $totalSystemAdminBalance, 2));

            if ($totalVolume > 0) {
                $adminPercentage = ($totalSystemAdminBalance / $totalVolume) * 100;
                $this->line('System Admin Share: '.number_format($adminPercentage, 1).'%');
            }
        }

        return Command::SUCCESS;
    }
}
