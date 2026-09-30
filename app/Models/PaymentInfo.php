<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class PaymentInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organizer_id',
        'submitted_by',
        'paystack_subaccount_code',
        'business_name',
        'settlement_bank',
        'account_number',
        'account_name',
        'bank_code',
        'payment_channel',
        'percentage_charge',
        'transaction_fee_payer',
        'metadata',
        'is_verified',
        'is_active',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'submitted_at',
        'verified_at',
        'paystack_verified',
        'verification_response',
        'admin_notified',
        'admin_notified_at',
        'available_balance',
        'ledger_balance',
        'balance_last_updated',
        'settlement_schedule',
        'minimum_settlement_amount',
        'subaccount_created_at',
        'paystack_response',
        // New balance tracking fields
        'subaccount_balance',
        'system_admin_balance',
        'total_transaction_volume',
        'total_transactions',
        'successful_transactions',
        'pending_transactions',
        'last_transaction_at',
        'last_transaction_reference',
        'last_transaction_amount',
        'subaccount_balance_last_synced',
        'balance_sync_log',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paystack_response' => 'array',
        'verification_response' => 'array',
        'balance_sync_log' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'paystack_verified' => 'boolean',
        'admin_notified' => 'boolean',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'admin_notified_at' => 'datetime',
        'balance_last_updated' => 'datetime',
        'subaccount_created_at' => 'datetime',
        'subaccount_balance_last_synced' => 'datetime',
        'last_transaction_at' => 'datetime',
        'percentage_charge' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'ledger_balance' => 'decimal:2',
        'minimum_settlement_amount' => 'decimal:2',
        'subaccount_balance' => 'decimal:2',
        'system_admin_balance' => 'decimal:2',
        'total_transaction_volume' => 'decimal:2',
        'last_transaction_amount' => 'decimal:2',
    ];

    // Relationships
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'submitted_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function (PaymentInfo $model) {
            $banks = [
                '190100' => 'Stanbic Bank Ghana',
                '030100' => 'Absa Bank Ghana',
                '280100' => 'Access Bank Ghana',
                '080100' => 'ADB Bank Limited',
                '070101' => 'ARB Apex Bank',
                '140100' => 'CAL Bank',
                '340100' => 'Consolidated Bank Ghana Limited',
                '130100' => 'Ecobank Ghana',
                '060100' => 'UBA Ghana',
                '240100' => 'Fidelity Bank Ghana',
                '040100' => 'GCB Bank Limited',
                '180100' => 'Prudential Bank',
                '110100' => 'Republic Bank Ghana',
                '090100' => 'Societe Generale Ghana',
                '020100' => 'Standard Chartered Bank Ghana',
                '100100' => 'Universal Merchant Bank',
                '120100' => 'Zenith Bank Ghana',
                '010100' => 'Bank of Ghana',
                '230100' => 'GT Bank Ghana',
                'MTN' => 'MTN Mobile Money',
                'VOD' => 'Vodafone Cash',
                'ATL' => 'AirtelTigo Money',
                'ZEEPAY' => 'Zeepay',
            ];
            Log::info($banks[$model->bank_code]);
            $model->settlement_bank = $banks[$model->bank_code];
        });

        static::updated(function (PaymentInfo $model) {
            // Check if status was changed to approved
            if ($model->isDirty('status') && $model->status === 'approved') {
                try {
                    // Only create Paystack subaccount if it doesn't exist yet and wasn't created via pipeline
                    $metadata = $model->metadata ?? [];
                    $createdViaAutomation = $metadata['paystack_created_via_pipeline'] ?? false;

                    if (!$model->paystack_subaccount_code && !$createdViaAutomation) {
                        // Create Paystack subaccount
                        \App\Filament\SystemAdmin\Resources\PaymentInfoResource::createPaystackSubaccount($model);
                    } else {
                        Log::info('Skipping Paystack subaccount creation - already created or created via pipeline', [
                            'payment_info_id' => $model->id,
                            'has_subaccount_code' => !is_null($model->paystack_subaccount_code),
                            'created_via_pipeline' => $createdViaAutomation,
                        ]);
                    }

                    // Note: Role assignment is now handled by PaymentInfoObserver which dispatches a job
                } catch (\Exception $e) {
                    Log::error('Failed to process approval in model observer', [
                        'payment_info_id' => $model->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Sync with Paystack when relevant fields are updated
            // Only sync if changes are being made by system admin or through system admin panel
            if ($model->shouldSyncWithPaystack() && static::isSystemAdminContext()) {
                static::syncWithPaystack($model);
            }
        });

        static::deleting(function (PaymentInfo $model) {
            // Delete from Paystack when model is being deleted (soft delete)
            if ($model->paystack_subaccount_code) {
                static::deleteFromPaystack($model);
            }
        });
    }

    // Accessors
    public function getFormattedAvailableBalanceAttribute(): string
    {
        return 'GHS '.number_format((float) $this->available_balance, 2);
    }

    public function getFormattedLedgerBalanceAttribute(): string
    {
        return 'GHS '.number_format((float) $this->ledger_balance, 2);
    }

    public function getFormattedSubaccountBalanceAttribute(): string
    {
        return 'GHS '.number_format((float) $this->subaccount_balance, 2);
    }

    public function getFormattedSystemAdminBalanceAttribute(): string
    {
        return 'GHS '.number_format((float) $this->system_admin_balance, 2);
    }

    public function getFormattedTotalTransactionVolumeAttribute(): string
    {
        return 'GHS '.number_format((float) $this->total_transaction_volume, 2);
    }

    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->subaccount_balance + (float) $this->system_admin_balance;
    }

    public function getFormattedTotalBalanceAttribute(): string
    {
        return 'GHS '.number_format($this->total_balance, 2);
    }

    public function getSystemAdminPercentageAttribute(): float
    {
        if ($this->total_transaction_volume == 0) {
            return 0;
        }

        return ((float) $this->system_admin_balance / (float) $this->total_transaction_volume) * 100;
    }

    public function getSubaccountPercentageAttribute(): float
    {
        if ($this->total_transaction_volume == 0) {
            return 0;
        }

        return ((float) $this->subaccount_balance / (float) $this->total_transaction_volume) * 100;
    }

    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if (! $this->paystack_subaccount_code) {
            return 'pending_creation';
        }

        if (! $this->is_verified) {
            return 'pending_verification';
        }

        return 'active';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'pending_verification' => 'warning',
            'pending_creation' => 'info',
            'inactive' => 'danger',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeWithSubaccount($query)
    {
        return $query->whereNotNull('paystack_subaccount_code');
    }

    // Business methods
    public function isReadyForPayments(): bool
    {
        return $this->is_active &&
               $this->is_verified &&
               ! empty($this->paystack_subaccount_code);
    }

    public function needsBalanceUpdate(): bool
    {
        if (! $this->balance_last_updated) {
            return true;
        }

        // Update if balance is older than 1 hour
        return $this->balance_last_updated->diffInHours(now()) > 1;
    }

    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    public function updateBalance(float $availableBalance, float $ledgerBalance): void
    {
        $this->update([
            'available_balance' => $availableBalance,
            'ledger_balance' => $ledgerBalance,
            'balance_last_updated' => now(),
        ]);
    }

    public function updateSeparateBalances(array $balanceData): void
    {
        $updateData = [
            'subaccount_balance' => $balanceData['subaccount_balance'] ?? $this->subaccount_balance,
            'system_admin_balance' => $balanceData['system_admin_balance'] ?? $this->system_admin_balance,
            'total_transaction_volume' => $balanceData['total_transaction_volume'] ?? $this->total_transaction_volume,
            'subaccount_balance_last_synced' => now(),
        ];

        if (isset($balanceData['transaction_counts'])) {
            $updateData = array_merge($updateData, [
                'total_transactions' => $balanceData['transaction_counts']['total'] ?? $this->total_transactions,
                'successful_transactions' => $balanceData['transaction_counts']['successful'] ?? $this->successful_transactions,
                'pending_transactions' => $balanceData['transaction_counts']['pending'] ?? $this->pending_transactions,
            ]);
        }

        if (isset($balanceData['last_transaction'])) {
            $updateData = array_merge($updateData, [
                'last_transaction_at' => $balanceData['last_transaction']['created_at'] ?? null,
                'last_transaction_reference' => $balanceData['last_transaction']['reference'] ?? null,
                'last_transaction_amount' => $balanceData['last_transaction']['amount'] ?? null,
            ]);
        }

        // Log the balance sync for audit trail
        $syncLog = $this->balance_sync_log ?? [];
        $syncLog[] = [
            'synced_at' => now()->toISOString(),
            'previous_subaccount_balance' => $this->subaccount_balance,
            'new_subaccount_balance' => $updateData['subaccount_balance'],
            'previous_system_admin_balance' => $this->system_admin_balance,
            'new_system_admin_balance' => $updateData['system_admin_balance'],
            'sync_method' => $balanceData['sync_method'] ?? 'manual',
        ];

        // Keep only last 50 sync logs
        if (count($syncLog) > 50) {
            $syncLog = array_slice($syncLog, -50);
        }

        $updateData['balance_sync_log'] = $syncLog;

        $this->update($updateData);
    }

    public function recordTransaction(array $transactionData): void
    {
        $amount = (float) $transactionData['amount'];

        // Use actual Paystack split data if available, otherwise calculate
        if (isset($transactionData['fees_split'])) {
            // Use actual amounts from Paystack (convert from pesewas to cedis)
            $subaccountAmount = isset($transactionData['fees_split']['subaccount'])
                ? (float) $transactionData['fees_split']['subaccount'] / 100
                : 0;
            $systemAdminAmount = isset($transactionData['fees_split']['integration'])
                ? (float) $transactionData['fees_split']['integration'] / 100
                : 0;

            Log::info('Recording transaction with actual Paystack split data', [
                'payment_info_id' => $this->id,
                'reference' => $transactionData['reference'] ?? 'unknown',
                'total_amount' => $amount,
                'subaccount_amount' => $subaccountAmount,
                'system_admin_amount' => $systemAdminAmount,
                'fees_split' => $transactionData['fees_split'],
            ]);
        } else {
            // Fallback to percentage calculation
            $systemAdminAmount = $amount * ((float) $this->percentage_charge / 100);
            $subaccountAmount = $amount - $systemAdminAmount;

            Log::info('Recording transaction with calculated split', [
                'payment_info_id' => $this->id,
                'reference' => $transactionData['reference'] ?? 'unknown',
                'percentage_charge' => $this->percentage_charge,
                'calculated_subaccount_amount' => $subaccountAmount,
                'calculated_system_admin_amount' => $systemAdminAmount,
            ]);
        }

        $this->updateSeparateBalances([
            'subaccount_balance' => $this->subaccount_balance + $subaccountAmount,
            'system_admin_balance' => $this->system_admin_balance + $systemAdminAmount,
            'total_transaction_volume' => $this->total_transaction_volume + $amount,
            'transaction_counts' => [
                'total' => $this->total_transactions + 1,
                'successful' => $this->successful_transactions + ($transactionData['status'] === 'success' ? 1 : 0),
                'pending' => $this->pending_transactions + ($transactionData['status'] === 'pending' ? 1 : 0),
            ],
            'last_transaction' => [
                'created_at' => $transactionData['created_at'] ?? now(),
                'reference' => $transactionData['reference'] ?? null,
                'amount' => $amount,
            ],
            'sync_method' => 'transaction_recording',
        ]);
    }

    public function needsBalanceSyncFromPaystack(): bool
    {
        if (! $this->subaccount_balance_last_synced) {
            return true;
        }

        // Sync if balance is older than 6 hours
        return $this->subaccount_balance_last_synced->diffInHours(now()) > 6;
    }

    /**
     * Determine if this model should sync with Paystack based on changed fields
     */
    public function shouldSyncWithPaystack(): bool
    {
        // Only sync if we have a Paystack subaccount code
        if (! $this->paystack_subaccount_code) {
            return false;
        }

        // Fields that require Paystack sync when updated
        $syncableFields = [
            'business_name',
            'bank_code',
            'account_number',
            'percentage_charge',
        ];

        // Check if any syncable field was changed
        foreach ($syncableFields as $field) {
            if ($this->isDirty($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sync this PaymentInfo with Paystack
     */
    public static function syncWithPaystack(PaymentInfo $model): void
    {
        try {
            $paystackService = app(\App\Services\PaystackSubaccountService::class);
            $paystackService->updateSubaccount($model);

            Log::info('PaymentInfo successfully synced with Paystack', [
                'payment_info_id' => $model->id,
                'subaccount_code' => $model->paystack_subaccount_code,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync PaymentInfo with Paystack', [
                'payment_info_id' => $model->id,
                'subaccount_code' => $model->paystack_subaccount_code,
                'error' => $e->getMessage(),
                'changed_fields' => $model->getDirty(),
            ]);

            // Store sync error for admin review
            $model->updateQuietly([
                'metadata' => array_merge($model->metadata ?? [], [
                    'last_sync_error' => [
                        'timestamp' => now()->toISOString(),
                        'error' => $e->getMessage(),
                        'changed_fields' => $model->getDirty(),
                    ],
                ]),
            ]);
        }
    }

    /**
     * Delete this PaymentInfo's subaccount from Paystack
     */
    public static function deleteFromPaystack(PaymentInfo $model): void
    {
        try {
            $paystackService = app(\App\Services\PaystackSubaccountService::class);
            $paystackService->deleteSubaccount($model);

            Log::info('PaymentInfo subaccount successfully deleted from Paystack during soft delete', [
                'payment_info_id' => $model->id,
                'subaccount_code' => $model->paystack_subaccount_code,
                'business_name' => $model->business_name,
                'context' => static::getActionContext(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete PaymentInfo subaccount from Paystack during soft delete', [
                'payment_info_id' => $model->id,
                'subaccount_code' => $model->paystack_subaccount_code,
                'business_name' => $model->business_name,
                'error' => $e->getMessage(),
                'context' => static::getActionContext(),
            ]);

            // Store deletion error for admin review but don't prevent local deletion
            $model->updateQuietly([
                'metadata' => array_merge($model->metadata ?? [], [
                    'paystack_deletion_error' => [
                        'timestamp' => now()->toISOString(),
                        'error' => $e->getMessage(),
                        'attempted_to_delete_from_paystack' => true,
                        'context' => static::getActionContext(),
                    ],
                ]),
            ]);
        }
    }

    /**
     * Manually trigger a sync with Paystack
     */
    public function manualSyncWithPaystack(): array
    {
        if (! $this->paystack_subaccount_code) {
            throw new \InvalidArgumentException('Cannot sync: PaymentInfo does not have a Paystack subaccount code');
        }

        $paystackService = app(\App\Services\PaystackSubaccountService::class);

        return $paystackService->updateSubaccount($this);
    }

    /**
     * Check if the last sync had errors
     */
    public function hasRecentSyncError(): bool
    {
        $metadata = $this->metadata ?? [];

        if (! isset($metadata['last_sync_error'])) {
            return false;
        }

        $errorTimestamp = $metadata['last_sync_error']['timestamp'] ?? null;

        if (! $errorTimestamp) {
            return false;
        }

        // Consider error recent if it's less than 24 hours old
        $errorTime = \Carbon\Carbon::parse($errorTimestamp);

        return $errorTime->diffInHours(now()) < 24;
    }

    /**
     * Get the last sync error message
     */
    public function getLastSyncError(): ?string
    {
        $metadata = $this->metadata ?? [];

        return $metadata['last_sync_error']['error'] ?? null;
    }

    /**
     * Clear sync error from metadata
     */
    public function clearSyncError(): void
    {
        $metadata = $this->metadata ?? [];
        unset($metadata['last_sync_error']);
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Check if the current context is a system admin operation
     */
    private static function isSystemAdminContext(): bool
    {
        // Check if current user is system admin
        if (auth()->check() && auth()->user()->is_system_admin) {
            return true;
        }

        // Check if we're in the system admin panel context (Filament)
        if (request()->is('system-admin/*')) {
            return true;
        }

        // Check if this is being called from a system admin resource
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($backtrace as $trace) {
            if (isset($trace['class']) && str_contains($trace['class'], 'SystemAdmin')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get context information for logging
     */
    private static function getActionContext(): array
    {
        $context = [
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'is_system_admin' => auth()->user()?->is_system_admin ?? false,
            'request_path' => request()->path(),
            'user_agent' => request()->userAgent(),
        ];

        // Determine the source of the action
        if (request()->is('system-admin/*')) {
            $context['source'] = 'system_admin_panel';
        } elseif (request()->is('admin/*')) {
            $context['source'] = 'admin_panel';
        } elseif (app()->runningInConsole()) {
            $context['source'] = 'console_command';
        } else {
            $context['source'] = 'web_interface';
        }

        return $context;
    }
}
