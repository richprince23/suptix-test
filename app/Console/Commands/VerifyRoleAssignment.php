<?php

namespace App\Console\Commands;

use App\Models\PaymentInfo;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class VerifyRoleAssignment extends Command
{
    protected $signature = 'verify:role-assignment {--payment-info-id=} {--test-approval}';

    protected $description = 'Verify that organizer admin roles are properly assigned when payment info is approved';

    public function handle()
    {
        $this->info('🔍 Verifying Organizer Admin Role Assignment...');
        $this->newLine();

        if ($this->option('test-approval')) {
            return $this->testApprovalProcess();
        }

        // Check existing approved payment infos
        $this->checkExistingApprovals();

        // Check role existence
        $this->checkRoleSetup();

        // Provide recommendations
        $this->provideRecommendations();

        return 0;
    }

    private function checkExistingApprovals()
    {
        $this->line('📋 <info>Checking existing approved payment infos...</info>');

        $paymentInfoId = $this->option('payment-info-id');

        if ($paymentInfoId) {
            $paymentInfos = PaymentInfo::where('id', $paymentInfoId)->get();
            if ($paymentInfos->isEmpty()) {
                $this->error("❌ PaymentInfo with ID {$paymentInfoId} not found");

                return;
            }
        } else {
            $paymentInfos = PaymentInfo::where('status', 'approved')
                ->with(['organizer.users', 'submittedBy'])
                ->get();
        }

        if ($paymentInfos->isEmpty()) {
            $this->warn('⚠️  No approved payment infos found');

            return;
        }

        $this->table(
            ['ID', 'Organizer', 'Submitted By', 'Has Admin Role?', 'Total Org Users', 'Org Admins', 'Status'],
            $paymentInfos->map(function ($paymentInfo) {
                $submittedBy = $paymentInfo->submittedBy;
                $organizer = $paymentInfo->organizer;

                $hasAdminRole = $submittedBy ? $submittedBy->hasRole('Organizer Admin') : false;
                $totalUsers = $organizer ? $organizer->users->count() : 0;
                $adminUsers = $organizer ? $organizer->users->filter(fn ($user) => $user->hasRole('Organizer Admin'))->count() : 0;

                $status = $this->determineStatus($paymentInfo, $hasAdminRole, $totalUsers, $adminUsers);

                return [
                    $paymentInfo->id,
                    $organizer->name ?? 'N/A',
                    $submittedBy ? "{$submittedBy->name} (ID: {$submittedBy->id})" : 'N/A',
                    $hasAdminRole ? '✅ Yes' : '❌ No',
                    $totalUsers,
                    $adminUsers,
                    $status,
                ];
            })->toArray()
        );
    }

    private function determineStatus($paymentInfo, $hasAdminRole, $totalUsers, $adminUsers)
    {
        if (! $paymentInfo->submittedBy) {
            return '⚠️  No submitter';
        }

        if ($hasAdminRole) {
            return '✅ Correct';
        }

        if ($totalUsers === 1 && $adminUsers === 0) {
            return '❌ Should have role (single user)';
        }

        if ($adminUsers === 0) {
            return '❌ Should have role (no admin exists)';
        }

        if ($adminUsers > 0) {
            return '✅ Skipped (admin exists)';
        }

        return '❓ Unknown';
    }

    private function checkRoleSetup()
    {
        $this->newLine();
        $this->line('🔧 <info>Checking role setup...</info>');

        $organizerAdminRole = Role::where('name', 'Organizer Admin')->first();

        if (! $organizerAdminRole) {
            $this->error('❌ Organizer Admin role does not exist');
            $this->line('   Run: Role::create([\'name\' => \'Organizer Admin\'])');
        } else {
            $this->info('✅ Organizer Admin role exists');

            $usersWithRole = User::role('Organizer Admin')->count();
            $this->line("   📊 {$usersWithRole} users have this role");
        }
    }

    private function provideRecommendations()
    {
        $this->newLine();
        $this->line('💡 <info>Recommendations:</info>');

        // Find payment infos that should have admin role but don't
        $problematicCases = PaymentInfo::where('status', 'approved')
            ->whereNotNull('submitted_by')
            ->with(['organizer.users', 'submittedBy'])
            ->get()
            ->filter(function ($paymentInfo) {
                $submittedBy = $paymentInfo->submittedBy;
                $organizer = $paymentInfo->organizer;

                if (! $submittedBy || ! $organizer) {
                    return false;
                }

                $hasAdminRole = $submittedBy->hasRole('Organizer Admin');
                $totalUsers = $organizer->users->count();
                $adminUsers = $organizer->users->filter(fn ($user) => $user->hasRole('Organizer Admin'))->count();

                // Should have role if: single user OR no admin exists
                $shouldHaveRole = ($totalUsers === 1) || ($adminUsers === 0);

                return $shouldHaveRole && ! $hasAdminRole;
            });

        if ($problematicCases->isEmpty()) {
            $this->info('✅ All role assignments appear correct');
        } else {
            $this->warn("⚠️  Found {$problematicCases->count()} cases that may need role assignment");

            foreach ($problematicCases as $case) {
                $this->line("   • PaymentInfo ID {$case->id}: User {$case->submittedBy->email} should have organizer_admin role");
            }

            $this->newLine();
            $this->line('To fix these manually, you can run:');
            foreach ($problematicCases as $case) {
                $this->line("   php artisan tinker --execute=\"App\\Models\\User::find({$case->submittedBy->id})->assignRole('Organizer Admin')\"");
            }
        }
    }

    private function testApprovalProcess()
    {
        $this->info('🧪 Testing approval process...');

        // Find a pending payment info to test with
        $pendingPaymentInfo = PaymentInfo::where('status', 'pending')->first();

        if (! $pendingPaymentInfo) {
            $this->warn('⚠️  No pending payment infos found to test with');
            $this->line('   Create a test payment info submission first');

            return 1;
        }

        $this->line("Testing with PaymentInfo ID: {$pendingPaymentInfo->id}");
        $this->line("Organizer: {$pendingPaymentInfo->organizer->name}");
        $submittedByEmail = $pendingPaymentInfo->submittedBy ? $pendingPaymentInfo->submittedBy->email : 'Unknown';
        $this->line("Submitted by: {$submittedByEmail}");

        if (! $this->confirm('Do you want to simulate the approval process?')) {
            return 0;
        }

        // Simulate the approval process
        try {
            $this->line('🔄 Simulating approval...');

            // Call the same method that's used in the PaymentInfoResource
            \App\Filament\Resources\PaymentInfoResource::assignOrganizerAdminRole($pendingPaymentInfo);

            $this->info('✅ Role assignment process completed');

            // Check if role was assigned
            $submittedBy = $pendingPaymentInfo->submittedBy;
            if ($submittedBy && $submittedBy->hasRole('Organizer Admin')) {
                $this->info('✅ Organizer Admin role successfully assigned');
            } else {
                $this->warn('⚠️  Role was not assigned (this may be expected based on business rules)');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error during role assignment: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
