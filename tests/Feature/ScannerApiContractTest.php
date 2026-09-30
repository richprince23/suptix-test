<?php

use App\Models\Event;
use App\Models\Organizer;
use App\Models\RSVP;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

/*
 * Contract tests for the endpoints consumed by the suptix_scan app.
 * Venue data is flattened onto the event (venue_*); there is no Venue model.
 */

function scannerOrganizer(): Organizer
{
    return Organizer::create([
        'name' => 'Scanner Org '.Str::random(4),
        'email' => Str::random(6).'@test.com',
    ]);
}

function scannerEvent(Organizer $organizer, array $overrides = []): Event
{
    return Event::create(array_merge([
        'organizer_id' => $organizer->id,
        'code' => strtoupper(Str::random(6)),
        'title' => 'Scanner Event '.Str::random(4),
        'description' => 'Event used by the scanner contract tests.',
        'venue_name' => 'Accra Sports Stadium',
        'venue_address' => 'Ako Adjei, Accra',
        'venue_gps_code' => 'GA-123-4567',
        'venue_capacity' => 4000,
        'venue_phone' => '0300000000',
        'start_date' => now()->addDays(5)->toDateString(),
        'start_time' => '18:00:00',
        'status' => 'published',
        'is_rsvp' => true,
    ], $overrides));
}

function scannerUserFor(Organizer $organizer): User
{
    $user = User::factory()->create();
    $organizer->users()->attach($user);

    return $user;
}

test('ping endpoint is reachable without authentication', function () {
    $this->getJson('/api/ping')
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('organizer events endpoint returns flattened venue fields', function () {
    $organizer = scannerOrganizer();
    $event = scannerEvent($organizer);

    Sanctum::actingAs(scannerUserFor($organizer));

    $this->getJson("/api/organizers/{$organizer->id}/events")
        ->assertOk()
        ->assertJsonPath('data.0.venue_name', 'Accra Sports Stadium')
        ->assertJsonPath('data.0.venue_address', 'Ako Adjei, Accra')
        ->assertJsonPath('data.0.venue_gps_code', 'GA-123-4567')
        ->assertJsonPath('data.0.venue_capacity', 4000)
        ->assertJsonPath('data.0.venue_phone', '0300000000')
        ->assertJsonPath('data.0.organizer_id', $organizer->id)
        ->assertJsonMissingPath('data.0.venue');

    expect($event->fresh()->venue_name)->toBe('Accra Sports Stadium');
});

test('single event endpoint returns the event for its organizer', function () {
    $organizer = scannerOrganizer();
    $event = scannerEvent($organizer);

    Sanctum::actingAs(scannerUserFor($organizer));

    $this->getJson("/api/organizers/{$organizer->id}/events/{$event->code}")
        ->assertOk()
        ->assertJsonPath('data.id', $event->id)
        ->assertJsonPath('data.venue_name', 'Accra Sports Stadium');
});

test('events endpoint rejects a user outside the organizer', function () {
    $organizer = scannerOrganizer();
    scannerEvent($organizer);

    Sanctum::actingAs(scannerUserFor(scannerOrganizer()));

    $this->getJson("/api/organizers/{$organizer->id}/events")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

test('mobile guest verify-event route exists and returns flat venue', function () {
    $organizer = scannerOrganizer();
    $event = scannerEvent($organizer);

    RSVP::create([
        'event_id' => $event->id,
        'organizer_id' => $organizer->id,
        'guest_code' => strtoupper(Str::random(8)),
        'guest_name' => 'Ama Mensah',
        'email' => 'ama@test.com',
        'status' => 'attending',
    ]);

    Sanctum::actingAs(scannerUserFor($organizer));

    $this->postJson("/api/mobile/guest/verify-event/{$event->code}", [
        'guest_identifier' => 'ama@test.com',
    ])
        ->assertOk()
        ->assertJsonPath('data.event.venue_name', 'Accra Sports Stadium')
        ->assertJsonPath('data.rsvps.0.event_id', $event->id);
});

test('mobile guest statistics route exists', function () {
    $organizer = scannerOrganizer();
    $event = scannerEvent($organizer);

    Sanctum::actingAs(scannerUserFor($organizer));

    $this->getJson("/api/mobile/guest/event/{$event->code}/statistics")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['total_rsvps', 'checked_in_rsvps']]);
});

test('guest check-in returns the rsvp both at top level and under data', function () {
    $organizer = scannerOrganizer();
    $event = scannerEvent($organizer);

    $rsvp = RSVP::create([
        'event_id' => $event->id,
        'organizer_id' => $organizer->id,
        'guest_code' => strtoupper(Str::random(8)),
        'guest_name' => 'Kofi Owusu',
        'email' => 'kofi@test.com',
        'status' => 'attending',
    ]);

    Sanctum::actingAs(scannerUserFor($organizer));

    $this->postJson('/api/mobile/guest/check-in', [
        'guest_code' => $rsvp->guest_code,
    ])
        ->assertOk()
        ->assertJsonPath('rsvp.guest_code', $rsvp->guest_code)
        ->assertJsonPath('data.rsvp.guest_code', $rsvp->guest_code)
        ->assertJsonPath('data.rsvp.event.venue_name', 'Accra Sports Stadium')
        ->assertJsonPath('data.rsvp.event_id', $event->id);
});

test('guest list returns formatted rsvps with event context', function () {
    $organizer = scannerOrganizer();
    $event = scannerEvent($organizer);

    RSVP::create([
        'event_id' => $event->id,
        'organizer_id' => $organizer->id,
        'guest_code' => strtoupper(Str::random(8)),
        'guest_name' => 'Yaa Asantewaa',
        'email' => 'yaa@test.com',
        'status' => 'attending',
    ]);

    Sanctum::actingAs(scannerUserFor($organizer));

    $this->getJson("/api/organizers/{$organizer->id}/events/{$event->code}/guest-list")
        ->assertOk()
        ->assertJsonPath('data.0.event_id', $event->id)
        ->assertJsonPath('data.0.organizer_id', $organizer->id)
        ->assertJsonPath('data.0.event.venue_name', 'Accra Sports Stadium');
});
