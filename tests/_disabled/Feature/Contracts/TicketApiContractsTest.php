<?php

// use App\Models\Order;
// use App\Models\Ticket;
// use App\Models\TicketResponse;
// use App\Models\User;

// /*
//  * Response contracts of the ticket endpoints: exact keys, values and messages.
//  * They make removed or renamed fields and changed messages fail a test, which
//  * is what mutation testing checks. Case identifiers (F3-6x) refer to
//  * docs/testing/TEST_PLAN.md, Appendix A.3.
//  */

// beforeEach(function () {
//     [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
//     $this->ticketType->update(['description' => 'Standard entry']);
//     $this->organizer->update(['name' => 'Accra Events']);
//     $this->event->update(['title' => 'Night Market', 'venue_name' => 'Labadi Beach', 'venue_address' => 'Labadi, Accra']);
//     $this->staff = User::factory()->create();
//     $this->organizer->users()->attach($this->staff);

//     $order = Order::factory()->confirmed()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
//     $this->order = $order;
//     $this->ticket = Ticket::factory()->create([
//         'order_id' => $order->id,
//         'event_id' => $this->event->id,
//         'organizer_id' => $this->organizer->id,
//         'ticket_type_id' => $this->ticketType->id,
//         'attendee_name' => 'Ama Mensah',
//         'attendee_email' => 'ama@example.com',
//         'price' => 100,
//         'status' => 'active',
//     ]);
//     $field = $this->event->formFields()->create(['organizer_id' => $this->organizer->id, 'label' => 'T-shirt size', 'field_type' => 'text']);
//     TicketResponse::create(['ticket_id' => $this->ticket->id, 'event_form_field_id' => $field->id, 'value' => 'Large']);
// });

// function asGuest(): void
// {
//     app('auth')->forgetGuards();
// }

// function assertTicketShape(array $ticket, $test, bool $withEmail = true): void
// {
//     $expected = ['id', 'ticket_number', 'attendee_name', 'price', 'status', 'used_at', 'event', 'ticket_type', 'order', 'custom_field_responses'];
//     if ($withEmail) {
//         $expected[] = 'attendee_email';
//     }
//     sort($expected);
//     $keys = array_keys($ticket);
//     sort($keys);

//     expect($keys)->toBe($expected)
//         ->and($ticket['id'])->toBe($test->ticket->id)
//         ->and($ticket['ticket_number'])->toBe($test->ticket->ticket_number)
//         ->and($ticket['attendee_name'])->toBe('Ama Mensah')
//         ->and((float) $ticket['price'])->toBe(100.0)
//         ->and($ticket['status'])->toBe('active')
//         ->and($ticket['used_at'])->toBeNull()
//         ->and(array_keys($ticket['event']))->toBe(['id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer'])
//         ->and($ticket['event']['id'])->toBe($test->event->id)
//         ->and($ticket['event']['title'])->toBe('Night Market')
//         ->and($ticket['event']['start_time'])->toBe('18:00:00')
//         ->and($ticket['event']['venue_name'])->toBe('Labadi Beach')
//         ->and($ticket['event']['venue_address'])->toBe('Labadi, Accra')
//         ->and($ticket['event']['organizer'])->toBe(['id' => $test->organizer->id, 'name' => 'Accra Events'])
//         ->and($ticket['ticket_type'])->toBe(['name' => $test->ticketType->name, 'description' => 'Standard entry'])
//         ->and($ticket['order'])->toBe(['order_number' => $test->order->order_number, 'status' => 'confirmed'])
//         ->and($ticket['custom_field_responses'])->toBe([['label' => 'T-shirt size', 'value' => 'Large']]);
//     if ($withEmail) {
//         expect($ticket['attendee_email'])->toBe('ama@example.com');
//     }
// }

// it('F3-60 lookup by number returns the full ticket for staff', function () {
//     $response = $this->actingAs($this->staff)
//         ->postJson('/api/tickets/verify-by-number', ['ticket_number' => $this->ticket->ticket_number])
//         ->assertOk()
//         ->assertJsonPath('success', true)
//         ->assertJsonPath('message', 'Ticket found successfully');

//     assertTicketShape($response->json('ticket'), $this);
// });

