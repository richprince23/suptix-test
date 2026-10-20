<?php

use App\Models\Payment;
use App\Models\PaymentInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/*
 * Flow F2: payment initialisation. Case identifiers (F2-0x) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.2.1.
 */

function initialize(int $orderId)
{
    return test()->postJson(route('payment.initialize'), ['order_id' => $orderId]);
}

it('F2-01 initialisation creates a pending payment and stores the reference on the order', function () {
    [, $event, $ticketType] = makeCheckout();
    [$order] = makePendingOrder($event, $ticketType);
    $order->update(['payment_reference' => null]);
    Payment::where('order_id', $order->id)->delete();
    fakePaystackInitialize();

    initialize($order->id)->assertOk()->assertJsonPath('success', true);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    expect($payment->status)->toBe('pending')
        ->and($order->fresh()->payment_reference)->toBe($payment->transaction_id);
});

it('F2-02 and F2-03 send whole pesewas for the amount and the platform fee', function (float $percentage, float $total, int $amount, int $fee) {
    [, $event, $ticketType] = makeCheckout(['price' => $total], [], $percentage);
    [$order] = makePendingOrder($event, $ticketType);
    $order->update(['payment_reference' => null]);
    Payment::where('order_id', $order->id)->delete();
    fakePaystackInitialize();

    initialize($order->id)->assertOk();

    Http::assertSent(function ($request) use ($amount, $fee) {
        return $request->url() === 'https://api.paystack.co/transaction/initialize'
            && $request['amount'] == $amount
            && $request['transaction_charge'] == $fee
            && fmod((float) $request['amount'], 1.0) === 0.0
            && fmod((float) $request['transaction_charge'], 1.0) === 0.0;
    });
})->with([
    'F2-02 5% of GHS 100' => [5.0, 100.0, 10000, 500],
    'F2-03 3.5% of GHS 33.33' => [3.5, 33.33, 3333, 117],
]);

it('F2-04 refuses to initialise an order that is already confirmed', function () {
    [, $event, $ticketType] = makeCheckout();
    [$order] = makePendingOrder($event, $ticketType);
    $order->update(['status' => 'confirmed']);

    initialize($order->id)->assertStatus(400)->assertJsonPath('message', 'This order has already been paid for');
});

it('F2-05 refuses an organiser without a Paystack subaccount', function () {
    [$organizer, $event, $ticketType] = makeCheckout();
    PaymentInfo::withoutEvents(fn () => PaymentInfo::where('organizer_id', $organizer->id)
        ->update(['paystack_subaccount_code' => null]));
    [$order] = makePendingOrder($event, $ticketType);

    initialize($order->id)->assertStatus(400);
    expect(Payment::where('order_id', $order->id)->count())->toBe(1);
});

it('F2-06 leaves no new payment behind when Paystack rejects the request', function () {
    [, $event, $ticketType] = makeCheckout();
    [$order] = makePendingOrder($event, $ticketType);
    Payment::where('order_id', $order->id)->delete();
    Http::fake(['api.paystack.co/*' => Http::response(['status' => false, 'message' => 'Invalid key'], 401)]);

    initialize($order->id)->assertStatus(400)->assertJsonPath('success', false);
    expect(Payment::where('order_id', $order->id)->count())->toBe(0);
});

it('F2-07 handles a gateway failure without changing the order', function () {
    [, $event, $ticketType] = makeCheckout();
    [$order] = makePendingOrder($event, $ticketType);
    Payment::where('order_id', $order->id)->delete();
    Http::fake(['api.paystack.co/*' => fn () => throw new Exception('Connection timed out')]);

    initialize($order->id)->assertStatus(400)->assertJsonPath('success', false);
    expect($order->fresh()->status)->toBe('pending')->and(Payment::where('order_id', $order->id)->count())->toBe(0);
});

it('F2-08 gives two initialisations in the same second different references', function () {
    [, $event, $ticketType] = makeCheckout();
    [$order] = makePendingOrder($event, $ticketType);
    $order->update(['payment_reference' => null]);
    Payment::where('order_id', $order->id)->delete();
    fakePaystackInitialize();
    Carbon::setTestNow(now()->startOfSecond());

    initialize($order->id)->assertOk();
    initialize($order->id)->assertOk();

    $references = Http::recorded()
        ->filter(fn ($pair) => str_ends_with($pair[0]->url(), '/transaction/initialize'))
        ->map(fn ($pair) => $pair[0]['reference']);
    expect($references)->toHaveCount(2)->and($references->unique())->toHaveCount(2);

    Carbon::setTestNow();
});

it('F2-09 takes the fee bearer from the organiser payment info', function () {
    [$organizer, $event, $ticketType] = makeCheckout();
    PaymentInfo::withoutEvents(fn () => PaymentInfo::where('organizer_id', $organizer->id)
        ->update(['transaction_fee_payer' => 'subaccount']));
    [$order] = makePendingOrder($event, $ticketType);
    Payment::where('order_id', $order->id)->delete();
    fakePaystackInitialize();

    initialize($order->id)->assertOk();

    Http::assertSent(fn ($request) => ($request['bearer'] ?? null) === 'subaccount');
});
