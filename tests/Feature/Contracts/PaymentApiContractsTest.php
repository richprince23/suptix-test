<?php

use App\Models\Payment;
use App\Models\PaymentInfo;
use App\Services\PaystackService;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
 * Contracts of the payment service and controller: exact request payloads,
 * result arrays and messages. Case identifiers (F2-10x) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.2.
 */

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout([], [], 10.0);
    $this->organizer->update(['name' => 'Accra Events', 'email' => 'events@example.com', 'phone' => '+233201111111']);
    $this->service = app(PaystackService::class);
});

function sentTo(string $urlPart): ?Request
{
    return Http::recorded()->map(fn ($pair) => $pair[0])->first(fn (Request $r) => str_contains($r->url(), $urlPart));
}

// ---------------------------------------------------------------- subaccounts

it('F2-100 createSubaccount sends the organiser details and stores the answer', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create([
        'organizer_id' => $this->organizer->id,
        'paystack_subaccount_code' => null,
        'business_name' => 'Accra Events Ltd',
        'bank_code' => '040100',
        'account_number' => '0123456789',
        'percentage_charge' => 7.5,
    ]));
    Http::fake(['api.paystack.co/subaccount' => Http::response(['status' => true, 'data' => ['subaccount_code' => 'ACCT_abc', 'is_verified' => true]])]);

    $result = $this->service->createSubaccount($info);

    expect($result)->toBe(['success' => true, 'data' => ['subaccount_code' => 'ACCT_abc', 'is_verified' => true], 'message' => 'Subaccount created successfully']);
    $request = sentTo('/subaccount');
    expect($request->method())->toBe('POST')
        ->and($request->hasHeader('Authorization', 'Bearer sk_test_dummy'))->toBeTrue()
        ->and($request->data())->toMatchArray([
            'business_name' => 'Accra Events Ltd',
            'settlement_bank' => '040100',
            'account_number' => '0123456789',
            'percentage_charge' => 7.5,
            'description' => 'Subaccount for Accra Events',
            'primary_contact_email' => 'events@example.com',
            'primary_contact_name' => 'Accra Events',
            'primary_contact_phone' => '+233201111111',
        ])
        ->and($request->data())->toHaveKey('metadata');
    $info->refresh();
    expect($info->paystack_subaccount_code)->toBe('ACCT_abc')
        ->and($info->subaccount_created_at)->not->toBeNull()
        ->and($info->is_verified)->toBeTrue()
        ->and($info->paystack_response)->toEqual(['subaccount_code' => 'ACCT_abc', 'is_verified' => true]);
});

it('F2-100 createSubaccount reports failures with the gateway message or a default', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['organizer_id' => $this->organizer->id, 'paystack_subaccount_code' => null]));

    Http::fake(['api.paystack.co/subaccount' => Http::response(['status' => false, 'message' => 'Bad bank'], 400)]);
    expect($this->service->createSubaccount($info))->toBe(['success' => false, 'message' => 'Bad bank', 'data' => ['status' => false, 'message' => 'Bad bank']]);

    Http::swap(new Factory);
    Http::fake(['api.paystack.co/subaccount' => Http::response(['status' => false], 400)]);
    expect($this->service->createSubaccount($info)['message'])->toBe('Failed to create subaccount');

    Http::swap(new Factory);
    Http::fake(['api.paystack.co/subaccount' => fn () => throw new Exception('down')]);
    $result = $this->service->createSubaccount($info);
    expect($result['success'])->toBeFalse()->and($result['message'])->toBe('An error occurred while creating the subaccount')->and($result['error'])->toBe('down');
});

