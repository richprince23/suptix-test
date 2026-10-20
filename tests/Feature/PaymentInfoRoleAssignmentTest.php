<?php

use App\Jobs\AssignOrganizerAdminRole;
use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\User;

use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;



beforeEach(function () {
    $this->seed();
    
    // Create the Organizer Admin role
    Role::firstOrCreate(['name' => 'Organizer Admin']);
});

test('job is dispatched when payment info is approved', function () {
    Queue::fake();

    // Create test data
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);
    $organizer->users()->attach($user->id);

    $paymentInfo = PaymentInfo::withoutEvents(fn () => PaymentInfo::create([
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
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'pending',
    ]));

    // Update status to approved (this should trigger the job via observer)
    $paymentInfo->update(['status' => 'approved']);

    // Assert job was dispatched
    Queue::assertPushed(AssignOrganizerAdminRole::class, function ($job) use ($paymentInfo) {
        return $job->paymentInfo->id === $paymentInfo->id;
    });
});

test('job does not run when payment info is not approved', function () {
    // Create test data
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);
    $organizer->users()->attach($user->id);

    $paymentInfo = PaymentInfo::withoutEvents(fn () => PaymentInfo::create([
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
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'pending',
    ]));

    // Create and run the job with non-approved status
    $job = new AssignOrganizerAdminRole($paymentInfo);
    $job->handle();

    // User should not have the role
    expect($user->fresh()->hasRole('Organizer Admin'))->toBeFalse();
});

test('job assigns organizer admin role when payment info is approved', function () {
    // Create test data
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);
    $organizer->users()->attach($user->id);

    $paymentInfo = PaymentInfo::withoutEvents(fn () => PaymentInfo::create([
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
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'approved',
    ]));

    // Create and run the job
    $job = new AssignOrganizerAdminRole($paymentInfo);
    $job->handle();

    // User should now have the organizer admin role
    expect($user->fresh()->hasRole('Organizer Admin'))->toBeTrue();
});

test('job does not assign duplicate roles', function () {
    // Create test data
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);
    $organizer->users()->attach($user->id);

    // Assign role manually first
    $user->assignRole('Organizer Admin');

    $paymentInfo = PaymentInfo::withoutEvents(fn () => PaymentInfo::create([
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
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'approved',
    ]));

    // Count roles before job
    $rolesBefore = $user->roles->count();

    // Create and run the job
    $job = new AssignOrganizerAdminRole($paymentInfo);
    $job->handle();

    // Should still have the same number of roles (no duplicates)
    expect($user->fresh()->roles->count())->toBe($rolesBefore);
    expect($user->fresh()->hasRole('Organizer Admin'))->toBeTrue();
});


test('observer triggers job when status changes to approved', function () {
    Queue::fake();

    // Create test data
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);
    $organizer->users()->attach($user->id);

    $paymentInfo = PaymentInfo::withoutEvents(fn () => PaymentInfo::create([
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
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'pending',
    ]));

    // Update status to approved
    $paymentInfo->update(['status' => 'approved']);

    // Should have dispatched the job
    Queue::assertPushed(AssignOrganizerAdminRole::class);
});

test('observer does not trigger job when status changes to non-approved', function () {
    Queue::fake();

    // Create test data
    $user = User::factory()->create();
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'test@example.com',
        'phone' => '+233123456789',
        'description' => 'Test organizer description',
    ]);
    $organizer->users()->attach($user->id);

    $paymentInfo = PaymentInfo::withoutEvents(fn () => PaymentInfo::create([
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
        'submitted_at' => now(),
        'is_active' => true,
        'status' => 'pending',
    ]));

    // Update status to rejected (not approved)
    $paymentInfo->update(['status' => 'rejected']);

    // Should not have dispatched the job
    Queue::assertNotPushed(AssignOrganizerAdminRole::class);
});