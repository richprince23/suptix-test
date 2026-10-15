<?php

namespace Tests\Feature;

use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\User;
use App\Services\PaystackSubaccountService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaystackSubaccountSyncTest extends TestCase
{
    use DatabaseTransactions;

    private PaymentInfo $paymentInfo;

    private PaystackSubaccountService $paystackService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data manually
        $user = User::factory()->create();

        $organizer = Organizer::create([
            'name' => 'Test Organizer',
            'email' => 'test@example.com',
            'phone' => '+233123456789',
            'description' => 'Test organizer description',
        ]);

        $this->paymentInfo = PaymentInfo::create([
            'organizer_id' => $organizer->id,
            'submitted_by' => $user->id,
            'business_name' => 'Test Business',
            'bank_code' => '040100',
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'payment_channel' => 'bank',
            'percentage_charge' => 10.0,
            'transaction_fee_payer' => 'subaccount',
            'paystack_subaccount_code' => 'ACCT_test123',
            'status' => 'approved',
            'is_verified' => true,
            'is_active' => true,
            'paystack_verified' => true,
        ]);

        $this->paystackService = new PaystackSubaccountService;
    }

    #[Test]
    public function it_can_determine_when_to_sync_with_paystack()
    {
        // Should not sync when no subaccount code
        $paymentInfoWithoutCode = PaymentInfo::create([
            'organizer_id' => $this->paymentInfo->organizer_id,
            'submitted_by' => $this->paymentInfo->submitted_by,
            'business_name' => 'Test Business 2',
            'bank_code' => '040100',
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567891',
            'account_name' => 'Test Account 2',
            'payment_channel' => 'bank',
            'percentage_charge' => 10.0,
            'transaction_fee_payer' => 'subaccount',
            'paystack_subaccount_code' => null,
            'status' => 'pending',
        ]);
        $this->assertFalse($paymentInfoWithoutCode->shouldSyncWithPaystack());

        // Should not sync when no relevant fields changed
        $this->assertFalse($this->paymentInfo->shouldSyncWithPaystack());

        // Should sync when business name changes
        $this->paymentInfo->business_name = 'Updated Business Name';
        $this->assertTrue($this->paymentInfo->shouldSyncWithPaystack());

        // Reset and test bank code change
        $this->paymentInfo->refresh();
        $this->paymentInfo->bank_code = '030100';
        $this->assertTrue($this->paymentInfo->shouldSyncWithPaystack());

        // Reset and test account number change
        $this->paymentInfo->refresh();
        $this->paymentInfo->account_number = '9876543210';
        $this->assertTrue($this->paymentInfo->shouldSyncWithPaystack());

        // Reset and test percentage charge change
        $this->paymentInfo->refresh();
        $this->paymentInfo->percentage_charge = 15.0;
        $this->assertTrue($this->paymentInfo->shouldSyncWithPaystack());
    }

    #[Test]
    public function it_handles_sync_errors_gracefully()
    {
        // Mock failed Paystack API response
        Http::fake([
            'api.paystack.co/subaccount/*' => Http::response([
                'status' => false,
                'message' => 'Invalid subaccount code',
            ], 404),
        ]);

        // Trigger sync by updating a field
        $this->paymentInfo->update(['business_name' => 'Updated Name']);

        // Should have recorded the sync error
        $this->assertTrue($this->paymentInfo->hasRecentSyncError());
        $this->assertStringContainsString('Invalid subaccount code', $this->paymentInfo->getLastSyncError());
    }

    #[Test]
    public function it_can_clear_sync_errors()
    {
        // Add a sync error
        $this->paymentInfo->update([
            'metadata' => [
                'last_sync_error' => [
                    'timestamp' => now()->toISOString(),
                    'error' => 'Test error message',
                ],
            ],
        ]);

        $this->assertTrue($this->paymentInfo->hasRecentSyncError());

        // Clear the error
        $this->paymentInfo->clearSyncError();

        $this->assertFalse($this->paymentInfo->hasRecentSyncError());
        $this->assertNull($this->paymentInfo->getLastSyncError());
    }

    #[Test]
    public function it_can_perform_manual_sync()
    {
        // Mock successful Paystack API response
        Http::fake([
            'api.paystack.co/subaccount/*' => Http::response([
                'status' => true,
                'message' => 'Subaccount updated successfully',
                'data' => [
                    'subaccount_code' => 'ACCT_test123',
                    'business_name' => $this->paymentInfo->business_name,
                    'active' => true,
                    'settlement_schedule' => 'auto',
                    'metadata' => [],
                ],
            ], 200),
        ]);

        $result = $this->paymentInfo->manualSyncWithPaystack();

        $this->assertTrue($result['status']);
        $this->assertEquals('Subaccount updated successfully', $result['message']);
    }

    #[Test]
    public function it_throws_exception_for_manual_sync_without_subaccount_code()
    {
        $paymentInfoWithoutCode = PaymentInfo::create([
            'organizer_id' => $this->paymentInfo->organizer_id,
            'submitted_by' => $this->paymentInfo->submitted_by,
            'business_name' => 'Test Business 3',
            'bank_code' => '040100',
            'settlement_bank' => 'GCB Bank Limited',
            'account_number' => '1234567892',
            'account_name' => 'Test Account 3',
            'payment_channel' => 'bank',
            'percentage_charge' => 10.0,
            'transaction_fee_payer' => 'subaccount',
            'paystack_subaccount_code' => null,
            'status' => 'pending',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot sync: PaymentInfo does not have a Paystack subaccount code');

        $paymentInfoWithoutCode->manualSyncWithPaystack();
    }

    #[Test]
    public function it_updates_payment_info_with_paystack_response_data()
    {
        // Mock successful Paystack API response with updated data
        Http::fake([
            'api.paystack.co/subaccount/*' => Http::response([
                'status' => true,
                'message' => 'Subaccount updated successfully',
                'data' => [
                    'subaccount_code' => 'ACCT_test123',
                    'business_name' => 'Updated Business Name',
                    'active' => true,
                    'settlement_schedule' => 'weekly',
                    'metadata' => [
                        'updated_at' => now()->toISOString(),
                        'updated_via' => 'dashboard',
                    ],
                ],
            ], 200),
        ]);

        $this->paymentInfo->manualSyncWithPaystack();

        // Refresh the model to get updated data
        $this->paymentInfo->refresh();

        // Check that response data was stored
        $this->assertNotNull($this->paymentInfo->paystack_response);
        $this->assertEquals('weekly', $this->paymentInfo->settlement_schedule);
        $this->assertTrue($this->paymentInfo->is_active);
    }

    #[Test]
    public function paystack_service_provides_user_friendly_error_messages()
    {
        $testCases = [
            ['not found', 'The subaccount was not found on Paystack. Please contact support.'],
            ['unauthorized', 'Unable to authenticate with Paystack. Please check your API configuration.'],
            ['authentication failed', 'Unable to authenticate with Paystack. Please check your API configuration.'],
            ['validation error', 'The provided information is invalid. Please check your inputs and try again.'],
            ['invalid data', 'The provided information is invalid. Please check your inputs and try again.'],
            ['network timeout', 'Unable to connect to Paystack. Please try again later.'],
            ['timeout error', 'Unable to connect to Paystack. Please try again later.'],
            ['random unknown error', 'An error occurred while updating the payment information. Please try again or contact support.'],
        ];

        foreach ($testCases as [$errorMessage, $expectedUserMessage]) {
            $exception = new \Exception($errorMessage);
            $userMessage = $this->paystackService->getValidationMessage($exception);
            $this->assertEquals($expectedUserMessage, $userMessage);
        }
    }
}