it('F2-101 getSubaccount, getSubaccountBalance and updateSubaccount keep their result shapes', function () {
    Http::fake([
        'api.paystack.co/subaccount/ACCT_x' => Http::sequence()
            ->push(['status' => true, 'data' => ['subaccount_code' => 'ACCT_x']])
            ->push(['status' => false], 404)
            ->push(['status' => false, 'message' => 'Nope'], 404)
            ->push(['status' => true, 'data' => ['percentage_charge' => 5]])
            ->push(['status' => false], 400),
        'api.paystack.co/balance*' => Http::sequence()
            ->push(['status' => true, 'data' => [['balance' => 123456, 'currency' => 'USD']]])
            ->push(['status' => true, 'data' => [['balance' => 5000]]])
            ->push(['status' => false], 400)
            ->push(['status' => false, 'message' => 'Denied'], 400),
    ]);

    expect($this->service->getSubaccount('ACCT_x'))->toBe(['success' => true, 'data' => ['subaccount_code' => 'ACCT_x']])
        ->and($this->service->getSubaccount('ACCT_x'))->toBe(['success' => false, 'message' => 'Failed to fetch subaccount'])
        ->and($this->service->getSubaccount('ACCT_x'))->toBe(['success' => false, 'message' => 'Nope']);

    expect($this->service->updateSubaccount('ACCT_x', ['percentage_charge' => 5]))->toBe(['success' => true, 'data' => ['percentage_charge' => 5], 'message' => 'Subaccount updated successfully'])
        ->and($this->service->updateSubaccount('ACCT_x', []))->toBe(['success' => false, 'message' => 'Failed to update subaccount']);

    expect($this->service->getSubaccountBalance('ACCT_x'))->toBe(['success' => true, 'balance' => 1234.56, 'currency' => 'USD', 'raw_data' => [['balance' => 123456, 'currency' => 'USD']]])
        ->and($this->service->getSubaccountBalance('ACCT_x')['currency'])->toBe('GHS')
        ->and($this->service->getSubaccountBalance('ACCT_x'))->toBe(['success' => false, 'message' => 'Failed to fetch balance'])
        ->and($this->service->getSubaccountBalance('ACCT_x'))->toBe(['success' => false, 'message' => 'Denied']);
});

it('F2-101 the subaccount calls turn exceptions into failed results with fixed messages', function () {
    Http::fake(['api.paystack.co/*' => fn () => throw new Exception('boom')]);

    expect($this->service->getSubaccount('A')['message'])->toBe('An error occurred while fetching subaccount details')
        ->and($this->service->updateSubaccount('A', [])['message'])->toBe('An error occurred while updating the subaccount')
        ->and($this->service->getSubaccountBalance('A')['message'])->toBe('An error occurred while fetching balance')
        ->and($this->service->getBanks()['message'])->toBe('An error occurred while fetching banks')
        ->and($this->service->verifyBankAccount('1', '2')['message'])->toBe('An error occurred while verifying account')
        ->and($this->service->verifyPayment('R')['message'])->toBe('An error occurred while verifying payment');
});

it('F2-102 syncSubaccountBalance stores the balance, and reports false without a code or on failure', function () {
    $info = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    Http::fake(['api.paystack.co/balance*' => Http::sequence()
        ->push(['status' => true, 'data' => [['balance' => 250050]]])
        ->push(['status' => false, 'message' => 'x'], 400)]);

    expect($this->service->syncSubaccountBalance($info))->toBeTrue();
    $info->refresh();
    expect((float) $info->available_balance)->toBe(2500.5)->and((float) $info->ledger_balance)->toBe(2500.5)->and($info->balance_last_updated)->not->toBeNull();
    expect(sentTo('/balance')->data())->toBe(['subaccount' => $info->paystack_subaccount_code]);

    expect($this->service->syncSubaccountBalance($info))->toBeFalse();

    $info->paystack_subaccount_code = null;
    expect($this->service->syncSubaccountBalance($info))->toBeFalse();
});