// it('F3-60 lookup messages for an unknown ticket and for another organiser', function () {
//     $this->actingAs($this->staff)
//         ->postJson('/api/tickets/verify-by-number', ['ticket_number' => 'TKT-NOPE'])
//         ->assertStatus(404)
//         ->assertExactJson(['success' => false, 'message' => 'Ticket not found']);

//     $outsider = User::factory()->create();
//     makeCheckout()[0]->users()->attach($outsider);
//     $this->actingAs($outsider)
//         ->postJson('/api/tickets/verify-by-number', ['ticket_number' => $this->ticket->ticket_number])
//         ->assertStatus(403)
//         ->assertExactJson(['success' => false, 'message' => 'You are not authorized to view this ticket']);
// });

// it('F3-61 scan returns the full ticket to staff and a reduced ticket to guests', function () {
//     $guest = $this->postJson(route('tickets.scan-qr'), ['ticket_number' => $this->ticket->ticket_number])->assertOk();
//     assertTicketShape($guest->json('ticket'), $this, false);

//     asGuest();
//     $staff = $this->actingAs($this->staff)->postJson(route('tickets.scan-qr'), ['ticket_number' => $this->ticket->ticket_number])
//         ->assertOk()->assertJsonPath('message', 'Ticket verification successful');
//     assertTicketShape($staff->json('ticket'), $this);
// });

// it('F3-61 scan messages for each refusal', function () {
//     $messages = [
//         'used' => 'This ticket has already been used.',
//         'pending' => 'This ticket has not been paid for.',
//         'cancelled' => 'This ticket has been cancelled.',
//     ];
//     foreach ($messages as $status => $message) {
//         $this->ticket->update(['status' => $status]);
//         $this->postJson(route('tickets.scan-qr'), ['ticket_number' => $this->ticket->ticket_number])
//             ->assertStatus(400)
//             ->assertJsonPath('success', false)
//             ->assertJsonPath('message', $message)
//             ->assertJsonPath('ticket.status', $status);
//     }

//     $this->postJson(route('tickets.scan-qr'), ['ticket_number' => 'TKT-NOPE'])
//         ->assertStatus(404)->assertExactJson(['success' => false, 'message' => 'Ticket not found']);
// });

// it('F3-61 scan gives staff of the organiser the email even on a refused ticket and hides it from others', function () {
//     $this->ticket->update(['status' => 'used']);
//     $outsider = User::factory()->create();
//     makeCheckout()[0]->users()->attach($outsider);

//     $other = $this->actingAs($outsider)->postJson(route('tickets.scan-qr'), ['ticket_number' => $this->ticket->ticket_number]);
//     expect($other->json('ticket'))->not->toHaveKey('attendee_email');

//     asGuest();
//     $staff = $this->actingAs($this->staff)->postJson(route('tickets.scan-qr'), ['ticket_number' => $this->ticket->ticket_number]);
//     expect($staff->json('ticket.attendee_email'))->toBe('ama@example.com');
// });

// it('F3-62 JSON QR data is checked field by field', function () {
//     $qr = [
//         'ticket_number' => $this->ticket->ticket_number,
//         'attendee_name' => 'Ama Mensah',
//         'price' => 100,
//         'event' => ['title' => 'Night Market', 'start_date' => $this->event->start_date],
//         'order' => ['order_number' => $this->order->order_number],
//     ];

//     $this->postJson(route('tickets.scan-qr'), ['qr_data' => json_encode($qr)])->assertOk();

//     foreach ([
//         ['event', ['title' => 'Other', 'start_date' => $this->event->start_date]],
//         ['event', ['title' => 'Night Market', 'start_date' => '1999-01-01']],
//         ['event', ['start_date' => $this->event->start_date]],
//         ['order', ['order_number' => 'ORD-OTHER']],
//         ['order', []],
//     ] as [$key, $value]) {
//         $bad = $qr;
//         $bad[$key] = $value;
//         $this->postJson(route('tickets.scan-qr'), ['qr_data' => json_encode($bad)])
//             ->assertStatus(400)
//             ->assertJsonPath('message', 'QR code data does not match database records');
//     }

//     $noPrice = $qr;
//     unset($noPrice['price']);
//     $this->postJson(route('tickets.scan-qr'), ['qr_data' => json_encode($noPrice)])->assertStatus(400);

