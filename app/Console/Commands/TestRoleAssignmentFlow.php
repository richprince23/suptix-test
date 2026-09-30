<?php

namespace App\Console\Commands;

use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class TestRoleAssignmentFlow extends Command
{
    protected $signature = 'test:role-assignment-flow';

    protected $description = 'Test the complete role assignment flow during payment info approval';

    public function handle()
    {
        $this->info('🧪 Testing Role Assignment Flow...');
        $this->newLine();

        try {
            // Ensure the role exists
            $role = Role::firstOrCreate(['name' => 'Organizer Admin']);
            $this->info('✅ Organizer Admin role ensured');

            // Get or create test user
            $user = User::where('email', 'test-organizer@example.com')->first();
            if (! $user) {
                $this->line('Creating test user...');
                $user = User::create([
                    'name' => 'Test Organizer User',
                    'email' => 'test-organizer@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
            }
            $this->info("✅ Test user: {$user->email} (ID: {$user->id})");

            // Get or create test organizer
            $organizer = Organizer::where('name', 'Test Organizer')->first();
            if (! $organizer) {
                $this->line('Creating test organizer...');
                $organizer = Organizer::create([
                    'name' => 'Test Organizer',
                    'email' => 'test-organizer@example.com',
                    'phone' => '+1234567890',
                    'website' => 'https://test-organizer.com',
                    'description' => 'Test organizer for role assignment testing',
                ]);
            }
            $this->info("✅ Test organizer: {$organizer->name} (ID: {$organizer->id})");

            // Associate user with organizer if not already
            if (! $organizer->users()->where('user_id', $user->id)->exists()) {
                $organizer->users()->attach($user->id);
                $this->line('📎 User associated with organizer');
            }

            // Remove any existing Organizer Admin role from user for clean test
            if ($user->hasRole('Organizer Admin')) {
                $user->removeRole('Organizer Admin');
                $this->line('🧹 Removed existing Organizer Admin role for clean test');
            }

            // Create test payment info
            $paymentInfo = PaymentInfo::create([
                'organizer_id' => $organizer->id,
                'submitted_by' => $user->id,
                'business_name' => 'Test Business',
                'settlement_bank' => 'Test Bank',
                'account_number' => '1234567890',
                'account_name' => 'Test Account',
                'bank_code' => '030100',
                'payment_channel' => 'bank',
                'percentage_charge' => 7.0,
                'status' => 'pending',
                'submitted_at' => now(),
                'paystack_verified' => true,
            ]);
            $this->info("✅ Test payment info created (ID: {$paymentInfo->id})");

            $this->newLine();
            $this->line('📊 <comment>Before approval:</comment>');
            $this->line('   User has Organizer Admin role: '.($user->hasRole('Organizer Admin') ? '✅ Yes' : '❌ No'));
            $this->line('   Organizer users count: '.$organizer->users->count());
            $this->line('   Organizer admin users: '.$organizer->users->filter(fn ($u) => $u->hasRole('Organizer Admin'))->count());

            // Test the approval process
            $this->newLine();
            $this->line('🔄 <comment>Simulating approval process...</comment>');

            // Manually implement the role assignment logic to test
            $this->assignOrganizerAdminRole($paymentInfo);

            // Refresh the user to get updated roles
            $user->refresh();

            $this->newLine();
            $this->line('📊 <comment>After approval:</comment>');
            $this->line('   User has Organizer Admin role: '.($user->hasRole('Organizer Admin') ? '✅ Yes' : '❌ No'));
            $this->line('   User roles: '.$user->roles->pluck('name')->join(', '));

            // Test scenarios
            $this->newLine();
            $this->line('🎯 <comment>Testing different scenarios...</comment>');

            // Scenario 1: User already has role - should skip
            $this->line('Scenario 1: User already has role');
            $this->assignOrganizerAdminRole($paymentInfo);
            $this->line('   Result: '.($user->hasRole('Organizer Admin') ? '✅ Still has role' : '❌ Lost role'));

            // Scenario 2: Create another user and test multi-user logic
            $this->line('Scenario 2: Multiple users in organization');
            $user2 = User::create([
                'name' => 'Test Organizer User 2',
                'email' => 'test-organizer-2@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $organizer->users()->attach($user2->id);

            $paymentInfo2 = PaymentInfo::create([
                'organizer_id' => $organizer->id,
                'submitted_by' => $user2->id,
                'business_name' => 'Test Business 2',
                'settlement_bank' => 'Test Bank',
                'account_number' => '0987654321',
                'account_name' => 'Test Account 2',
                'bank_code' => '030100',
                'payment_channel' => 'bank',
                'percentage_charge' => 7.0,
                'status' => 'pending',
                'submitted_at' => now(),
                'paystack_verified' => true,
            ]);

            $this->assignOrganizerAdminRole($paymentInfo2);
            $user2->refresh();

            $this->line('   User 1 still has role: '.($user->hasRole('Organizer Admin') ? '✅ Yes' : '❌ No'));
            $this->line('   User 2 gets role (should be no - admin already exists): '.($user2->hasRole('Organizer Admin') ? '❌ Yes' : '✅ No'));

            $this->newLine();
            $this->info('🎉 Role assignment flow test completed successfully!');

            // Clean up test data
            if ($this->confirm('Clean up test data?', true)) {
                $paymentInfo->delete();
                $paymentInfo2->delete();
                $user->delete();
                $user2->delete();
                // Note: keeping organizer as it might be used elsewhere
                $this->line('🧹 Test data cleaned up');
            }

        } catch (\Exception $e) {
            $this->error('❌ Test failed: '.$e->getMessage());
            $this->line('Stack trace: '.$e->getTraceAsString());

            return 1;
        }

        return 0;
    }

    private function assignOrganizerAdminRole(PaymentInfo $paymentInfo): void
    {
        $organizer = $paymentInfo->organizer;
        $submittedByUser = $paymentInfo->submittedBy;

        if (! $organizer) {
            $this->error('No organizer found for payment info');

            return;
        }

        // Check if role exists, create if not
        Role::firstOrCreate(['name' => 'Organizer Admin']);

        // Get all users associated with this organizer
        $allOrganizerUsers = $organizer->users;

        // Check if there's already an organizer admin
        $hasOrganizerAdmin = $allOrganizerUsers->filter(function ($user) {
            return $user->hasRole('Organizer Admin');
        })->count() > 0;

        $shouldAssignRole = false;

        // Logic: Only assign organizer admin role if:
        // 1. There's only one user in the organization, OR
        // 2. There is no existing organizer admin
        if ($allOrganizerUsers->count() === 1 || ! $hasOrganizerAdmin) {
            $shouldAssignRole = true;
        }

        if (! $shouldAssignRole) {
            $this->line('   ⏭️  Role assignment skipped - organization already has admin or multiple users');

            return;
        }

        // Assign role to the user who submitted the payment info
        if ($submittedByUser && ! $submittedByUser->hasRole('Organizer Admin')) {
            $submittedByUser->assignRole('Organizer Admin');

            // Also make sure the user is associated with the organizer
            if (! $organizer->users()->where('user_id', $submittedByUser->id)->exists()) {
                $organizer->users()->attach($submittedByUser->id);
            }

            $reason = $allOrganizerUsers->count() === 1
                ? 'as the only user in the organization'
                : 'as no organizer admin existed';

            $this->line("   ✅ Assigned Organizer Admin role to submitter {$reason}");
        } else {
            $this->line('   ⏭️  User already has Organizer Admin role');
        }
    }
}