it('F2-103 getBanks and verifyBankAccount send their parameters and keep their shapes', function () {
    Http::fake([
        'api.paystack.co/bank/resolve*' => Http::sequence()
            ->push(['status' => true, 'data' => ['account_name' => 'KWAME', 'account_number' => '0123456789', 'bank_id' => 9]])
            ->push(['status' => false], 422)
            ->push(['status' => false, 'message' => 'Unknown account'], 422),
        'api.paystack.co/bank?country*' => Http::sequence()
            ->push(['status' => true, 'data' => [['code' => '1', 'name' => 'A Bank', 'slug' => 'a-bank'], ['code' => '2', 'name' => 'B Bank']]])
            ->push(['status' => false], 400)
            ->push(['status' => false, 'message' => 'Busy'], 400),
    ]);

    expect($this->service->getBanks())->toBe(['success' => true, 'banks' => [['code' => '1', 'name' => 'A Bank', 'slug' => 'a-bank'], ['code' => '2', 'name' => 'B Bank', 'slug' => null]]]);
    expect(sentTo('/bank')->data())->toBe(['country' => 'ghana']);
    expect($this->service->getBanks('nigeria'))->toBe(['success' => false, 'message' => 'Failed to fetch banks'])
        ->and($this->service->getBanks())->toBe(['success' => false, 'message' => 'Busy']);

    expect($this->service->verifyBankAccount('0123456789', '040100'))->toBe(['success' => true, 'account_name' => 'KWAME', 'account_number' => '0123456789', 'bank_id' => 9]);
    expect(sentTo('/bank/resolve')->data())->toBe(['account_number' => '0123456789', 'bank_code' => '040100']);
    expect($this->service->verifyBankAccount('1', '2'))->toBe(['success' => false, 'message' => 'Failed to verify account'])
        ->and($this->service->verifyBankAccount('1', '2'))->toBe(['success' => false, 'message' => 'Unknown account']);
});

// ---------------------------------------------------------------- payments

it('F2-104 initializePayment sends the full payload and records the payment', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType, 2);
    $order->update(['payment_reference' => null, 'buyer_email' => 'buyer@example.com']);
    Payment::where('order_id', $order->id)->delete();
    $info = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    Http::fake(['api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://pay.test/x', 'access_code' => 'ac1', 'reference' => 'REF_FROM_GATEWAY']])]);

    $result = $this->service->initializePayment($order);

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    expect($result)->toBe([
        'success' => true,
        'data' => ['authorization_url' => 'https://pay.test/x', 'access_code' => 'ac1', 'reference' => 'REF_FROM_GATEWAY', 'payment_id' => $payment->id],
        'message' => 'Payment initialized successfully',
    ]);

    $request = sentTo('/transaction/initialize');
    $data = $request->data();
    expect(array_keys($data))->toBe(['email', 'amount', 'currency', 'reference', 'callback_url', 'metadata', 'subaccount', 'transaction_charge', 'bearer'])
        ->and($data['email'])->toBe('buyer@example.com')
        ->and($data['amount'])->toEqual(20000)
        ->and($data['currency'])->toBe('GHS')
        ->and($data['reference'])->toStartWith('SUP_'.$order->order_number.'_')
        ->and($data['callback_url'])->toBe(route('payment.callback'))
        ->and($data['subaccount'])->toBe($info->paystack_subaccount_code)
        ->and($data['transaction_charge'])->toEqual(2000)
        ->and($data['bearer'])->toBe('account')
        ->and($data['metadata'])->toEqual([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'organizer_id' => $this->organizer->id,
            'event_id' => $this->event->id,
            'platform_fee' => 2000,
            'organizer_amount' => 18000,
        ]);

    expect((float) $payment->amount)->toBe(200.0)
        ->and($payment->payment_method)->toBe('paystack')
        ->and($payment->transaction_id)->toBe('REF_FROM_GATEWAY')
        ->and($payment->status)->toBe('pending')
        ->and($payment->notes)->toBe('Payment initialized via Paystack')
        ->and($payment->organizer_id)->toBe($this->organizer->id);
    $order->refresh();
    expect($order->payment_reference)->toBe('REF_FROM_GATEWAY')->and($order->payment_method)->toBe('paystack');
});

