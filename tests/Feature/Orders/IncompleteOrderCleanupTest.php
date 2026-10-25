<?php

use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

/*
 * Stock release and clean-up of abandoned orders (reported with F1). Case
 * identifiers (F4-xx) refer to docs/testing/TEST_PLAN.md, Appendix A.4.
 */

beforeEach(function () {
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
});

function ageOrder(Order $order, Carbon $createdAt): void
{
    Order::whereKey($order->id)->update(['created_at' => $createdAt]);
}

function cleanup(array $options = ['--timeout' => 15]): void
{
    Artisan::call('orders:cleanup-incomplete', $options);
}

it('F4-01 deletes an old pending order with its tickets and pending payment', function () {
    [$order, $payment] = makePendingOrder($this->event, $this->ticketType, 2);
    ageOrder($order, now()->subMinutes(30));

    cleanup();

    expect(Order::find($order->id))->toBeNull()
        ->and(Ticket::where('order_id', $order->id)->count())->toBe(0)
        ->and(Payment::find($payment->id))->toBeNull();
});

it('F4-02 restores the reserved stock for the deleted tickets', function () {
    $this->ticketType->update(['quantity' => 48]);
    [$order] = makePendingOrder($this->event, $this->ticketType, 2);
    ageOrder($order, now()->subMinutes(30));

    cleanup();

    expect($this->ticketType->fresh()->quantity)->toBe(50);
});

it('F4-03 keeps an order at 14 minutes 59 seconds and removes one at 15 minutes 1 second', function () {
    Carbon::setTestNow(now()->startOfSecond());
    [$young] = makePendingOrder($this->event, $this->ticketType);
    [$old] = makePendingOrder($this->event, $this->ticketType);
    ageOrder($young, now()->subMinutes(14)->subSeconds(59));
    ageOrder($old, now()->subMinutes(15)->subSeconds(1));

    cleanup();

    expect(Order::find($young->id))->not->toBeNull()->and(Order::find($old->id))->toBeNull();
    Carbon::setTestNow();
});

it('F4-04 never deletes a confirmed order', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType);
    $order->update(['status' => 'confirmed']);
    ageOrder($order, now()->subDay());

    cleanup();

    expect(Order::find($order->id))->not->toBeNull();
});

it('F4-05 gives the discount use back when its unpaid order is deleted', function () {
    $discount = Discount::factory()->create([
        'organizer_id' => $this->organizer->id,
        'event_id' => $this->event->id,
        'usage_limit' => 5,
        'used_count' => 1,
    ]);
    [$order] = makePendingOrder($this->event, $this->ticketType);
    $order->update(['discount_id' => $discount->id]);
    ageOrder($order, now()->subMinutes(30));

    cleanup();

    expect($discount->fresh()->used_count)->toBe(0);
});

it('F4-06 a dry run changes nothing', function () {
    $this->ticketType->update(['quantity' => 48]);
    [$order] = makePendingOrder($this->event, $this->ticketType, 2);
    ageOrder($order, now()->subMinutes(30));

    cleanup(['--timeout' => 15, '--dry-run' => true]);

    expect(Order::find($order->id))->not->toBeNull()
        ->and(Ticket::where('order_id', $order->id)->count())->toBe(2)
        ->and($this->ticketType->fresh()->quantity)->toBe(48);
});

it('F4-08 the clean-up command is scheduled every fifteen minutes', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())->toMatch('/\*\/15 \* \* \* \*\s+php artisan orders:cleanup-incomplete --timeout=15/');
});
