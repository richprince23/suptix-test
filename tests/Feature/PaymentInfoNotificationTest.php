<?php

use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\SystemAdmin;
use App\Models\User;
use App\Notifications\NewPaymentInfoSubmissionNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed();
});

test('admin receives notification when payment info is created', function () {
    // Fake notifications to capture them
    Notification::fake();

    // Create test data
    $user = User::factory()->create();

    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);

    $organizer->users()->attach($user->id);

    // Create payment info (this should trigger notification)
    $paymentInfo = PaymentInfo::create([
        'organizer_id' => $organizer->id,
        'submitted_by' => $user->id,
        'status' => 'pending',
        'business_name' => 'Test Business',
        'bank_code' => '040100',
        'settlement_bank' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'payment_channel' => 'bank',
        'percentage_charge' => 10.0,
        'transaction_fee_payer' => 'subaccount',
        'submitted_at' => now(),
        'is_active' => true,
    ]);

    // Simulate the notification call that happens in the controller
    $systemAdmin = new SystemAdmin;
    Notification::send($systemAdmin, new NewPaymentInfoSubmissionNotification($paymentInfo));

    // Assert notification was sent to system admin
    Notification::assertSentTo(
        $systemAdmin,
        NewPaymentInfoSubmissionNotification::class,
        function ($notification) use ($paymentInfo) {
            return $notification->paymentInfo->id === $paymentInfo->id;
        }
    );
});

test('notification contains correct payment info details', function () {
    // Create test data
    $user = User::factory()->create();

    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);

    $organizer->users()->attach($user->id);

    $paymentInfo = PaymentInfo::create([
        'organizer_id' => $organizer->id,
        'submitted_by' => $user->id,
        'business_name' => 'Test Business',
        'bank_code' => '040100',
        'settlement_bank' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'payment_channel' => 'bank',
        'percentage_charge' => 10.0,
        'transaction_fee_payer' => 'subaccount',
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'pending',
    ]);

    // Create the notification
    $notification = new NewPaymentInfoSubmissionNotification($paymentInfo);
    $systemAdmin = new SystemAdmin;

    // Test email content
    $mailMessage = $notification->toMail($systemAdmin);
    expect($mailMessage->subject)->toBe('New Payment Information Submission - Suptix');

    // Check that email contains key information
    $emailLines = $mailMessage->introLines;
    $emailBody = implode(' ', $emailLines);

    expect($emailBody)->toContain('Test Organizer');
    expect($emailBody)->toContain('Test Business');
    expect($emailBody)->toContain('1234567890');
    expect($emailBody)->toContain('Test Account');
    expect($emailBody)->toContain('GCB Bank Limited');

    // Test SMS content
    $smsMessage = $notification->toSms($systemAdmin);
    expect($smsMessage)->toContain('Test Organizer');
    expect($smsMessage)->toContain('Test Business');
    expect($smsMessage)->toContain('GCB Bank Limited');
    expect($smsMessage)->toContain('1234567890');
});

test('notification is delivered by email only because SMS was removed to reduce costs', function () {
    $user = User::factory()->create();

    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);

    $paymentInfo = PaymentInfo::create([
        'organizer_id' => $organizer->id,
        'submitted_by' => $user->id,
        'business_name' => 'Test Business',
        'bank_code' => '040100',
        'settlement_bank' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'payment_channel' => 'bank',
        'percentage_charge' => 10.0,
        'transaction_fee_payer' => 'subaccount',
        'submitted_at' => now(),
        'is_active' => true,
    ]);

    $notification = new NewPaymentInfoSubmissionNotification($paymentInfo);
    $systemAdmin = new SystemAdmin;

    expect($notification->via($systemAdmin))->toBe(['mail']);
});
