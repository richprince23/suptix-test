<?php

use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// ── Helpers ───────────────────────────────────────────────────────────────────

function venueOrganizer(): Organizer
{
    return Organizer::create([
        'name' => 'Test Org '.Str::random(4),
        'email' => Str::random(6).'@test.com',
    ]);
}

function venueEvent(int $organizerId, array $overrides = []): Event
{
    return Event::create(array_merge([
        'organizer_id' => $organizerId,
        'title' => 'Test Event '.Str::random(4),
        'description' => 'A test event description.',
        'venue_name' => 'The Grand Hall',
        'venue_address' => '123 Main Street',
        'start_date' => now()->addDays(10)->toDateString(),
        'start_time' => '18:00:00',
        'status' => 'published',
    ], $overrides));
}

function venueOrder(int $eventId, int $organizerId): Order
{
    return Order::create([
        'order_number' => strtoupper(Str::random(8)),
        'event_id' => $eventId,
        'organizer_id' => $organizerId,
        'buyer_email' => 'buyer@test.com',
        'buyer_phone' => '0200000000',
        'subtotal_amount' => 50.00,
        'total_amount' => 50.00,
        'status' => 'confirmed',
        'payment_method' => 'paystack',
        'purchased_at' => now(),
    ]);
}

// ── Schema tests ──────────────────────────────────────────────────────────────

test('events table has venue_name and venue_address columns', function () {
    expect(Schema::hasColumn('events', 'venue_name'))->toBeTrue();
    expect(Schema::hasColumn('events', 'venue_address'))->toBeTrue();
});

test('events table does not have venue_id column', function () {
    expect(Schema::hasColumn('events', 'venue_id'))->toBeFalse();
});

// ── Model tests ───────────────────────────────────────────────────────────────

test('event stores and retrieves inline venue fields', function () {
    $organizer = venueOrganizer();
    $event = venueEvent($organizer->id, [
        'venue_name' => 'Accra International Conference Centre',
        'venue_address' => 'Liberation Road, Accra',
    ]);

    $fresh = Event::find($event->id);

    expect($fresh->venue_name)->toBe('Accra International Conference Centre');
    expect($fresh->venue_address)->toBe('Liberation Road, Accra');
});

test('event venue fields can be null for online events', function () {
    $organizer = venueOrganizer();
    $event = venueEvent($organizer->id, [
        'venue_name' => null,
        'venue_address' => null,
        'mode' => 'online',
        'platform_name' => 'Zoom',
    ]);

    $fresh = Event::find($event->id);

    expect($fresh->venue_name)->toBeNull();
    expect($fresh->venue_address)->toBeNull();
});

// ── OrderController::search endpoint tests ────────────────────────────────────

test('order search returns venue from inline event fields', function () {
    $organizer = venueOrganizer();
    $event = venueEvent($organizer->id, [
        'venue_name' => 'Kempinski Hotel',
        'venue_address' => 'Independence Ave, Accra',
    ]);
    $order = venueOrder($event->id, $organizer->id);

    $response = $this->postJson('/api/orders/search', [
        'order_number' => $order->order_number,
        'verification_email' => $order->buyer_email,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('order.event.venue_name', 'Kempinski Hotel')
        ->assertJsonPath('order.event.venue_address', 'Independence Ave, Accra');
});

test('order search returns null venue when event has no venue name', function () {
    $organizer = venueOrganizer();
    $event = venueEvent($organizer->id, [
        'venue_name' => null,
        'venue_address' => null,
    ]);
    $order = venueOrder($event->id, $organizer->id);

    $response = $this->postJson('/api/orders/search', [
        'order_number' => $order->order_number,
        'verification_email' => $order->buyer_email,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('order.event.venue_name', null)
        ->assertJsonPath('order.event.venue_address', null);
});

test('order search does not throw unknown column venue_id error', function () {
    $organizer = venueOrganizer();
    $event = venueEvent($organizer->id);
    $order = venueOrder($event->id, $organizer->id);

    $response = $this->postJson('/api/orders/search', [
        'order_number' => $order->order_number,
        'verification_email' => $order->buyer_email,
    ]);

    $response->assertStatus(200);
});