//     $noName = $qr;
//     unset($noName['attendee_name']);
//     $this->postJson(route('tickets.scan-qr'), ['qr_data' => json_encode($noName)])->assertStatus(400);
// });

// it('F3-63 marking as used answers with the updated ticket and the exact messages', function () {
//     $this->actingAs($this->staff);

//     $response = $this->patchJson(route('tickets.mark-used', $this->ticket))
//         ->assertOk()
//         ->assertJsonPath('success', true)
//         ->assertJsonPath('message', 'Ticket marked as used successfully.')
//         ->assertJsonPath('ticket.status', 'used');
//     expect($response->json('ticket.used_at'))->not->toBeNull();

//     $this->patchJson(route('tickets.mark-used', $this->ticket))
//         ->assertStatus(400)
//         ->assertExactJson(['success' => false, 'message' => 'This ticket is not active and cannot be used.']);

//     asGuest();
//     $this->patchJson(route('tickets.mark-used', $this->ticket))
//         ->assertStatus(401)
//         ->assertExactJson(['success' => false, 'message' => 'Authentication required to mark tickets as used.']);

//     $outsider = User::factory()->create();
//     makeCheckout()[0]->users()->attach($outsider);
//     $this->actingAs($outsider)->patchJson(route('tickets.mark-used', $this->ticket))
//         ->assertStatus(403)
//         ->assertExactJson(['success' => false, 'message' => 'You are not authorized to mark this ticket as used.']);
// });

// it('F3-64 the event routes answer with the exact payloads and messages', function () {
//     $this->actingAs($this->staff, 'sanctum');
//     $base = "/api/organizers/{$this->organizer->id}/events/{$this->event->id}";

//     $verify = $this->postJson("{$base}/tickets/verify", ['ticket_number' => $this->ticket->ticket_number])
//         ->assertOk()->assertJsonPath('message', 'Ticket verified successfully');
//     assertTicketShape($verify->json('ticket'), $this);

//     $this->postJson("{$base}/tickets/verify", ['ticket_number' => 'TKT-NOPE'])
//         ->assertStatus(404)->assertExactJson(['success' => false, 'message' => 'Ticket not found', 'ticket' => null]);

//     [, $otherEvent] = makeCheckout();
//     $foreign = Ticket::factory()->create([
//         'order_id' => Order::factory()->confirmed()->create(['event_id' => $otherEvent->id, 'organizer_id' => $otherEvent->organizer_id])->id,
//         'event_id' => $otherEvent->id,
//         'organizer_id' => $otherEvent->organizer_id,
//         'ticket_type_id' => $otherEvent->ticketTypes()->first()->id,
//     ]);
//     $this->postJson("{$base}/tickets/verify", ['ticket_number' => $foreign->ticket_number])
//         ->assertStatus(403)->assertExactJson(['success' => false, 'message' => 'This ticket is not valid for this event']);

//     $this->ticket->update(['status' => 'pending']);
//     $this->postJson("{$base}/tickets/verify", ['ticket_number' => $this->ticket->ticket_number])
//         ->assertStatus(400)->assertJsonPath('message', 'Ticket is not active: pending');
//     $this->ticket->update(['status' => 'used']);
//     $this->postJson("{$base}/tickets/verify", ['ticket_number' => $this->ticket->ticket_number])
//         ->assertStatus(400)->assertJsonPath('message', 'Ticket has already been used')->assertJsonPath('ticket.status', 'used');
// });

// it('F3-65 the organiser mark-as-used route returns the ticket and exact messages', function () {
//     $this->actingAs($this->staff, 'sanctum');
//     $base = "/api/organizers/{$this->organizer->id}/events/{$this->event->id}/tickets/{$this->ticket->id}";

//     $ok = $this->putJson($base)->assertOk()->assertJsonPath('message', 'Ticket marked as used successfully.');
//     expect($ok->json('ticket.status'))->toBe('used');

//     $this->putJson($base)->assertStatus(400)
//         ->assertJsonPath('message', 'This ticket is not active and cannot be used.')
//         ->assertJsonPath('ticket.ticket_number', $this->ticket->ticket_number);

