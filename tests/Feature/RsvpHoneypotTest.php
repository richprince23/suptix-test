<?php

use App\Models\Event;
use App\Models\Organizer;
use App\Models\RSVP;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Create test organizer
    $organizer = Organizer::create([
        'name' => 'Test Organizer',
        'email' => 'organizer@test.com',
        'phone' => '1234567890',
        'address' => '123 Test St',
        'city' => 'Test City',
        'state' => 'Test State',
        'country' => 'Test Country'
    ]);

    // Create test user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'user@test.com',
        'password' => bcrypt('password')
    ]);
    $organizer->users()->attach($user);

    // // Create test venue
    // $venue = Venue::create([
    //     'organizer_id' => $organizer->id,
    //     'name' => 'Test Venue',
    //     'address' => '123 Venue St',
    //     'city' => 'Venue City',
    //     'state' => 'Venue State',
    //     'country' => 'Venue Country',
    //     'capacity' => 100
    // ]);

    // Create test event
    $this->event = Event::create([
        'organizer_id' => $organizer->id,
        'venue' => "Test Venue",
        'title' => 'Test Event',
        'description' => 'A test event',
        'start_date' => now()->addDays(7),
        'start_time' => '18:00:00',
        'end_date' => now()->addDays(7),
        'end_time' => '22:00:00',
        'is_rsvp' => true,
        'status' => 'published',
        'slug' => 'test-event',
        'code' => 'TEST001'
    ]);
});

test('rsvp form accepts valid submission without honeypot', function () {
    $validData = [
        'guest_name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'referral_source' => 'Facebook',
        'notes' => 'Looking forward to the event',
        'honeypot' => '', // Empty honeypot field
    ];

    $response = $this->post(route('events.rsvp.book', $this->event->slug), $validData);

    $response->assertRedirect();
    $response->assertSessionHas('message');
    $this->assertDatabaseHas('rsvps', [
        'email' => 'john@example.com',
        'event_id' => $this->event->id
    ]);
});

test('rsvp form blocks spam with filled honeypot', function () {
    $spamData = [
        'guest_name' => 'Spam Bot',
        'email' => 'spam@bot.com',
        'phone' => '1234567890',
        'referral_source' => 'Facebook',
        'notes' => 'This is spam',
        'honeypot' => 'I am a bot', // Filled honeypot indicates spam
    ];

    $response = $this->post(route('events.rsvp.book', $this->event->slug), $spamData);

    // Should redirect back with validation errors
    $response->assertRedirect();
    $response->assertSessionHasErrors(['honeypot']);

    // Should NOT create RSVP record
    $this->assertDatabaseMissing('rsvps', [
        'email' => 'spam@bot.com',
        'event_id' => $this->event->id
    ]);
});

test('honeypot field validation prevents empty field values greater than max length', function () {
    $invalidData = [
        'guest_name' => 'Valid User',
        'email' => 'valid@example.com',
        'phone' => '1234567890',
        'honeypot' => 'any value', // Should fail max:0 validation
    ];

    $response = $this->post(route('events.rsvp.book', $this->event->slug), $invalidData);

    // Should redirect with validation error
    $response->assertRedirect();
    $response->assertSessionHasErrors(['honeypot']);

    // Should not create RSVP
    $this->assertDatabaseMissing('rsvps', [
        'email' => 'valid@example.com',
        'event_id' => $this->event->id
    ]);
});