it('F2-104 initializePayment explains each way it can fail', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType);
    Payment::where('order_id', $order->id)->delete();

    Http::fake(['api.paystack.co/*' => Http::sequence()
        ->push(['status' => false, 'message' => 'Invalid key'], 401)
        ->push(['status' => false], 400)]);
    $failed = $this->service->initializePayment($order);
    expect($failed['success'])->toBeFalse()->and($failed['message'])->toBe('Invalid key')->and($failed['data'])->toBe(['status' => false, 'message' => 'Invalid key']);
    expect($this->service->initializePayment($order)['message'])->toBe('Failed to initialize payment');

    Http::swap(new Factory);
    Http::fake(['api.paystack.co/*' => fn () => throw new Exception('timeout')]);
    $exception = $this->service->initializePayment($order);
    expect($exception['message'])->toBe('An error occurred while initializing payment')->and($exception['error'])->toBe('timeout');

    PaymentInfo::withoutEvents(fn () => PaymentInfo::where('organizer_id', $this->organizer->id)->update(['paystack_subaccount_code' => null]));
    expect($this->service->initializePayment($order->fresh()))->toBe(['success' => false, 'message' => 'Organizer payment information not properly configured']);
});

it('F2-105 verifyPayment returns the gateway data or the gateway message', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::sequence()
        ->push(['status' => true, 'data' => ['status' => 'success', 'reference' => 'R1']])
        ->push(['status' => false], 404)
        ->push(['status' => false, 'message' => 'No such reference'], 404)]);

    expect($this->service->verifyPayment('R1'))->toBe(['success' => true, 'data' => ['status' => 'success', 'reference' => 'R1']]);
    expect(sentTo('/transaction/verify/R1')->method())->toBe('GET');
    expect($this->service->verifyPayment('R2'))->toBe(['success' => false, 'message' => 'Failed to verify payment'])
        ->and($this->service->verifyPayment('R3'))->toBe(['success' => false, 'message' => 'No such reference']);
});

it('F2-106 processSuccessfulPayment answers for success, repeat, mismatch and unknown reference', function () {
    [$order, $payment, $reference] = makePendingOrder($this->event, $this->ticketType, 2);
    $data = ['reference' => $reference, 'amount' => 20000, 'status' => 'success', 'metadata' => ['order_id' => $order->id]];

    $first = $this->service->processSuccessfulPayment($data);
    expect($first['success'])->toBeTrue()->and($first['message'])->toBe('Payment processed successfully')
        ->and($first['data']['order']->is($order))->toBeTrue()->and($first['data']['payment']->is($payment))->toBeTrue()
        ->and($payment->fresh()->notes)->toBe('Payment completed successfully via Paystack')
        ->and($payment->fresh()->processed_at)->not->toBeNull();

    $again = $this->service->processSuccessfulPayment($data);
    expect($again['success'])->toBeTrue()->and($again['message'])->toBe('Payment already processed');

    [, , $otherReference] = makePendingOrder($this->event, $this->ticketType);
    $wrong = $this->service->processSuccessfulPayment(['reference' => $otherReference, 'amount' => 1]);
    expect($wrong['success'])->toBeFalse()->and($wrong['message'])->toBe('An error occurred while processing the payment')
        ->and($wrong['error'])->toContain('does not match the order total of 10000 pesewas');

    $noAmount = $this->service->processSuccessfulPayment(['reference' => $otherReference]);
    expect($noAmount['error'])->toContain('Paid amount missing');

    $unknown = $this->service->processSuccessfulPayment(['reference' => 'NOPE', 'amount' => 1]);
    expect($unknown['error'])->toBe('Order not found for reference: NOPE');
});

it('F2-107 processSuccessfulPayment reports a missing payment record and a metadata mismatch', function () {
    [$order, $payment, $reference] = makePendingOrder($this->event, $this->ticketType);
    $payment->forceDelete();
    expect($this->service->processSuccessfulPayment(['reference' => $reference, 'amount' => 10000])['error'])
        ->toBe("Payment record not found for reference: {$reference}");

    [$order2, , $reference2] = makePendingOrder($this->event, $this->ticketType);
    $result = $this->service->processSuccessfulPayment(['reference' => $reference2, 'amount' => 10000, 'metadata' => ['order_id' => $order->id]]);
    expect($result['error'])->toBe("Gateway metadata names order {$order->id}, not order {$order2->id}");
});

