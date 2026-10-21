<?php

// use App\Mail\OrderEmail;
// use App\Models\Order;
// use App\Models\PaymentInfo;
// use App\Notifications\OrderConfirmationSms;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Notification;

// /*
//  * Flow F2: payment callback (browser redirect from Paystack). Case
//  * identifiers (F2-1x) refer to docs/testing/TEST_PLAN.md, Appendix A.2.2.
//  */

// beforeEach(function () {
//     Mail::fake();
//     Notification::fake();
//     [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
// });

// function callback(?string $reference)
// {
//     return test()->get(route('payment.callback', $reference === null ? [] : ['reference' => $reference]));
// }

// it('F2-10 sends a callback without a reference to the failed page', function () {
//     callback(null)->assertRedirect(route('payment.failed'));
// });

// it('F2-11 sends the buyer to the failed page when verification fails and leaves the order alone', function () {
//     [$order, , $reference] = makePendingOrder($this->event, $this->ticketType);
//     Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(['status' => false, 'message' => 'Not found'], 404)]);

//     callback($reference)->assertRedirect(route('payment.failed'));

//     expect($order->fresh()->status)->toBe('pending');
// });

// it('F2-12 keeps the order pending when Paystack reports a failed or abandoned payment', function (string $status) {
//     [$order, , $reference] = makePendingOrder($this->event, $this->ticketType);
//     fakePaystackVerify($reference, 10000, $status);

//     callback($reference)->assertRedirect(route('payment.failed'));

//     expect($order->fresh()->status)->toBe('pending')
//         ->and($order->tickets()->pluck('status')->unique()->all())->toBe(['pending']);
// })->with(['F2-12 failed' => 'failed', 'F2-12 abandoned' => 'abandoned']);

// it('F2-13 confirms the order, completes the payment and activates the tickets on success', function () {
//     [$order, $payment, $reference] = makePendingOrder($this->event, $this->ticketType, 2);
//     fakePaystackVerify($reference, 20000);

//     callback($reference)->assertRedirect(route('payment.success', ['order' => $order->order_number]));

//     $order->refresh();
//     expect($order->status)->toBe('confirmed')
//         ->and($order->purchased_at)->not->toBeNull()
//         ->and($payment->fresh()->status)->toBe('completed')
//         ->and($order->tickets()->pluck('status')->unique()->all())->toBe(['active']);
// });

// it('F2-14 returns 404 on the success page for an order that is not confirmed', function () {
//     [$order] = makePendingOrder($this->event, $this->ticketType);

//     $this->get(route('payment.success', ['order' => $order->order_number]))->assertNotFound();
// });

// it('F2-15 records one purchase per ticket, even when the tickets have the same price', function () {
//     [$order, , $reference] = makePendingOrder($this->event, $this->ticketType, 3);
//     fakePaystackVerify($reference, 30000);

//     callback($reference);

//     expect($order->purchases()->count())->toBe(3);
// });

// it('F2-16 does not confirm the order when the paid amount is lower than the order total', function () {
//     [$order, $payment, $reference] = makePendingOrder($this->event, $this->ticketType, 1);
//     fakePaystackVerify($reference, 5000);

//     callback($reference)->assertRedirect(route('payment.failed'));

//     expect($order->fresh()->status)->toBe('pending')
//         ->and($payment->fresh()->status)->toBe('pending')
//         ->and($order->tickets()->pluck('status')->unique()->all())->toBe(['pending']);
// });

// it('F2-17 does not confirm an order when the gateway metadata names a different order', function () {
//     [$order, , $reference] = makePendingOrder($this->event, $this->ticketType);
//     [$other] = makePendingOrder($this->event, $this->ticketType);
//     fakePaystackVerify($reference, 10000, 'success', ['metadata' => ['order_id' => $other->id]]);

//     callback($reference);

//     expect($order->fresh()->status)->toBe('pending');
// });

// it('F2-18 a replayed callback does not add purchases, balances or emails', function () {
//     [$order, , $reference] = makePendingOrder($this->event, $this->ticketType, 2);
//     fakePaystackVerify($reference, 20000);

//     callback($reference);
//     $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
//     $purchases = $order->purchases()->count();
//     $balance = (float) $paymentInfo->fresh()->subaccount_balance;
//     $volume = (float) $paymentInfo->fresh()->total_transaction_volume;

//     callback($reference);

//     expect($order->purchases()->count())->toBe($purchases)
//         ->and((float) $paymentInfo->fresh()->subaccount_balance)->toBe($balance)
//         ->and((float) $paymentInfo->fresh()->total_transaction_volume)->toBe($volume);
//     Mail::assertSent(OrderEmail::class, 1);
// });

// it('F2-19 sends one confirmation email and no SMS when there is no phone number', function () {
//     [, , $reference] = makePendingOrder($this->event, $this->ticketType);
//     fakePaystackVerify($reference, 10000);

//     callback($reference);

//     Mail::assertSent(OrderEmail::class, 1);
//     Notification::assertNothingSent();
// });

// it('F2-19 queues the confirmation SMS when the buyer gave a phone number', function () {
//     [$order, , $reference] = makePendingOrder($this->event, $this->ticketType);
//     $order->update(['buyer_phone' => '+233201234567']);
//     fakePaystackVerify($reference, 10000);

//     callback($reference);

//     Notification::assertSentTo($order->fresh(), OrderConfirmationSms::class);
// });

// it('F2-13 total volume and balances are recorded for the organiser', function () {
//     [, , $reference] = makePendingOrder($this->event, $this->ticketType, 2);
//     fakePaystackVerify($reference, 20000);

//     callback($reference);

//     $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
//     expect((float) $paymentInfo->total_transaction_volume)->toBe(200.0)
//         ->and((float) $paymentInfo->subaccount_balance)->toBe(180.0)
//         ->and((float) $paymentInfo->system_admin_balance)->toBe(20.0)
//         ->and(Order::where('status', 'confirmed')->count())->toBe(1);
// });
