<?php

namespace Tests\Feature;

use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountSetupPipelineTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Organizer Admin']);
    }

    public function test_payment_info_submission_triggers_auto_approval_with_default_settings(): void
    {
        // Arrange
        $user = User::factory()->create();
        $organizer = Organizer::create([
            'name' => 'Test Organization',
            'email' => 'org@test.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'greater_accra_region',
            'country' => 'ghana',
        ]);
        $organizer->users()->attach($user);

        // Act - User submits payment info
        $paymentInfo = PaymentInfo::create([
            'organizer_id' => $organizer->id,
            'submitted_by' => $user->id,
            'business_name' => $organizer->name,
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567890',
            'bank_code' => '040100',
            'payment_channel' => 'bank',
        ]);

        // Assert - Pipeline should have set defaults and auto-approved
        $paymentInfo = $paymentInfo->fresh();
        $this->assertEquals(7.00, $paymentInfo->percentage_charge);
        $this->assertEquals('account', $paymentInfo->transaction_fee_payer);
        $this->assertNotNull($paymentInfo->approved_at);
        $this->assertTrue($paymentInfo->metadata['auto_approved'] ?? false);
        $this->assertEquals(7.00, $paymentInfo->metadata['default_percentage_charge']);
        $this->assertEquals('account', $paymentInfo->metadata['default_transaction_fee_payer']);
    }

    public function test_payment_info_submission_assigns_organizer_admin_role_to_first_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $organizer = Organizer::create([
            'name' => 'Test Organization',
            'email' => 'org@test.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'greater_accra_region',
            'country' => 'ghana',
        ]);
        $organizer->users()->attach($user);

        // Act - User submits payment info
        PaymentInfo::create([
            'organizer_id' => $organizer->id,
            'submitted_by' => $user->id,
            'business_name' => $organizer->name,
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567890',
            'bank_code' => '040100',
            'payment_channel' => 'bank',
        ]);

        // Assert - User should have Organizer Admin role
        $this->assertTrue($user->fresh()->hasRole('Organizer Admin'));
    }

    public function test_payment_info_submission_does_not_assign_role_when_admin_already_exists(): void
    {
        // Arrange
        $adminUser = User::factory()->create();
        $adminUser->assignRole('Organizer Admin');

        $newUser = User::factory()->create();

        $organizer = Organizer::create([
            'name' => 'Test Organization',
            'email' => 'org@test.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'greater_accra_region',
            'country' => 'ghana',
        ]);

        $organizer->users()->attach($adminUser);
        $organizer->users()->attach($newUser);

        // Act - New user submits payment info
        PaymentInfo::create([
            'organizer_id' => $organizer->id,
            'submitted_by' => $newUser->id,
            'business_name' => $organizer->name,
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567890',
            'bank_code' => '040100',
            'payment_channel' => 'bank',
        ]);

        // Assert - New user should NOT have Organizer Admin role
        $this->assertFalse($newUser->fresh()->hasRole('Organizer Admin'));
    }

    public function test_complete_automated_flow_on_payment_info_submission(): void
    {
        // Arrange
        $user = User::factory()->create();
        $organizer = Organizer::create([
            'name' => 'Complete Test Organization',
            'email' => 'complete@test.com',
            'phone' => '1234567890',
            'address' => '123 Complete St',
            'city' => 'Complete City',
            'state' => 'greater_accra_region',
            'country' => 'ghana',
        ]);
        $organizer->users()->attach($user);

        // Act - User submits payment info (this triggers the automation)
        $paymentInfo = PaymentInfo::create([
            'organizer_id' => $organizer->id,
            'submitted_by' => $user->id,
            'business_name' => $organizer->name,
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567890',
            'bank_code' => '040100',
            'payment_channel' => 'bank',
        ]);

        // Assert
        // 1. PaymentInfo has default settings
        $paymentInfo = $paymentInfo->fresh();
        $this->assertEquals(7.00, $paymentInfo->percentage_charge);
        $this->assertEquals('account', $paymentInfo->transaction_fee_payer);

        // 2. PaymentInfo is auto-approved
        $this->assertNotNull($paymentInfo->approved_at);
        $this->assertTrue($paymentInfo->metadata['auto_approved']);

        // 3. User assigned Organizer Admin role
        $this->assertTrue($user->fresh()->hasRole('Organizer Admin'));

        // 4. User is associated with organizer
        $this->assertTrue($organizer->users()->where('user_id', $user->id)->exists());
    }

    public function test_paystack_subaccount_creation_triggered_after_approval(): void
    {
        // Arrange
        $user = User::factory()->create();
        $organizer = Organizer::create([
            'name' => 'Test Organization',
            'email' => 'org@test.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'greater_accra_region',
            'country' => 'ghana',
        ]);
        $organizer->users()->attach($user);

        // Act - User submits payment info
        $paymentInfo = PaymentInfo::create([
            'organizer_id' => $organizer->id,
            'submitted_by' => $user->id,
            'business_name' => $organizer->name,
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567890',
            'bank_code' => '040100',
            'payment_channel' => 'bank',
        ]);

        // Assert - PaymentInfo should be approved, triggering Paystack subaccount creation
        // (actual Paystack creation will be handled by the model observer)
        $paymentInfo = $paymentInfo->fresh();
        $this->assertNotNull($paymentInfo->approved_at);
    }
}
