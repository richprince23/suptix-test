<?php

use App\Models\Payment;
use App\Models\PaymentInfo;
use App\Services\PaystackService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
 * Flow F2: payment pages, the verify endpoint, transfer webhooks and the
 * Paystack subaccount calls. Case identifiers (F2-9x) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.2.
 */

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
});

it('F2-90 the success page shows a confirmed order grouped by ticket type', function () {
    [$order, , $reference] = makePendingOrder($this->event, $this->ticketType, 3);
    fakePaystackVerify($reference, 30000);
    $this->get(route('payment.callback', ['reference' => $reference]));

    $this->get(route('payment.success', $order->order_number))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Payment/Success')
            ->where('order.order_number', $order->order_number)
            ->where('order.tickets.0.quantity', 3)
            ->where('order.tickets.0.total_price', 300));
});

it('F2-91 the failed page renders', function () {
    $this->get(route('payment.failed'))->assertOk()->assertInertia(fn ($page) => $page->component('Payment/Failed'));
});

it('F2-92 the verify endpoint returns the gateway data or a 400', function () {
    fakePaystackVerify('REF_OK', 10000);
    $this->postJson(route('payment.verify'), ['reference' => 'REF_OK'])->assertOk()->assertJsonPath('data.status', 'success');

    Http::swap(new Factory);
    Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(['status' => false, 'message' => 'Transaction reference not found'], 404)]);
    $this->postJson(route('payment.verify'), ['reference' => 'REF_BAD'])->assertStatus(400);

    $this->postJson(route('payment.verify'), [])->assertStatus(422);
});

it('F2-93 transfer webhooks are acknowledged without changing payments', function (string $event) {
    [, $payment] = makePendingOrder($this->event, $this->ticketType);

    postWebhook(['event' => $event, 'data' => ['reference' => 'TRF_1']])->assertOk();

    expect($payment->fresh()->status)->toBe('pending');
})->with(['F2-93 transfer.success' => 'transfer.success', 'F2-93 transfer.failed' => 'transfer.failed']);

it('F2-94 creates a subaccount and stores the code returned by Paystack', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['paystack_subaccount_code' => null]));
    Http::fake(['api.paystack.co/subaccount' => Http::response(['status' => true, 'data' => ['subaccount_code' => 'ACCT_new123', 'is_verified' => true]])]);

    $result = app(PaystackService::class)->createSubaccount($info);

    expect($result['success'])->toBeTrue()
        ->and($info->fresh()->paystack_subaccount_code)->toBe('ACCT_new123')
        ->and($info->fresh()->is_verified)->toBeTrue();
});

it('F2-94 reports a failed subaccount creation and keeps the record unchanged', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['paystack_subaccount_code' => null]));
    Http::fake(['api.paystack.co/subaccount' => Http::response(['status' => false, 'message' => 'Invalid bank code'], 400)]);

    $result = app(PaystackService::class)->createSubaccount($info);

    expect($result['success'])->toBeFalse()->and($result['message'])->toBe('Invalid bank code')
        ->and($info->fresh()->paystack_subaccount_code)->toBeNull();
});

it('F2-94 turns a gateway exception into a failed result', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['paystack_subaccount_code' => null]));
    Http::fake(['api.paystack.co/subaccount' => fn () => throw new Exception('Timeout')]);

    expect(app(PaystackService::class)->createSubaccount($info)['success'])->toBeFalse();
});

it('F2-95 reads and updates a subaccount', function () {
    Http::fake([
        'api.paystack.co/subaccount/ACCT_x' => Http::sequence()
            ->push(['status' => true, 'data' => ['subaccount_code' => 'ACCT_x']])
            ->push(['status' => true, 'data' => ['subaccount_code' => 'ACCT_x', 'percentage_charge' => 5]])
            ->push(['status' => false, 'message' => 'Not found'], 404)
            ->push(['status' => false, 'message' => 'Not found'], 404),
    ]);
    $service = app(PaystackService::class);

    expect($service->getSubaccount('ACCT_x')['data']['subaccount_code'])->toBe('ACCT_x');
    expect($service->updateSubaccount('ACCT_x', ['percentage_charge' => 5])['success'])->toBeTrue();
    expect($service->getSubaccount('ACCT_x')['success'])->toBeFalse();
    expect($service->updateSubaccount('ACCT_x', [])['success'])->toBeFalse();
});

it('F2-96 verifyPayment reports a gateway failure and an exception as failed results', function () {
    Http::fake(['api.paystack.co/transaction/verify/BAD' => Http::response(['status' => false, 'message' => 'Not found'], 404)]);
    expect(app(PaystackService::class)->verifyPayment('BAD')['success'])->toBeFalse();

    Http::swap(new Factory);
    Http::fake(['api.paystack.co/transaction/verify/BOOM' => fn () => throw new Exception('Timeout')]);
    expect(app(PaystackService::class)->verifyPayment('BOOM')['success'])->toBeFalse();
});

it('F2-97 a successful callback for an organiser without a subaccount still confirms the order', function () {
    PaymentInfo::withoutEvents(fn () => PaymentInfo::where('organizer_id', $this->organizer->id)->update(['paystack_subaccount_code' => null]));
    [$order, , $reference] = makePendingOrder($this->event, $this->ticketType);
    fakePaystackVerify($reference, 10000);

    $this->get(route('payment.callback', ['reference' => $reference]));

    expect($order->fresh()->status)->toBe('confirmed')->and(Payment::where('transaction_id', $reference)->value('status'))->toBe('completed');
});
