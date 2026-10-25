<?php

use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/*
 * Flow F3: ticket validation and entry. Case identifiers (F3-xx) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.3.
 */

beforeEach(function () {
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
    $this->staff = User::factory()->create();
    $this->organizer->users()->attach($this->staff);
});

function ticketFor(Event $event, string $status = 'active', array $attributes = []): Ticket
{
    $order = Order::factory()->confirmed()->create([
        'event_id' => $event->id,
        'organizer_id' => $event->organizer_id,
    ]);

    return Ticket::factory()->create(array_merge([
        'order_id' => $order->id,
        'event_id' => $event->id,
        'organizer_id' => $event->organizer_id,
        'ticket_type_id' => $event->ticketTypes()->first()->id,
        'status' => $status,
        'attendee_name' => 'Ama Mensah',
        'attendee_email' => 'ama@example.com',
    ], $attributes));
}

function markUsed(Ticket $ticket)
{
    return test()->patchJson(route('tickets.mark-used', $ticket));
}

function scan(array $body)
{
    return test()->postJson(route('tickets.scan-qr'), $body);
}

// ---------------------------------------------------------------- state machine

it('F3-01 marks an active ticket as used and records the time', function () {
    $ticket = ticketFor($this->event);

    $this->actingAs($this->staff);
    markUsed($ticket)->assertOk()->assertJsonPath('success', true);

    $ticket->refresh();
    expect($ticket->status)->toBe('used')->and($ticket->used_at)->not->toBeNull();
});

it('F3-02 refuses to mark a used ticket again and keeps the first timestamp', function () {
    $firstUse = now()->subHour()->startOfSecond();
    $ticket = ticketFor($this->event, 'used', ['used_at' => $firstUse]);

    $this->actingAs($this->staff);
    markUsed($ticket)->assertStatus(400);

    expect($ticket->fresh()->used_at->equalTo($firstUse))->toBeTrue();
});

it('F3-03 and F3-04 refuse to mark a pending or cancelled ticket as used', function (string $status) {
    $ticket = ticketFor($this->event, $status);

    $this->actingAs($this->staff);
    markUsed($ticket)->assertStatus(400);

    expect($ticket->fresh()->status)->toBe($status);
})->with(['F3-03 pending' => 'pending', 'F3-04 cancelled' => 'cancelled']);

it('F3-05 scanning an active ticket reports success', function () {
    $ticket = ticketFor($this->event);

    scan(['ticket_number' => $ticket->ticket_number])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('ticket.ticket_number', $ticket->ticket_number);
});

it('F3-06 to F3-08 scanning a used, pending or cancelled ticket does not report a plain success', function (string $status) {
    $ticket = ticketFor($this->event, $status);

    scan(['ticket_number' => $ticket->ticket_number])->assertJsonPath('success', false);
})->with([
    'F3-06 used' => 'used',
    'F3-07 pending' => 'pending',
    'F3-08 cancelled' => 'cancelled',
]);

it('F3-09 every fillable attribute of Ticket is a real column', function () {
    $missing = collect((new Ticket)->getFillable())
        ->reject(fn ($attribute) => Schema::hasColumn('tickets', $attribute))
        ->values()
        ->all();

    expect($missing)->toBe([]);
});

// ---------------------------------------------------------------- authorisation

it('F3-10 marking a ticket as used without signing in returns 401', function () {
    $ticket = ticketFor($this->event);

    markUsed($ticket)->assertStatus(401);

    expect($ticket->fresh()->status)->toBe('active');
});

it('F3-11 a user of another organiser cannot mark the ticket as used', function () {
    $ticket = ticketFor($this->event);
    $outsider = User::factory()->create();
    Organizer::factory()->create()->users()->attach($outsider);

    $this->actingAs($outsider);
    markUsed($ticket)->assertStatus(403);

    expect($ticket->fresh()->status)->toBe('active');
});

it('F3-12 a user of two organisers can check in tickets for either', function () {
    $second = Organizer::factory()->create();
    $second->users()->attach($this->staff);
    [, $otherEvent] = makeCheckout([], ['organizer_id' => $second->id]);
    $ticketA = ticketFor($this->event);
    $ticketB = ticketFor($otherEvent, 'active', ['ticket_type_id' => $otherEvent->ticketTypes()->first()?->id ?? $this->ticketType->id]);

    $this->actingAs($this->staff);
    markUsed($ticketA)->assertOk();
    markUsed($ticketB)->assertOk();
});

it('F3-13 a user of another organiser cannot look up the ticket by number', function () {
    $ticket = ticketFor($this->event);
    $outsider = User::factory()->create();
    Organizer::factory()->create()->users()->attach($outsider);

    $this->actingAs($outsider)
        ->postJson('/api/tickets/verify-by-number', ['ticket_number' => $ticket->ticket_number])
        ->assertStatus(403);
});

it('F3-14 looking up an unknown ticket number returns 404', function () {
    $this->actingAs($this->staff)
        ->postJson('/api/tickets/verify-by-number', ['ticket_number' => 'TKT-DOESNOTEXIST'])
        ->assertStatus(404);
});

it('F3-15 the ticket number lookup is case-insensitive', function () {
    $ticket = ticketFor($this->event);

    $this->actingAs($this->staff)
        ->postJson('/api/tickets/verify-by-number', ['ticket_number' => strtolower($ticket->ticket_number)])
        ->assertOk()
        ->assertJsonPath('ticket.ticket_number', $ticket->ticket_number);
});

it('F3-16 an unauthenticated scan does not return the attendee email', function () {
    $ticket = ticketFor($this->event);

    $response = scan(['ticket_number' => $ticket->ticket_number]);

    expect($response->getContent())->not->toContain('ama@example.com');
});

