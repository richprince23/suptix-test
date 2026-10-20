<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;

/*
 * Model behaviour used by the three flows. Case identifiers (M-xx) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.1 and A.3.
 */

beforeEach(function () {
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
});

it('F1-34 available quantity adds back cancelled tickets', function () {
    $this->ticketType->update(['quantity' => 10]);
    [$order] = makePendingOrder($this->event, $this->ticketType, 3);
    $order->tickets()->limit(2)->update(['status' => 'cancelled']);

    expect($this->ticketType->fresh()->available_quantity)->toBe(12);
});

it('F1-35 sold count and total capacity count active and used tickets only', function () {
    $this->ticketType->update(['quantity' => 10]);
    [$order] = makePendingOrder($this->event, $this->ticketType, 4);
    $order->tickets()->get()->each(fn (Ticket $t, int $i) => $t->update(['status' => ['active', 'used', 'pending', 'cancelled'][$i]]));

    $type = $this->ticketType->fresh();
    expect($type->sold_tickets_count)->toBe(2)->and($type->total_capacity)->toBe(12);
});

it('F1-26 the sales window scopes include open and open-ended types and exclude ended ones', function () {
    $ended = TicketType::factory()->salesEnded()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
    $notStarted = TicketType::factory()->salesNotStarted()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);

    $available = TicketType::available()->where('event_id', $this->event->id)->pluck('id');
    $notExpired = TicketType::notExpired()->where('event_id', $this->event->id)->pluck('id');

    expect($available)->toContain($this->ticketType->id)->not->toContain($ended->id)->not->toContain($notStarted->id)
        ->and($notExpired)->toContain($this->ticketType->id)->toContain($notStarted->id)->not->toContain($ended->id);
});

it('F1-36 a ticket type belongs to its event and organiser', function () {
    expect($this->ticketType->event->is($this->event))->toBeTrue()
        ->and($this->ticketType->organizer->is($this->organizer))->toBeTrue();
});

it('F2-98 an order can be confirmed, counts its tickets and routes SMS to the buyer phone', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType, 2);
    $order->update(['buyer_phone' => '+233201234567']);

    expect($order->isConfirmed())->toBeFalse()->and($order->total_tickets)->toBe(2)
        ->and($order->routeNotificationForSms(null))->toBe('+233201234567');

    $order->confirm();

    expect($order->fresh()->isConfirmed())->toBeTrue()->and($order->fresh()->purchased_at)->not->toBeNull();
});

it('F2-98 an order knows its creator, organizer, event and discount relations', function () {
    $order = Order::factory()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);

    expect($order->organizer->is($this->organizer))->toBeTrue()
        ->and($order->event->is($this->event))->toBeTrue()
        ->and($order->createdByUser)->toBeNull()
        ->and($order->discountUsers)->toHaveCount(0);
});

it('F2-99 a payment belongs to its order and organiser', function () {
    [$order, $payment] = makePendingOrder($this->event, $this->ticketType);

    expect($payment->order->is($order))->toBeTrue()->and($payment->organizer->is($this->organizer))->toBeTrue()
        ->and(Payment::where('order_id', $order->id)->count())->toBe(1);
});

it('F3-49 a ticket is active only in the active status and relates to its check-ins', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType);
    $ticket = $order->tickets()->firstOrFail();

    expect($ticket->isActive())->toBeFalse()
        ->and($ticket->checkIns)->toHaveCount(0)
        ->and($ticket->event_name)->toBe($this->event->title);

    $ticket->update(['status' => 'active']);
    expect($ticket->fresh()->isActive())->toBeTrue();
});
