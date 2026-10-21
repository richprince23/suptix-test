<?php

// use App\Models\PaymentInfo;
// use App\Models\Ticket;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Notification;

// /*
//  * Flow F2: signed webhook. Case identifiers (F2-3x, F2-4x) refer to
//  * docs/testing/TEST_PLAN.md, Appendix A.2.3. The decision table is over
//  * signature validity, payment reference and current payment status.
//  */

// beforeEach(function () {
//     Mail::fake();
//     Notification::fake();
//     [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
//     [$this->order, $this->payment, $this->reference] = makePendingOrder($this->event, $this->ticketType);
//     fakePaystackVerify($this->reference, 10000);
// });

// function chargeSuccess(string $reference, int $amount = 10000): array
// {
//     return ['event' => 'charge.success', 'data' => ['reference' => $reference, 'amount' => $amount, 'status' => 'success']];
// }

// // ---------------------------------------------------------------- decision table

// it('F2-30 valid signature, known reference, pending payment: payment completed and order confirmed', function () {
//     postWebhook(chargeSuccess($this->reference))->assertOk();

//     expect($this->payment->fresh()->status)->toBe('completed')
//         ->and($this->order->fresh()->status)->toBe('confirmed')
//         ->and($this->order->tickets()->pluck('status')->unique()->all())->toBe(['active']);
// });

// it('F2-31 valid signature, known reference, completed payment: no second processing', function () {
//     postWebhook(chargeSuccess($this->reference))->assertOk();
//     $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
//     $balance = (float) $paymentInfo->fresh()->subaccount_balance;
//     $count = $paymentInfo->fresh()->total_transactions;

//     postWebhook(chargeSuccess($this->reference))->assertOk();

//     expect((float) $paymentInfo->fresh()->subaccount_balance)->toBe($balance)
//         ->and($paymentInfo->fresh()->total_transactions)->toBe($count);
// });

// it('F2-32 valid signature, unknown reference: 404', function () {
//     postWebhook(chargeSuccess('SUP_UNKNOWN_REFERENCE'))
//         ->assertNotFound()
//         ->assertJsonPath('message', 'Payment not found');
// });

// it('F2-33 valid signature, reference missing from the payload: 400', function () {
//     postWebhook(['event' => 'charge.success', 'data' => ['amount' => 10000]])
//         ->assertStatus(400)
//         ->assertJsonPath('message', 'Missing reference');
// });

// it('F2-34 invalid signature (wrong secret), known reference: 400 and nothing changes', function () {
//     $body = json_encode(chargeSuccess($this->reference));

//     postWebhook(chargeSuccess($this->reference), hash_hmac('sha512', $body, 'a-different-secret'))
//         ->assertStatus(400);

//     expect($this->payment->fresh()->status)->toBe('pending')->and($this->order->fresh()->status)->toBe('pending');
// });

// it('F2-35 invalid signature (payload altered after signing): 400', function () {
//     $signed = hash_hmac('sha512', json_encode(chargeSuccess($this->reference, 10000)), config('services.paystack.secret_key'));

//     postWebhook(chargeSuccess($this->reference, 1), $signed)->assertStatus(400);

//     expect($this->order->fresh()->status)->toBe('pending');
// });

// it('F2-36 signature header missing, known reference: 400 and not a server error', function () {
//     postWebhook(chargeSuccess($this->reference), null, false)->assertStatus(400);

//     expect($this->order->fresh()->status)->toBe('pending');
// });

// it('F2-37 empty signature, known reference: 400', function () {
//     postWebhook(chargeSuccess($this->reference), '')->assertStatus(400);

//     expect($this->order->fresh()->status)->toBe('pending');
// });

// it('F2-38 valid signature, unhandled event type: 200 and no state change', function () {
//     postWebhook(['event' => 'invoice.create', 'data' => ['reference' => $this->reference]])
//         ->assertOk()
//         ->assertJsonPath('message', 'Event not handled');

//     expect($this->order->fresh()->status)->toBe('pending');
// });

// // ---------------------------------------------------------------- further cases

// it('F2-40 two identical charge.success webhooks credit the balances once', function () {
//     postWebhook(chargeSuccess($this->reference))->assertOk();
//     postWebhook(chargeSuccess($this->reference))->assertOk();

//     $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
//     expect((float) $paymentInfo->total_transaction_volume)->toBe(100.0)
//         ->and($paymentInfo->total_transactions)->toBe(1)
//         ->and($this->order->purchases()->count())->toBe(1);
// });

// it('F2-41 charge.success for an amount lower than the order total does not confirm the order', function () {
//     fakePaystackVerify($this->reference, 5000);

//     postWebhook(chargeSuccess($this->reference, 5000));

//     expect($this->order->fresh()->status)->toBe('pending')
//         ->and($this->payment->fresh()->status)->toBe('pending');
// });

// it('F2-42 a dispute webhook marks the payment as disputed and keeps the tickets', function () {
//     postWebhook(['event' => 'charge.dispute.create', 'data' => [
//         'reason' => 'Chargeback',
//         'transaction' => ['reference' => $this->reference],
//     ]])->assertOk();

//     expect($this->payment->fresh()->status)->toBe('disputed')
//         ->and(Ticket::where('order_id', $this->order->id)->count())->toBe(1);
// });

// it('F2-44 the webhook route is exempt from CSRF but still needs a valid signature', function () {
//     $this->postJson(route('paystack.webhook'), chargeSuccess($this->reference))->assertStatus(400);

//     expect($this->order->fresh()->status)->toBe('pending');
// });