// ---------------------------------------------------------------- payment controller

it('F2-108 the initialize endpoint answers with the exact payloads', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType);
    $order->update(['payment_reference' => null]);
    Payment::where('order_id', $order->id)->delete();
    Http::fake(['api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://pay.test/y', 'access_code' => 'ac2', 'reference' => 'REF_Y']])]);

    $ok = $this->postJson(route('payment.initialize'), ['order_id' => $order->id])->assertOk()
        ->assertJsonPath('success', true)->assertJsonPath('message', 'Payment initialized successfully');
    expect(array_keys($ok->json('data')))->toBe(['authorization_url', 'access_code', 'reference', 'payment_id']);

    $order->update(['status' => 'confirmed']);
    $this->postJson(route('payment.initialize'), ['order_id' => $order->id])->assertStatus(400)
        ->assertExactJson(['success' => false, 'message' => 'This order has already been paid for']);

    $order->update(['status' => 'pending']);
    PaymentInfo::withoutEvents(fn () => PaymentInfo::where('organizer_id', $this->organizer->id)->update(['paystack_subaccount_code' => null]));
    $this->postJson(route('payment.initialize'), ['order_id' => $order->id])->assertStatus(400)
        ->assertExactJson(['success' => false, 'message' => 'Payment is not available for this event. Please contact the organizer.']);

    $this->postJson(route('payment.initialize'), ['order_id' => 0])->assertStatus(422);
});

it('F2-108 the initialize endpoint passes on a gateway failure message', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType);
    Payment::where('order_id', $order->id)->delete();
    Http::fake(['api.paystack.co/*' => Http::response(['status' => false, 'message' => 'Invalid key'], 401)]);

    $this->postJson(route('payment.initialize'), ['order_id' => $order->id])->assertStatus(400)
        ->assertExactJson(['success' => false, 'message' => 'Invalid key']);
});

it('F2-109 the verify endpoint passes on the gateway message', function () {
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(['status' => false, 'message' => 'No such reference'], 404)]);

    $this->postJson(route('payment.verify'), ['reference' => 'X'])->assertStatus(400)
        ->assertExactJson(['success' => false, 'message' => 'No such reference']);

    Http::swap(new Factory);
    fakePaystackVerify('X', 10000);
    $this->postJson(route('payment.verify'), ['reference' => 'X'])->assertOk()
        ->assertJsonPath('success', true)->assertJsonPath('data.reference', 'X');
});

it('F2-110 the webhook answers with the exact messages', function () {
    [$order, $payment, $reference] = makePendingOrder($this->event, $this->ticketType);
    fakePaystackVerify($reference, 10000);

    postWebhook(['event' => 'charge.success', 'data' => ['reference' => $reference, 'amount' => 10000]])
        ->assertOk()->assertExactJson(['message' => 'Webhook processed']);
    postWebhook(['event' => 'charge.success', 'data' => ['amount' => 1]])->assertStatus(400)->assertExactJson(['message' => 'Missing reference']);
    postWebhook(['event' => 'charge.success', 'data' => ['reference' => 'GONE']])->assertNotFound()->assertExactJson(['message' => 'Payment not found']);
    postWebhook(['event' => 'x.y', 'data' => []])->assertOk()->assertExactJson(['message' => 'Event not handled']);
    postWebhook(['data' => []])->assertOk()->assertExactJson(['message' => 'Event not handled']);
    postWebhook(['event' => 'transfer.success', 'data' => []])->assertOk()->assertExactJson(['message' => 'Transfer success handled']);
    postWebhook(['event' => 'transfer.failed', 'data' => []])->assertOk()->assertExactJson(['message' => 'Transfer failure handled']);
    postWebhook(['event' => 'charge.success', 'data' => ['reference' => $reference]], 'bad')->assertStatus(400)->assertExactJson(['message' => 'Invalid signature']);
});