//     [, $otherEvent] = makeCheckout();
//     $this->putJson("/api/organizers/{$this->organizer->id}/events/{$otherEvent->id}/tickets/{$this->ticket->id}")
//         ->assertStatus(400)->assertJsonPath('message', 'This ticket does not belong to the specified event.');
// });

// it('F3-66 the event ticket list, entry history and statistics have exact shapes', function () {
//     $this->actingAs($this->staff, 'sanctum');
//     $base = "/api/organizers/{$this->organizer->id}/events/{$this->event->id}";

//     $list = $this->getJson("{$base}/tickets")->assertOk();
//     expect($list->json('meta'))->toBe(['total' => 1, 'event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
//     $row = $list->json('data.0');
//     expect(array_keys($row))->toBe(['id', 'ticket_number', 'ticket_type', 'price', 'event', 'event_id', 'venue', 'venue_name', 'venue_address', 'organizer', 'order_number', 'attendee_name', 'attendee_email', 'status', 'used_at', 'created_at'])
//         ->and($row['ticket_type'])->toBe($this->ticketType->name)
//         ->and($row['event'])->toBe('Night Market')
//         ->and($row['venue'])->toBe('Labadi Beach')
//         ->and($row['venue_address'])->toBe('Labadi, Accra')
//         ->and($row['organizer'])->toBe('Accra Events')
//         ->and($row['order_number'])->toBe($this->order->order_number)
//         ->and($row['attendee_email'])->toBe('ama@example.com')
//         ->and($row['used_at'])->toBeNull();

//     $this->ticket->update(['status' => 'used', 'used_at' => '2026-10-01 10:30:00']);
//     $entries = $this->getJson("{$base}/tickets/entries?limit=10&offset=0")->assertOk();
//     expect($entries->json('meta'))->toBe(['total' => 1, 'limit' => '10', 'offset' => '0'])
//         ->and($entries->json('data.0'))->toBe([
//             'id' => $this->ticket->id,
//             'ticket_number' => $this->ticket->ticket_number,
//             'attendee_name' => 'Ama Mensah',
//             'used_at' => $entries->json('data.0.used_at'),
//             'ticket_type' => $this->ticketType->name,
//             'order_number' => $this->order->order_number,
//         ]);
//     expect($this->getJson("{$base}/tickets/entries")->json('meta'))->toBe(['total' => 1, 'limit' => 50, 'offset' => 0]);
//     expect($this->getJson("{$base}/tickets/entries?limit=1&offset=1")->json('data'))->toBe([]);

//     $stats = $this->getJson("{$base}/statistics")->assertOk();
//     expect($stats->json('data'))->toBe(['total_tickets' => 1, 'used_tickets' => 1, 'active_tickets' => 0, 'cancelled_tickets' => 0, 'usage_rate' => 100]);

//     $scan = $this->getJson("{$base}/tickets/scan-statistics")->assertOk();
//     expect(array_keys($scan->json('data')))->toContain('scans_by_hour', 'scans_by_day');
//     expect($scan->json('data.scans_by_hour.0'))->toBe(['hour' => 10, 'count' => 1]);
//     expect($scan->json('data.scans_by_day.0.count'))->toBe(1);
//     expect($this->getJson("{$base}/tickets/scan-statistics?date_from=2026-10-02")->json('data.scans_by_hour'))->toBe([]);
//     expect($this->getJson("{$base}/tickets/scan-statistics?date_to=2026-09-30")->json('data.scans_by_hour'))->toBe([]);
//     expect($this->getJson("{$base}/tickets/scan-statistics?date_from=2026-10-01&date_to=2026-10-02")->json('data.scans_by_hour'))->toHaveCount(1);
// });

// it('F3-67 the organiser refusals carry the exact messages', function () {
//     $this->actingAs($this->staff, 'sanctum');
//     [$otherOrganizer, $otherEvent] = makeCheckout();

//     $this->getJson("/api/organizers/{$otherOrganizer->id}/events/{$otherEvent->id}/statistics")
//         ->assertStatus(403)->assertExactJson(['success' => false, 'message' => 'You are not authorized to view this data.']);
//     $this->getJson("/api/organizers/{$this->organizer->id}/events/{$otherEvent->id}/statistics")
//         ->assertStatus(400)->assertExactJson(['success' => false, 'message' => 'This event does not belong to the specified organizer.']);
//     $this->getJson("/api/organizers/{$otherOrganizer->id}/events/{$otherEvent->id}/tickets")
//         ->assertStatus(403)->assertJsonPath('message', 'You are not authorized to view this data.');
//     $this->getJson("/api/organizers/{$this->organizer->id}/events/{$otherEvent->id}/tickets")
//         ->assertStatus(400)->assertJsonPath('message', 'This event does not belong to the specified organizer.');
// });

