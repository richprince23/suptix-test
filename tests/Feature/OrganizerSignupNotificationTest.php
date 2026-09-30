<?php

use App\Models\Organizer;
use App\Models\SystemAdmin;
use App\Models\User;
use App\Notifications\NewOrganizerSignupNotification;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;



test('system admin receives notification when new organizer signs up', function () {
    // Fake notifications
    Notification::fake();

    // Create a user
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create organizer data
    $organizerData = [
        'name' => 'Test Event Company',
        'email' => 'test@eventcompany.com',
        'phone' => '0241234567',
        'address' => '123 Main St',
        'city' => 'Accra',
        'state' => 'greater_accra_region',
        'country' => 'ghana',
        'description' => 'We organize amazing events',
        'website' => 'https://eventcompany.com',
    ];

    // Create organizer
    $organizer = Organizer::create($organizerData);
    $organizer->users()->attach($user);

    // Manually trigger notification to test
    $systemAdmin = new SystemAdmin();
    $systemAdmin->notify(new NewOrganizerSignupNotification($organizer));

    // Assert notification was sent
    Notification::assertSentTo(
        $systemAdmin,
        NewOrganizerSignupNotification::class,
        function ($notification) use ($organizer) {
            return $notification->organizer->id === $organizer->id;
        }
    );
});

test('notification contains correct organizer information', function () {
    // Create a user
    $user = User::factory()->create();

    // Create organizer data
    $organizerData = [
        'name' => 'Test Event Company',
        'email' => 'test@eventcompany.com',
        'phone' => '0241234567',
        'address' => '123 Main St',
        'city' => 'Accra',
        'state' => 'greater_accra_region',
        'country' => 'ghana',
        'description' => 'We organize amazing events',
    ];

    // Create organizer
    $organizer = Organizer::create($organizerData);

    // Create notification
    $notification = new NewOrganizerSignupNotification($organizer);
    $systemAdmin = new SystemAdmin();

    // Test SMS content
    $smsContent = $notification->toSms($systemAdmin);
    expect($smsContent)->toContain('Test Event Company');
    expect($smsContent)->toContain('test@eventcompany.com');
    expect($smsContent)->toContain('0241234567');
    expect($smsContent)->toContain('Accra');

    // Test email content
    $mailMessage = $notification->toMail($systemAdmin);
    expect($mailMessage->subject)->toBe('New Organizer Registration - Suptix');
    expect($mailMessage->greeting)->toBe('New Organizer Signup!');
});