it('F2-111 a dispute marks the payment with the reason, or with a default note', function () {
    [, $payment, $reference] = makePendingOrder($this->event, $this->ticketType);

    postWebhook(['event' => 'charge.dispute.create', 'data' => ['reason' => 'Chargeback', 'transaction' => ['reference' => $reference]]])
        ->assertOk()->assertExactJson(['message' => 'Dispute handled']);
    expect($payment->fresh()->status)->toBe('disputed')->and($payment->fresh()->notes)->toBe('Payment disputed - Chargeback');

    $payment->update(['status' => 'pending']);
    postWebhook(['event' => 'charge.dispute.create', 'data' => ['transaction' => ['reference' => $reference]]])->assertOk();
    expect($payment->fresh()->notes)->toBe('Payment disputed - No reason provided');

    postWebhook(['event' => 'charge.dispute.create', 'data' => ['transaction' => ['reference' => 'UNKNOWN']]])->assertOk();
    postWebhook(['event' => 'charge.dispute.create', 'data' => []])->assertOk();
});

it('F2-112 the callback handles each outcome with the right redirect', function () {
    [$order, , $reference] = makePendingOrder($this->event, $this->ticketType);
    fakePaystackVerify($reference, 10000, 'failed');
    $this->get(route('payment.callback', ['reference' => $reference]))->assertRedirect(route('payment.failed'))->assertSessionHas('error', 'Payment was not successful');

    Http::swap(new Factory);
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(['status' => false, 'message' => 'x'], 404)]);
    $this->get(route('payment.callback', ['reference' => $reference]))->assertRedirect(route('payment.failed'))->assertSessionHas('error', 'Payment verification failed');
    $this->get(route('payment.callback'))->assertRedirect(route('payment.failed'))->assertSessionHas('error', 'Invalid payment reference');

    Http::swap(new Factory);
    fakePaystackVerify($reference, 5, 'success');
    $this->get(route('payment.callback', ['reference' => $reference]))->assertRedirect(route('payment.failed'))->assertSessionHas('error', 'Payment processing failed');

    Http::swap(new Factory);
    fakePaystackVerify($reference, 10000);
    $this->get(route('payment.callback', ['reference' => $reference]))->assertRedirect(route('payment.success', ['order' => $order->order_number]))->assertSessionHas('success', 'Payment completed successfully!');
});

it('F2-113 a confirmed order records the organiser balance and the payment page props', function () {
    [$order, , $reference] = makePendingOrder($this->event, $this->ticketType, 2);
    $order->update(['buyer_phone' => '+233209999999']);
    fakePaystackVerify($reference, 20000, 'success', ['created_at' => '2026-10-01T10:00:00.000Z']);
    $this->get(route('payment.callback', ['reference' => $reference]));

    $info = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    expect($info->last_transaction_reference)->toBe($reference)
        ->and((float) $info->last_transaction_amount)->toBe(200.0)
        ->and($info->successful_transactions)->toBe(1)
        ->and($info->pending_transactions)->toBe(0)
        ->and($info->total_transactions)->toBe(1);

    $this->get(route('payment.success', $order->order_number))->assertInertia(fn ($page) => $page
        ->where('order.id', $order->id)
        ->where('order.total_amount', '200.00')
        ->where('order.buyer_email', $order->buyer_email)
        ->where('order.buyer_phone', '+233209999999')
        ->where('order.event.id', $this->event->id)
        ->where('order.event.name', $this->event->title)
        ->where('order.event.code', $this->event->code)
        ->where('order.tickets.0.id', $this->ticketType->id)
        ->where('order.tickets.0.name', $this->ticketType->name)
        ->where('order.tickets.0.quantity', 2)
        ->where('order.tickets.0.unit_price', '100.00')
        ->has('order.purchased_at')
        ->has('order.event.start_date')
        ->has('order.event.start_time'));
});
