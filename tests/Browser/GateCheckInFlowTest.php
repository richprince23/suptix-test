<?php

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
 * End-to-end test for flow F3: gate staff admit a ticket in a real browser and
 * the same ticket is then refused. Case E-05 in docs/testing/TEST_PLAN.md.
 */

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    config(['inertia.ssr.enabled' => false]);

    [$this->organizer, $this->event] = makeCheckout();
    $this->staff = User::factory()->create();
    $this->organizer->users()->attach($this->staff);

    $order = Order::factory()->confirmed()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
    $this->ticket = Ticket::factory()->create([
        'order_id' => $order->id,
        'event_id' => $this->event->id,
        'organizer_id' => $this->organizer->id,
        'ticket_type_id' => $this->event->ticketTypes()->first()->id,
        'attendee_name' => 'Ama Mensah',
        'status' => 'active',
    ]);
});

function searchTicket($page, string $ticketNumber)
{
    return $page->typeSlowly('#ticketNumber', $ticketNumber, typingDelay())
        ->wait(pause())
        ->click('button:has-text("Search Ticket")')
        ->wait(pause());
}

it('E-05 admits an active ticket once and refuses it on the second scan', function () {
    $this->actingAs($this->staff);
    $page = visit(route('attendee-checkin', ['event' => $this->event->id]));

    searchTicket($page, $this->ticket->ticket_number)
        ->assertSee('Valid Ticket')
        ->assertSee('Ama Mensah')
        ->click('button:has-text("Check In Attendee")')
        ->wait(pause())
        ->assertSee('Ticket Already Used');

    expect($this->ticket->fresh()->status)->toBe('used')->and($this->ticket->fresh()->used_at)->not->toBeNull();

    $second = visit(route('attendee-checkin', ['event' => $this->event->id]));
    searchTicket($second, $this->ticket->ticket_number)
        ->assertSee('Ticket Already Used')
        ->assertMissing('button:has-text("Check In Attendee")');
});

it('E-05 tells the gate when a ticket number does not exist', function () {
    $this->actingAs($this->staff);

    $page = visit(route('attendee-checkin', ['event' => $this->event->id]));

    searchTicket($page, 'TKT-DOESNOTEXIST')->assertSee('Ticket not found');
    expect(Event::count())->toBeGreaterThan(0);
});
