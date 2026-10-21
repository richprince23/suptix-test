<?php

// use App\Models\Event;
// use App\Models\Order;
// use App\Models\Organizer;
// use App\Models\Ticket;
// use App\Models\User;

// /*
//  * Flow F3: organiser-scoped ticket endpoints (verify for an event, mark as
//  * used for an event, entry history and statistics, ticket details page).
//  * Case identifiers (F3-3x to F3-5x) refer to docs/testing/TEST_PLAN.md,
//  * Appendix A.3.
//  */

// beforeEach(function () {
//     [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
//     $this->staff = User::factory()->create();
//     $this->organizer->users()->attach($this->staff);

//     // A second organiser with an event and a ticket of its own
//     [$this->otherOrganizer, $this->otherEvent] = makeCheckout();
//     $this->otherTicket = organiserTicket($this->otherEvent);
// });

// function organiserTicket(Event $event, string $status = 'active', array $attributes = []): Ticket
// {
//     $order = Order::factory()->confirmed()->create(['event_id' => $event->id, 'organizer_id' => $event->organizer_id]);

//     return Ticket::factory()->create(array_merge([
//         'order_id' => $order->id,
//         'event_id' => $event->id,
//         'organizer_id' => $event->organizer_id,
//         'ticket_type_id' => $event->ticketTypes()->first()->id,
//         'status' => $status,
//         'attendee_email' => 'holder@example.com',
//     ], $attributes));
// }

// function eventUrl(Organizer $organizer, Event $event, string $path = ''): string
// {
//     return "/api/organizers/{$organizer->id}/events/{$event->id}{$path}";
// }

// // ---------------------------------------------------------------- verify for an event

// it('F3-34 verifies an active ticket of the event, by number or by QR data', function () {
//     $ticket = organiserTicket($this->event);
//     $this->actingAs($this->staff, 'sanctum');

//     $this->postJson(eventUrl($this->organizer, $this->event, '/tickets/verify'), ['ticket_number' => $ticket->ticket_number])
//         ->assertOk()->assertJsonPath('success', true);

//     $this->postJson(eventUrl($this->organizer, $this->event, '/tickets/verify'), ['qr_data' => json_encode(['ticket_number' => $ticket->ticket_number])])
//         ->assertOk()->assertJsonPath('success', true);
// });

// it('F3-34 rejects unknown tickets, bad QR data and tickets that are not active', function () {
//     $used = organiserTicket($this->event, 'used');
//     $this->actingAs($this->staff, 'sanctum');
//     $url = eventUrl($this->organizer, $this->event, '/tickets/verify');

//     $this->postJson($url, ['ticket_number' => 'TKT-NOSUCHTICKET'])->assertStatus(404);
//     $this->postJson($url, ['qr_data' => 'not json'])->assertStatus(400)->assertJsonPath('message', 'Invalid QR code format');
//     $this->postJson($url, ['qr_data' => json_encode(['other' => 1])])->assertStatus(400)->assertJsonPath('message', 'QR code missing ticket number');
//     $this->postJson($url, ['ticket_number' => $used->ticket_number])->assertStatus(400)->assertJsonPath('message', 'Ticket has already been used');
//     $this->postJson($url, [])->assertStatus(422);
// });

// it('F3-35 a user of another organiser cannot verify tickets for this event', function () {
//     $ticket = organiserTicket($this->event);
//     $outsider = User::factory()->create();
//     $this->otherOrganizer->users()->attach($outsider);

//     $this->actingAs($outsider, 'sanctum')
//         ->postJson(eventUrl($this->organizer, $this->event, '/tickets/verify'), ['ticket_number' => $ticket->ticket_number])
//         ->assertStatus(403);
// });

// it('F3-36 a ticket of another event is refused without returning its personal data', function () {
//     $this->actingAs($this->staff, 'sanctum');

//     $response = $this->postJson(eventUrl($this->organizer, $this->event, '/tickets/verify'), ['ticket_number' => $this->otherTicket->ticket_number]);

//     $response->assertStatus(403);
//     expect($response->getContent())->not->toContain('holder@example.com');
// });

// // ---------------------------------------------------------------- mark as used for an event

// it('F3-47 marks a ticket as used through the event route and refuses repeats and mismatches', function () {
//     $ticket = organiserTicket($this->event);
//     $this->actingAs($this->staff, 'sanctum');
//     $url = eventUrl($this->organizer, $this->event, "/tickets/{$ticket->id}");

//     $this->putJson($url)->assertOk()->assertJsonPath('success', true);
//     expect($ticket->fresh()->status)->toBe('used');

//     $this->putJson($url)->assertStatus(400);

