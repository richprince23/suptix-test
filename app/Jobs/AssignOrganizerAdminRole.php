<?php

namespace App\Jobs;

use App\Models\PaymentInfo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssignOrganizerAdminRole implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PaymentInfo $paymentInfo
    ) {
        // Set queue priority for important role assignments
        // $this->queue = 'high';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only proceed if payment info is approved (check the raw database value, not the computed status)
        $rawStatus = $this->paymentInfo->getRawOriginal('status') ?? $this->paymentInfo->getAttributes()['status'] ?? null;

        if ($rawStatus !== 'approved') {
            Log::info('Job skipped: PaymentInfo is not approved', [
                'payment_info_id' => $this->paymentInfo->id,
                'raw_status' => $rawStatus,
                'computed_status' => $this->paymentInfo->status,
            ]);
            return;
        }

        Log::info('Processing organizer admin role assignment', [
            'payment_info_id' => $this->paymentInfo->id,
            'organizer_id' => $this->paymentInfo->organizer_id,
            'submitted_by' => $this->paymentInfo->submitted_by,
        ]);

        try {
            $this->assignOrganizerAdminRole();

            Log::info('Organizer admin role assignment completed successfully', [
                'payment_info_id' => $this->paymentInfo->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to assign organizer admin role in job', [
                'payment_info_id' => $this->paymentInfo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AssignOrganizerAdminRole job failed permanently', [
            'payment_info_id' => $this->paymentInfo->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Assign organizer admin role without Filament notifications
     */
    private function assignOrganizerAdminRole(): void
    {
        $organizer = $this->paymentInfo->organizer;
        $submittedByUser = $this->paymentInfo->submittedBy;


        if (!$organizer) {
            Log::error('No organizer found for payment info', [
                'payment_info_id' => $this->paymentInfo->id,
            ]);
            return;
        }

        // Check if role exists, create if not
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Organizer Admin']);

        // Get all users associated with this organizer
        $allOrganizerUsers = $organizer->users;

        // Check if there's already an organizer admin
        $hasOrganizerAdmin = $allOrganizerUsers->filter(fn($user) => $user->hasRole('Organizer Admin'))->count() > 0;

        $shouldAssignRole = false;

        // Logic: Only assign organizer admin role if:
        // 1. There's only one user in the organization, OR
        // 2. There is no existing organizer admin
        if ($allOrganizerUsers->count() === 1 || !$hasOrganizerAdmin) {
            $shouldAssignRole = true;
        }

        if (!$shouldAssignRole) {
            Log::info('Role assignment skipped - organization already has admin or multiple users', [
                'payment_info_id' => $this->paymentInfo->id,
                'organizer_id' => $organizer->id,
                'user_count' => $allOrganizerUsers->count(),
                'has_admin' => $hasOrganizerAdmin,
            ]);
            return;
        }

        // Assign role to the user who submitted the payment info
        if ($submittedByUser && !$submittedByUser->hasRole('Organizer Admin')) {
            $submittedByUser->assignRole('Organizer Admin');

            // Also make sure the user is associated with the organizer
            if (!$organizer->users()->where('user_id', $submittedByUser->id)->exists()) {
                $organizer->users()->attach($submittedByUser->id);
            }

            Log::info('Assigned organizer admin role to submitter', [
                'user_id' => $submittedByUser->id,
                'user_email' => $submittedByUser->email,
                'organizer_id' => $organizer->id,
                'payment_info_id' => $this->paymentInfo->id,
                'reason' => $allOrganizerUsers->count() === 1 ? 'single_user' : 'no_existing_admin',
            ]);
        } else {
            Log::info('User already has organizer admin role or submitted_by user not found', [
                'payment_info_id' => $this->paymentInfo->id,
                'submitted_by' => $this->paymentInfo->submitted_by,
                'user_exists' => $submittedByUser ? 'yes' : 'no',
                'has_role' => $submittedByUser ? $submittedByUser->hasRole('Organizer Admin') : 'unknown',
            ]);
        }
    }
}