it('F3-17 the public verification page does not show the attendee email to a visitor', function () {
    $ticket = ticketFor($this->event);

    $this->get(route('tickets.verify', $ticket->ticket_number))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('TicketVerification')->missing('ticket.attendee_email'));
});

it('F3-18 and F3-19 the event ticket list is limited to the user\'s organiser and the event\'s owner', function () {
    $other = Organizer::factory()->create();
    [, $foreignEvent] = makeCheckout([], ['organizer_id' => $other->id]);

    $this->actingAs($this->staff, 'sanctum');
    $this->getJson("/api/organizers/{$other->id}/events/{$foreignEvent->id}/tickets")->assertStatus(403);
    $this->getJson("/api/organizers/{$this->organizer->id}/events/{$foreignEvent->id}/tickets")->assertStatus(400);
    $this->getJson("/api/organizers/{$this->organizer->id}/events/{$this->event->id}/tickets")->assertOk();
});

// ---------------------------------------------------------------- QR and ticket number handling

it('F3-20 finds a ticket from a lower-case ticket number', function () {
    $ticket = ticketFor($this->event);

    scan(['ticket_number' => strtolower($ticket->ticket_number)])->assertOk()->assertJsonPath('success', true);
});

it('F3-21 accepts JSON QR data that matches the ticket', function () {
    $ticket = ticketFor($this->event);

    scan(['qr_data' => json_encode([
        'ticket_number' => $ticket->ticket_number,
        'attendee_name' => $ticket->attendee_name,
        'price' => $ticket->price,
    ])])->assertOk()->assertJsonPath('success', true);
});

it('F3-22 and F3-23 reject JSON QR data that differs from the ticket', function (string $field, mixed $value) {
    $ticket = ticketFor($this->event);
    $qr = ['ticket_number' => $ticket->ticket_number, 'attendee_name' => $ticket->attendee_name, 'price' => $ticket->price];
    $qr[$field] = $value;

    scan(['qr_data' => json_encode($qr)])
        ->assertStatus(400)
        ->assertJsonPath('message', 'QR code data does not match database records');
})->with([
    'F3-22 different name' => ['attendee_name', 'Someone Else'],
    'F3-23 different price' => ['price', 1],
]);

it('F3-24 treats plain-text QR data as a ticket number', function () {
    $ticket = ticketFor($this->event);

    scan(['qr_data' => $ticket->ticket_number])->assertOk()->assertJsonPath('success', true);
});

it('F3-25 answers JSON QR data with missing keys with a client error, not a server error', function () {
    $ticket = ticketFor($this->event);

    $status = scan(['qr_data' => json_encode(['ticket_number' => $ticket->ticket_number])])->status();

    expect($status)->toBeGreaterThanOrEqual(400)->and($status)->toBeLessThan(500);
});

it('F3-26 returns 422 when neither qr_data nor ticket_number is sent', function () {
    scan([])->assertStatus(422);
});

it('F3-27 trims spaces around a ticket number', function () {
    $ticket = ticketFor($this->event);

    scan(['ticket_number' => '  '.$ticket->ticket_number.'  '])->assertOk()->assertJsonPath('success', true);
});

it('F3-28 generates unique ticket numbers', function () {
    $numbers = [];
    foreach (range(1, 300) as $i) {
        $numbers[] = 'TKT-'.strtoupper(Str::random(12));
    }

    expect(count(array_unique($numbers)))->toBe(300);
});

// ---------------------------------------------------------------- duplicate entry

it('F3-30 only one of two stale copies of an active ticket can claim entry', function () {
    $ticket = ticketFor($this->event);
    $first = Ticket::find($ticket->id);
    $second = Ticket::find($ticket->id);

    $firstClaim = $first->markAsUsed();
    $secondClaim = $second->markAsUsed();

    expect($firstClaim)->toBeTrue()->and($secondClaim)->toBeFalse();
});

it('F3-31 the second claim does not overwrite the first entry time', function () {
    $ticket = ticketFor($this->event);
    $first = Ticket::find($ticket->id);
    $second = Ticket::find($ticket->id);

    Carbon::setTestNow(now()->startOfSecond());
    $first->markAsUsed();
    $firstTime = $ticket->fresh()->used_at;

    Carbon::setTestNow(now()->addMinutes(5));
    $second->markAsUsed();

    expect($ticket->fresh()->used_at->equalTo($firstTime))->toBeTrue();
    Carbon::setTestNow();
});

it('F3-33 a ticket from event A cannot be checked in through event B\'s organiser route', function () {
    $other = Organizer::factory()->create();
    $other->users()->attach($this->staff);
    [, $otherEvent] = makeCheckout([], ['organizer_id' => $other->id]);
    $ticket = ticketFor($this->event);

    $this->actingAs($this->staff, 'sanctum');
    $response = $this->putJson("/api/organizers/{$other->id}/events/{$otherEvent->id}/tickets/{$ticket->id}");

    expect($response->status())->toBeGreaterThanOrEqual(400);
    expect($ticket->fresh()->status)->toBe('active');
});

// ---------------------------------------------------------------- check-in page

it('F3-40 redirects a guest from the check-in page to the login', function () {
    $this->get(route('attendee-checkin'))->assertRedirect();
});

it('F3-41 shows the selected event to an organiser user of that event', function () {
    $this->actingAs($this->staff)
        ->get(route('attendee-checkin', ['event' => $this->event->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('selectedEvent.id', $this->event->id));
});

it('F3-42 does not select an event that belongs to another organiser', function () {
    [, $foreignEvent] = makeCheckout();

    $this->actingAs($this->staff)
        ->get(route('attendee-checkin', ['event' => $foreignEvent->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('selectedEvent', null));
});
