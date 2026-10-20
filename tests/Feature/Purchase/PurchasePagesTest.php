<?php

use App\Models\Discount;
use App\Models\Order;
use App\Models\PaymentInfo;
use App\Models\Ticket;
use App\Models\TicketType;

/*
 * Flow F1: pages and read endpoints around the purchase. Case identifiers
 * (F1-8x) refer to docs/testing/TEST_PLAN.md, Appendix A.1.
 */

beforeEach(function () {
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
});

function selections($ticketType, int $quantity = 2): array
{
    return ['ticket_selections' => [['ticket_type_id' => $ticketType->id, 'quantity' => $quantity]]];
}

it('F1-80 the short ticket URL redirects to the event page', function () {
    $this->get(route('events.tickets.show', $this->event))->assertRedirect(route('events.show', $this->event));
});

it('F1-81 the buyer form opens with the selected tickets and the applied discount amount', function () {
    $discount = Discount::factory()->percentage(10)->create([
        'organizer_id' => $this->organizer->id,
        'event_id' => $this->event->id,
    ]);

    $this->post(route('events.tickets.buyer-form', $this->event), selections($this->ticketType) + ['discount_id' => $discount->id])
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('TicketBuyerForm')
            ->where('ticketSelections.0.quantity', 2)
            ->where('appliedDiscount.amount', 20));
});

it('F1-81 the buyer form ignores an inactive discount', function () {
    $discount = Discount::factory()->create([
        'organizer_id' => $this->organizer->id,
        'event_id' => $this->event->id,
        'is_active' => false,
    ]);

    $this->post(route('events.tickets.buyer-form', $this->event), selections($this->ticketType) + ['discount_id' => $discount->id])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('appliedDiscount', null));
});

it('F1-82 the buyer form refuses a missing selection, a foreign ticket type and too little stock', function () {
    [, , $foreign] = makeCheckout();
    $this->ticketType->update(['quantity' => 1]);

    $this->post(route('events.tickets.buyer-form', $this->event), [])
        ->assertRedirect(route('events.show', $this->event))->assertSessionHasErrors('error');
    $this->post(route('events.tickets.buyer-form', $this->event), selections($foreign))
        ->assertRedirect(route('events.show', $this->event))->assertSessionHasErrors(['error' => 'Invalid ticket selection.']);
    $this->post(route('events.tickets.buyer-form', $this->event), selections($this->ticketType, 2))
        ->assertRedirect(route('events.show', $this->event))->assertSessionHasErrors('error');
});

it('F1-83 the buyer form is not offered for an event that cannot be bought', function () {
    $this->event->update(['status' => 'draft']);

    $this->post(route('events.tickets.buyer-form', $this->event), selections($this->ticketType))
        ->assertRedirect(route('events.show', $this->event))->assertSessionHasErrors('error');
});

it('F1-84 opening the buyer form by GET goes back to the event page', function () {
    $this->get(route('events.tickets.buyer-form', $this->event))->assertRedirect(route('events.show', $this->event));
});

it('F1-85 lists the ticket types that can be bought and hides sold-out ones', function () {
    $soldOut = TicketType::factory()->stock(0)->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id, 'name' => 'Sold out']);

    $response = $this->getJson(route('events.tickets.available', $this->event))->assertOk()->assertJsonPath('purchasable', true);

    $names = collect($response->json('ticket_types'))->pluck('name');
    expect($names)->toContain($this->ticketType->name)->not->toContain('Sold out');
});

it('F1-85 answers 422 for the ticket list of an event that cannot be bought', function () {
    $this->event->update(['status' => 'canceled']);

    $this->getJson(route('events.tickets.available', $this->event))
        ->assertStatus(422)
        ->assertJsonPath('purchasable', false)
        ->assertJsonPath('ticket_types', []);
});

it('F1-86 the order confirmation page shows the order and 404s for an unknown number', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType, 2);

    $this->get(route('orders.confirmation', $order->order_number))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('TicketConfirmation')->where('order.order_number', $order->order_number)->has('tickets', 2));

    $this->get(route('orders.confirmation', 'ORD-NOSUCHORDER'))->assertNotFound();
});

it('F1-87 the download route answers for a known order and 404s for an unknown one', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType);

    $this->getJson(route('orders.download', $order->order_number))->assertOk()->assertJsonPath('order.order_number', $order->order_number);
    $this->getJson(route('orders.download', 'ORD-NOSUCHORDER'))->assertNotFound();
});

it('F1-88 the purchase answers with the confirmation page when the organiser has no payment setup', function () {
    [$organizer, $event, $ticketType] = makeCheckout();
    PaymentInfo::withoutEvents(fn () => PaymentInfo::where('organizer_id', $organizer->id)->update(['paystack_subaccount_code' => null]));

    $response = $this->post(route('events.tickets.purchase', $event), purchasePayload([[$ticketType, 1]]));

    $order = Order::firstOrFail();
    $response->assertRedirect(route('orders.confirmation', $order->order_number))->assertSessionHasErrors('payment');
    expect($order->status)->toBe('pending')->and(Ticket::count())->toBe(1);
});
