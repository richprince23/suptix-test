<?php

namespace App\Console\Commands;

use App\Models\PaymentInfo;
use App\Services\PaystackSubaccountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPaystackSubaccounts extends Command
{
    protected $signature = 'paystack:sync-subaccounts 
                            {--dry-run : Run without making changes}
                            {--force : Force update even if recently synced}';

    protected $description = 'Sync all local subaccounts with fresh data from Paystack';

    private PaystackSubaccountService $paystackService;

    public function handle(): int
    {
        $this->paystackService = app(PaystackSubaccountService::class);

        $this->info('Starting Paystack subaccount sync...');

        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No changes will be made');
        }

        // Get all PaymentInfo records with Paystack subaccount codes
        $paymentInfos = PaymentInfo::withSubaccount()
            ->when(! $force, function ($query) {
                // Only sync records that haven't been synced recently (unless forced)
                return $query->where(function ($q) {
                    $q->whereNull('subaccount_balance_last_synced')
                        ->orWhere('subaccount_balance_last_synced', '<', now()->subHours(6));
                });
            })
            ->get();

        if ($paymentInfos->isEmpty()) {
            $this->info('No subaccounts found that need syncing.');

            return 0;
        }

        $this->info("Found {$paymentInfos->count()} subaccounts to sync");

        $progressBar = $this->output->createProgressBar($paymentInfos->count());
        $progressBar->start();

        $syncedCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($paymentInfos as $paymentInfo) {
            try {
                $this->syncSubaccount($paymentInfo, $isDryRun);
                $syncedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'business_name' => $paymentInfo->business_name,
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to sync subaccount', [
                    'payment_info_id' => $paymentInfo->id,
                    'subaccount_code' => $paymentInfo->paystack_subaccount_code,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        if ($isDryRun) {
            $this->info("DRY RUN COMPLETE: Would have synced {$syncedCount} subaccounts");
        } else {
            $this->info("Sync completed: {$syncedCount} subaccounts synced successfully");
        }

        if ($errorCount > 0) {
            $this->error("Encountered {$errorCount} errors:");

            $headers = ['Payment Info ID', 'Subaccount Code', 'Business Name', 'Error'];
            $rows = array_map(function ($error) {
                return [
                    $error['payment_info_id'],
                    $error['subaccount_code'],
                    $error['business_name'],
                    substr($error['error'], 0, 50).(strlen($error['error']) > 50 ? '...' : ''),
                ];
            }, array_slice($errors, 0, 10)); // Show first 10 errors

            $this->table($headers, $rows);

            if (count($errors) > 10) {
                $this->warn('... and '.(count($errors) - 10).' more errors. Check logs for full details.');
            }
        }

        // Also sync from Paystack to catch any new subaccounts
        if (! $isDryRun) {
            $this->info('Fetching all subaccounts from Paystack to identify any missing locally...');
            $this->syncFromPaystack();
        }

        return $errorCount > 0 ? 1 : 0;
    }

    private function syncSubaccount(PaymentInfo $paymentInfo, bool $isDryRun): void
    {
        if ($isDryRun) {
            // Just fetch to verify connection and data
            $this->paystackService->fetchSubaccount($paymentInfo);

            return;
        }

        // Fetch latest data from Paystack
        $response = $this->paystackService->fetchSubaccount($paymentInfo);
        $subaccountData = $response['data'] ?? [];

        if (empty($subaccountData)) {
            throw new \Exception('No subaccount data returned from Paystack');
        }

        // Update local record with fresh data
        $updateData = [
            'is_active' => $subaccountData['active'] ?? $paymentInfo->is_active,
            'settlement_schedule' => $subaccountData['settlement_schedule'] ?? $paymentInfo->settlement_schedule,
            'paystack_response' => $subaccountData,
            'subaccount_balance_last_synced' => now(),
        ];

        // Update metadata with any new information
        if (! empty($subaccountData['metadata'])) {
            $updateData['metadata'] = array_merge(
                $paymentInfo->metadata ?? [],
                $subaccountData['metadata']
            );
        }

        // Clear any previous sync errors if sync is successful
        $metadata = $updateData['metadata'] ?? $paymentInfo->metadata ?? [];
        unset($metadata['last_sync_error']);
        $updateData['metadata'] = $metadata;

        $paymentInfo->updateQuietly($updateData);

        $this->line("  ✓ Synced: {$paymentInfo->business_name} ({$paymentInfo->paystack_subaccount_code})");
    }

    private function syncFromPaystack(): void
    {
        try {
            $page = 1;
            $perPage = 50;
            $totalProcessed = 0;
            $newSubaccounts = 0;

            do {
                $response = $this->paystackService->listAllSubaccounts($perPage, $page);
                $subaccounts = $response['data'] ?? [];
                $meta = $response['meta'] ?? [];

                foreach ($subaccounts as $subaccountData) {
                    $subaccountCode = $subaccountData['subaccount_code'] ?? null;

                    if (! $subaccountCode) {
                        continue;
                    }

                    // Check if we have this subaccount locally
                    $existingPaymentInfo = PaymentInfo::where('paystack_subaccount_code', $subaccountCode)->first();

                    if (! $existingPaymentInfo) {
                        // Log subaccounts that exist on Paystack but not locally
                        // Don't create them automatically as they need organizer assignment
                        Log::info('Found Paystack subaccount not in local database', [
                            'subaccount_code' => $subaccountCode,
                            'business_name' => $subaccountData['business_name'] ?? 'Unknown',
                            'account_number' => $subaccountData['account_number'] ?? 'Unknown',
                        ]);

                        $businessName = $subaccountData['business_name'] ?? 'Unknown';
                        $this->warn("  ⚠ Found orphaned subaccount on Paystack: {$subaccountCode} ({$businessName})");
                        $newSubaccounts++;
                    }

                    $totalProcessed++;
                }

                $page++;
            } while (! empty($subaccounts) && $page <= ($meta['pageCount'] ?? 1));

            $this->info("Processed {$totalProcessed} subaccounts from Paystack");

            if ($newSubaccounts > 0) {
                $this->warn("Found {$newSubaccounts} orphaned subaccounts on Paystack that don't exist locally");
                $this->warn('Check logs for details. These may need manual review.');
            }

        } catch (\Exception $e) {
            $this->error('Failed to fetch subaccounts from Paystack: '.$e->getMessage());
            Log::error('Failed to fetch all subaccounts from Paystack', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