// it('F3-68 the public pages expose the expected props', function () {
//     $this->ticket->update(['status' => 'used', 'used_at' => '2026-10-01 10:30:00']);

//     $this->actingAs($this->staff)->get(route('tickets.verify', $this->ticket->ticket_number))
//         ->assertInertia(fn ($page) => $page
//             ->component('TicketVerification')
//             ->where('ticketNumber', $this->ticket->ticket_number)
//             ->where('canMarkAsUsed', true)
//             ->where('ticket.id', $this->ticket->id)
//             ->where('ticket.attendee_name', 'Ama Mensah')
//             ->where('ticket.attendee_email', 'ama@example.com')
//             ->where('ticket.status', 'used')
//             ->where('ticket.event.title', 'Night Market')
//             ->where('ticket.event.venue_name', 'Labadi Beach')
//             ->where('ticket.event.venue_address', 'Labadi, Accra')
//             ->where('ticket.event.organizer.name', 'Accra Events')
//             ->where('ticket.ticket_type.name', $this->ticketType->name)
//             ->where('ticket.ticket_type.description', 'Standard entry')
//             ->where('ticket.order.order_number', $this->order->order_number)
//             ->where('ticket.order.status', 'confirmed')
//             ->has('ticket.used_at')
//             ->has('ticket.event.start_date')
//             ->has('ticket.event.start_time')
//             ->has('ticket.price'));

//     asGuest();
//     $this->get(route('tickets.verify', $this->ticket->ticket_number))
//         ->assertInertia(fn ($page) => $page->where('canMarkAsUsed', false)->missing('ticket.attendee_email'));
//     $this->get(route('tickets.verify', 'TKT-NOPE'))
//         ->assertInertia(fn ($page) => $page->where('ticket', null)->where('ticketNumber', 'TKT-NOPE')->where('canMarkAsUsed', false));

//     $this->get(route('tickets.details', $this->ticket->ticket_number))
//         ->assertInertia(fn ($page) => $page
//             ->component('TicketDetails')
//             ->where('ticketNumber', $this->ticket->ticket_number)
//             ->where('ticket.id', $this->ticket->id)
//             ->where('ticket.attendee_name', 'Ama Mensah')
//             ->where('ticket.attendee_email', 'ama@example.com')
//             ->where('ticket.status', 'used')
//             ->where('ticket.event.title', 'Night Market')
//             ->where('ticket.event.venue_name', 'Labadi Beach')
//             ->where('ticket.event.venue_address', 'Labadi, Accra')
//             ->where('ticket.event.organizer.name', 'Accra Events')
//             ->where('ticket.ticket_type.description', 'Standard entry')
//             ->where('ticket.order.status', 'confirmed')
//             ->where('ticket.custom_field_responses', [['label' => 'T-shirt size', 'value' => 'Large']])
//             ->has('ticket.used_at')
//             ->has('ticket.price'));
// });

// it('F3-69 the check-in page passes the selected event with its organiser', function () {
//     $this->actingAs($this->staff)->get(route('attendee-checkin', ['event' => $this->event->id]))
//         ->assertInertia(fn ($page) => $page
//             ->component('AttendeeCheckIn')
//             ->where('selectedEvent.id', $this->event->id)
//             ->where('selectedEvent.title', 'Night Market')
//             ->where('selectedEvent.venue_name', 'Labadi Beach')
//             ->where('selectedEvent.venue_address', 'Labadi, Accra')
//             ->where('selectedEvent.organizer.id', $this->organizer->id)
//             ->where('selectedEvent.organizer.name', 'Accra Events')
//             ->has('selectedEvent.start_date')
//             ->has('selectedEvent.start_time'));

//     $this->actingAs($this->staff)->get(route('attendee-checkin'))
//         ->assertInertia(fn ($page) => $page->where('selectedEvent', null));
// });