//     $foreign = eventUrl($this->organizer, $this->event, "/tickets/{$this->otherTicket->id}");
//     $this->putJson($foreign)->assertStatus(400);
//     expect($this->otherTicket->fresh()->status)->toBe('active');
// });

// it('F3-47 refuses a user who does not belong to the organiser', function () {
//     $ticket = organiserTicket($this->event);
//     $outsider = User::factory()->create();
//     $this->otherOrganizer->users()->attach($outsider);

//     $this->actingAs($outsider, 'sanctum')
//         ->putJson(eventUrl($this->organizer, $this->event, "/tickets/{$ticket->id}"))
//         ->assertStatus(403);
//     expect($ticket->fresh()->status)->toBe('active');
// });

// // ---------------------------------------------------------------- statistics and history

// it('F3-43 event statistics count tickets by status', function () {
//     organiserTicket($this->event, 'active');
//     organiserTicket($this->event, 'active');
//     organiserTicket($this->event, 'used', ['used_at' => now()]);
//     organiserTicket($this->event, 'cancelled');
//     $this->actingAs($this->staff, 'sanctum');

//     $this->getJson(eventUrl($this->organizer, $this->event, '/statistics'))
//         ->assertOk()
//         ->assertJsonPath('data.total_tickets', 4)
//         ->assertJsonPath('data.active_tickets', 2)
//         ->assertJsonPath('data.used_tickets', 1)
//         ->assertJsonPath('data.cancelled_tickets', 1)
//         ->assertJsonPath('data.usage_rate', 25);
// });

// it('F3-45 entry history lists only used tickets of the event, newest first', function () {
//     $older = organiserTicket($this->event, 'used', ['used_at' => now()->subHours(2)]);
//     $newer = organiserTicket($this->event, 'used', ['used_at' => now()->subHour()]);
//     organiserTicket($this->event, 'active');
//     $this->actingAs($this->staff, 'sanctum');

//     $response = $this->getJson(eventUrl($this->organizer, $this->event, '/tickets/entries'))->assertOk();

//     expect(collect($response->json('data'))->pluck('ticket_number')->all())->toBe([$newer->ticket_number, $older->ticket_number]);
// });

// it('F3-46 scan statistics group the entries by hour and by day', function () {
//     organiserTicket($this->event, 'used', ['used_at' => '2026-10-01 10:15:00']);
//     organiserTicket($this->event, 'used', ['used_at' => '2026-10-01 10:45:00']);
//     organiserTicket($this->event, 'used', ['used_at' => '2026-10-01 11:05:00']);
//     $this->actingAs($this->staff, 'sanctum');

//     $response = $this->getJson(eventUrl($this->organizer, $this->event, '/tickets/scan-statistics'))->assertOk();

//     expect($response->json('data.scans_by_hour'))->toHaveCount(2)
//         ->and($response->json('data.scans_by_day.0.count'))->toBe(3);
// });

// it('F3-44 statistics, history and scan statistics are refused for users outside the organiser', function (string $path) {
//     $outsider = User::factory()->create();
//     $this->otherOrganizer->users()->attach($outsider);

//     $this->actingAs($outsider, 'sanctum')
//         ->getJson(eventUrl($this->organizer, $this->event, $path))
//         ->assertStatus(403);
// })->with([
//     'F3-44 statistics' => '/statistics',
//     'F3-44 entries' => '/tickets/entries',
//     'F3-44 scan statistics' => '/tickets/scan-statistics',
// ]);

// it('F3-44 an organiser user cannot read another organiser event through their own organiser id', function (string $path) {
//     $this->actingAs($this->staff, 'sanctum');

//     $status = $this->getJson(eventUrl($this->organizer, $this->otherEvent, $path))->status();

//     expect($status)->toBeGreaterThanOrEqual(400);
// })->with([
//     'F3-44 statistics of another event' => '/statistics',
//     'F3-44 entries of another event' => '/tickets/entries',
//     'F3-44 scan statistics of another event' => '/tickets/scan-statistics',
// ]);

// // ---------------------------------------------------------------- ticket details page

// it('F3-48 the ticket details page shows the ticket for its number', function () {
//     $ticket = organiserTicket($this->event);

//     $this->get(route('tickets.details', $ticket->ticket_number))
//         ->assertOk()
//         ->assertInertia(fn ($page) => $page->component('TicketDetails')->where('ticket.ticket_number', $ticket->ticket_number));
// });

// it('F3-48 the ticket details page tolerates an unknown ticket number', function () {
//     $this->get(route('tickets.details', 'TKT-UNKNOWN'))
//         ->assertOk()
//         ->assertInertia(fn ($page) => $page->where('ticket', null));
// });
