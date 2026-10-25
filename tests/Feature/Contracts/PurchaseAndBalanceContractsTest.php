<?php

use App\Models\Discount;
use App\Models\Order;
use App\Models\PaymentInfo;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\TicketType;
use App\Models\User;
use App\Services\PaystackBalanceService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

/*
 * Contracts of the purchase, discount and balance code: exact messages,
 * payloads and stored values. Case identifiers (F1-9x, F2-12x) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.
 */

beforeEach(function () {
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
    fakePaystackInitialize();
});

function buy($test, array $lines, array $extra = [])
{
    return $test->post(route('events.tickets.purchase', $test->event), purchasePayload($lines, $extra));
}

it('F1-90 each purchase refusal carries its exact message', function () {
    $refuse = fn ($response, string $message) => $response->assertSessionHasErrors(['error' => $message]);

    $this->event->update(['status' => 'draft']);
    $refuse(buy($this, [[$this->ticketType, 1]]), 'This event is still in draft mode.');
    $this->event->update(['status' => 'canceled']);
    $refuse(buy($this, [[$this->ticketType, 1]]), 'This event has been canceled.');
    $this->event->update(['status' => 'published']);

    $this->ticketType->update(['quantity' => 1]);
    $refuse(buy($this, [[$this->ticketType, 2]]), 'Not enough tickets available for '.$this->ticketType->name.'. Only 1 tickets remaining.');

    $payload = purchasePayload([[$this->ticketType, 1]]);
    $payload['tickets'][0]['attendees'] = [['name' => 'A'], ['name' => 'B']];
    $refuse($this->post(route('events.tickets.purchase', $this->event), $payload), 'Number of attendees must match ticket quantity for '.$this->ticketType->name);

    [, , $foreign] = makeCheckout();
    $refuse(buy($this, [[$foreign, 1]]), 'Invalid ticket type for this event');
    expect(Order::count())->toBe(0);
});

it('F1-91 each discount refusal carries its exact message', function () {
    $base = ['organizer_id' => $this->organizer->id, 'event_id' => $this->event->id];
    $cases = [
        [['is_active' => false], 'Discount code is no longer active'],
        [['usage_limit' => 2, 'used_count' => 2], 'Discount code has reached its usage limit'],
        [['valid_from' => now()->addDay(), 'valid_until' => now()->addMonth()], 'Discount code is not yet active'],
        [['valid_from' => now()->subMonth(), 'valid_until' => now()->subDay()], 'Discount code has expired'],
    ];
    foreach ($cases as [$attributes, $message]) {
        $discount = Discount::factory()->create($base + $attributes);
        buy($this, [[$this->ticketType, 1]], ['discount_id' => $discount->id])->assertSessionHasErrors(['error' => $message]);
    }

    [, $other] = makeCheckout();
    $foreign = Discount::factory()->create(['organizer_id' => $other->organizer_id, 'event_id' => $other->id]);
    buy($this, [[$this->ticketType, 1]], ['discount_id' => $foreign->id])->assertSessionHasErrors(['error' => 'Discount code is not valid for this event']);

    $open = Discount::factory()->create(['organizer_id' => $this->organizer->id, 'event_id' => null]);
    buy($this, [[$this->ticketType, 1]], ['discount_id' => $open->id])->assertSessionHasNoErrors();
    expect(Order::count())->toBe(1);
});

it('F1-92 a stored order carries the buyer, discount and amounts', function () {
    $discount = Discount::factory()->fixed(30)->create(['organizer_id' => $this->organizer->id, 'event_id' => $this->event->id]);

    buy($this, [[$this->ticketType, 2]], ['discount_id' => $discount->id, 'buyer_phone' => '+233201234567', 'buyer_email' => 'kofi@example.com']);

    $order = Order::firstOrFail();
    expect($order->event_id)->toBe($this->event->id)
        ->and($order->organizer_id)->toBe($this->organizer->id)
        ->and($order->buyer_email)->toBe('kofi@example.com')
        ->and($order->buyer_phone)->toBe('+233201234567')
        ->and((float) $order->subtotal_amount)->toBe(200.0)
        ->and($order->discount_id)->toBe($discount->id)
        ->and((float) $order->discount_amount)->toBe(30.0)
        ->and((float) $order->total_amount)->toBe(170.0)
        ->and($order->status)->toBe('pending')
        ->and($order->order_number)->toStartWith('ORD-');
    $ticket = $order->tickets()->orderBy('id')->first();
    expect($ticket->event_id)->toBe($this->event->id)->and($ticket->organizer_id)->toBe($this->organizer->id)
        ->and($ticket->ticket_type_id)->toBe($this->ticketType->id)->and($ticket->attendee_name)->toBe('Attendee 1')
        ->and((float) $ticket->price)->toBe(100.0)->and($ticket->status)->toBe('pending')
        ->and($ticket->ticket_number)->toStartWith('TKT-');
});

it('F1-93 attendee emails are kept and an empty custom field is not stored', function () {
    $field = $this->event->formFields()->create(['organizer_id' => $this->organizer->id, 'label' => 'Diet', 'field_type' => 'text']);
    $payload = purchasePayload([[$this->ticketType, 1]]);
    $payload['tickets'][0]['attendees'][0]['email'] = 'guest@example.com';
    $payload['tickets'][0]['attendees'][0]['custom_fields'] = [$field->id => ''];

    $this->post(route('events.tickets.purchase', $this->event), $payload);

    expect(Ticket::firstOrFail()->attendee_email)->toBe('guest@example.com')
        ->and(TicketResponse::count())->toBe(0);
});

it('F1-94 the discount endpoint answers with the exact payload and messages', function () {
    Discount::factory()->percentage(25)->create(['organizer_id' => $this->organizer->id, 'event_id' => $this->event->id, 'code' => 'SAVE25', 'name' => 'Quarter off']);

    $ok = $this->postJson(route('discounts.validate'), ['code' => 'save25', 'event_id' => $this->event->id, 'subtotal' => 80])->assertOk();
    $discount = $ok->json('discount');
    expect($ok->json('valid'))->toBeTrue()
        ->and(array_keys($discount))->toBe(['id', 'code', 'name', 'type', 'value', 'amount', 'subtotal', 'total'])
        ->and($discount['code'])->toBe('SAVE25')->and($discount['name'])->toBe('Quarter off')->and($discount['type'])->toBe('percentage')
        ->and((float) $discount['value'])->toBe(25.0)->and($discount['amount'])->toBe(20)->and($discount['subtotal'])->toBe(80)->and($discount['total'])->toBe(60);

    $this->postJson(route('discounts.validate'), ['code' => 'NOPE', 'event_id' => $this->event->id, 'subtotal' => 80])
        ->assertStatus(422)->assertExactJson(['valid' => false, 'message' => 'Invalid discount code']);

    [, $other] = makeCheckout();
    Discount::factory()->create(['organizer_id' => $other->organizer_id, 'event_id' => $other->id, 'code' => 'THEIRS']);
    $this->postJson(route('discounts.validate'), ['code' => 'THEIRS', 'event_id' => $this->event->id, 'subtotal' => 80])
        ->assertStatus(422)->assertExactJson(['valid' => false, 'message' => 'This discount code is not valid for this event']);

    $this->postJson(route('discounts.validate'), ['code' => 'SAVE25', 'event_id' => $this->event->id])->assertStatus(422);
    $this->postJson(route('discounts.validate'), ['event_id' => $this->event->id, 'subtotal' => 1])->assertStatus(422);
    $this->postJson(route('discounts.validate'), ['code' => 'SAVE25', 'event_id' => 0, 'subtotal' => 1])->assertStatus(422);
});

it('F1-95 the discount model normalises the code, caps amounts and judges its dates', function () {
    $discount = Discount::factory()->create(['organizer_id' => $this->organizer->id, 'code' => ' save 10 ']);
    expect($discount->code)->toBe('SAVE10');
    $discount->update(['code' => ' new code ']);
    expect($discount->fresh()->code)->toBe('NEWCODE');

    $percent = Discount::factory()->percentage(50)->make();
    $fixed = Discount::factory()->fixed(30)->make();
    $unknown = Discount::factory()->make(['type' => 'other', 'value' => 10]);
    expect($percent->amountFor(33.33))->toBe(16.67)
        ->and($percent->amountFor(-5))->toBe(0.0)
        ->and(Discount::factory()->percentage(150)->make()->amountFor(40))->toBe(40.0)
        ->and(Discount::factory()->percentage(100)->make()->amountFor(40))->toBe(40.0)
        ->and(Discount::factory()->percentage(99)->make()->amountFor(100))->toBe(99.0)
        ->and($fixed->amountFor(100))->toBe(30.0)
        ->and($fixed->amountFor(20))->toBe(20.0)
        ->and($unknown->amountFor(100))->toBe(10.0);

    expect(Discount::factory()->make()->dateProblem())->toBeNull()
        ->and(Discount::factory()->notYetValid()->make()->dateProblem())->toBe('not_started')
        ->and(Discount::factory()->expired()->make()->dateProblem())->toBe('expired')
        ->and(Discount::factory()->make(['valid_from' => now()->toDateString(), 'valid_until' => now()->toDateString()])->dateProblem())->toBeNull();
});

it('F1-96 the buyer form and ticket list pages carry the expected props', function () {
    $discount = Discount::factory()->percentage(10)->create(['organizer_id' => $this->organizer->id, 'event_id' => $this->event->id]);
    $field = $this->event->formFields()->create(['organizer_id' => $this->organizer->id, 'label' => 'Diet', 'field_type' => 'text']);

    $this->post(route('events.tickets.buyer-form', $this->event), ['ticket_selections' => [['ticket_type_id' => $this->ticketType->id, 'quantity' => 3]], 'discount_id' => $discount->id])
        ->assertInertia(fn ($page) => $page
            ->component('TicketBuyerForm')
            ->where('event.id', $this->event->id)
            ->where('event.organizer.id', $this->organizer->id)
            ->where('ticketTypes.0.id', $this->ticketType->id)
            ->where('ticketSelections.0.ticket_type_id', $this->ticketType->id)
            ->where('appliedDiscount.id', $discount->id)
            ->where('appliedDiscount.amount', 30)
            ->where('custom_fields.0.label', 'Diet'));

    $this->getJson(route('events.tickets.available', $this->event))->assertOk()
        ->assertJsonPath('event.id', $this->event->id)
        ->assertJsonPath('event.title', $this->event->title)
        ->assertJsonPath('event.status', 'published')
        ->assertJsonPath('ticket_types.0.id', $this->ticketType->id)
        ->assertJsonStructure(['purchasable', 'ticket_types', 'event' => ['id', 'title', 'start_date', 'start_time', 'status']]);

    $this->event->update(['status' => 'draft']);
    $this->getJson(route('events.tickets.available', $this->event))->assertStatus(422)
        ->assertJsonPath('error', 'This event is still in draft mode.')
        ->assertJsonPath('event.status', 'draft');
});

it('F1-97 the buyer form hides a discount that is no longer usable and refuses stale selections', function () {
    $this->post(route('events.tickets.buyer-form', $this->event), ['ticket_selections' => [['ticket_type_id' => $this->ticketType->id, 'quantity' => 0]]])
        ->assertRedirect()->assertSessionHasErrors(['error' => 'Please select tickets before proceeding.']);

    $this->ticketType->update(['quantity' => 1]);
    $this->post(route('events.tickets.buyer-form', $this->event), ['ticket_selections' => [['ticket_type_id' => $this->ticketType->id, 'quantity' => 2]]])
        ->assertSessionHasErrors(['error' => 'Not enough tickets available for '.$this->ticketType->name.'.']);

    $this->event->update(['status' => 'canceled']);
    $this->post(route('events.tickets.buyer-form', $this->event), ['ticket_selections' => [['ticket_type_id' => $this->ticketType->id, 'quantity' => 1]]])
        ->assertSessionHasErrors(['error' => 'This event has been canceled.']);
});

it('F1-98 the download route and confirmation page give the order back', function () {
    [$order] = makePendingOrder($this->event, $this->ticketType, 1);

    $this->getJson(route('orders.download', $order->order_number))
        ->assertOk()->assertJsonPath('message', 'Ticket download functionality to be implemented')->assertJsonPath('order.id', $order->id);
    $this->get(route('orders.confirmation', $order->order_number))
        ->assertInertia(fn ($page) => $page->where('order.id', $order->id)->where('order.event.id', $this->event->id)->where('tickets.0.id', $order->tickets()->first()->id));
});

// ---------------------------------------------------------------- Paystack controller and balances

it('F2-120 the bank, account and balance endpoints answer with exact payloads', function () {
    $staff = User::factory()->create();
    $this->organizer->users()->attach($staff);
    $info = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    $this->actingAs($staff);

    Http::fake([
        'api.paystack.co/bank/resolve*' => Http::sequence()
            ->push(['status' => true, 'data' => ['account_name' => 'KWAME', 'account_number' => '0123456789', 'bank_id' => 1]])
            ->push(['status' => false, 'message' => 'Unknown account'], 422),
        'api.paystack.co/bank?country*' => Http::sequence()
            ->push(['status' => true, 'data' => [['code' => '1', 'name' => 'A Bank', 'slug' => 'a']]])
            ->push(['status' => false, 'message' => 'Busy'], 400),
        'api.paystack.co/balance*' => Http::sequence()
            ->push(['status' => true, 'data' => [['balance' => 150025]]])
            ->push(['status' => false, 'message' => 'x'], 400),
    ]);

    $this->postJson(route('paystack.verify-account'), ['account_number' => '0123456789', 'bank_code' => '040100'])
        ->assertExactJson(['success' => true, 'account_name' => 'KWAME', 'account_number' => '0123456789']);
    $this->postJson(route('paystack.verify-account'), ['account_number' => '0123456789', 'bank_code' => '040100'])
        ->assertStatus(400)->assertExactJson(['success' => false, 'message' => 'Unknown account']);
    $this->postJson(route('paystack.verify-account'), ['account_number' => '0123456789'])->assertStatus(422);

    $this->getJson(route('paystack.banks'))->assertExactJson(['success' => true, 'banks' => [['code' => '1', 'name' => 'A Bank', 'slug' => 'a']]]);
    $this->getJson(route('paystack.banks', ['country' => 'nigeria']))->assertStatus(400)->assertExactJson(['success' => false, 'message' => 'Busy']);

    $sync = $this->postJson(route('paystack.sync-balance', $info))->assertOk();
    expect($sync->json('success'))->toBeTrue()
        ->and($sync->json('available_balance'))->toBe('GHS 1,500.25')
        ->and($sync->json('ledger_balance'))->toBe('GHS 1,500.25')
        ->and($sync->json('last_updated'))->toBeString();
    $this->postJson(route('paystack.sync-balance', $info))->assertStatus(400)->assertExactJson(['success' => false, 'message' => 'Failed to sync balance']);

    $outsider = User::factory()->create();
    makeCheckout()[0]->users()->attach($outsider);
    $this->actingAs($outsider)->postJson(route('paystack.sync-balance', $info))->assertStatus(403)->assertExactJson(['error' => 'Unauthorized']);
});

it('F2-121 a balance sync returns and stores every figure it calculates', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['percentage_charge' => 10]));
    $tx = fn (string $ref, int $amount, string $status, string $at, array $extra = []) => array_merge(['reference' => $ref, 'amount' => $amount, 'status' => $status, 'created_at' => $at], $extra);
    Http::fake([
        'api.paystack.co/transaction/verify/A' => Http::response(['status' => true, 'data' => $tx('A', 10000, 'success', '2026-10-01T10:00:00.000Z', ['fees_split' => ['subaccount' => 9000, 'integration' => 1000]])]),
        'api.paystack.co/transaction/verify/B' => Http::response(['status' => true, 'data' => $tx('B', 20000, 'pending', '2026-10-03T10:00:00.000Z')]),
        'api.paystack.co/transaction/verify/C' => Http::response(['status' => false], 404),
        'api.paystack.co/transaction*' => Http::response(['status' => true, 'data' => [$tx('A', 10000, 'success', '2026-10-01T10:00:00.000Z'), $tx('B', 20000, 'pending', '2026-10-03T10:00:00.000Z'), $tx('C', 5000, 'failed', '2026-10-02T10:00:00.000Z')]]),
    ]);

    $result = app(PaystackBalanceService::class)->syncSubaccountBalances($info);

    expect($result['subaccount_balance'])->toBe(315.0)
        ->and(array_keys($result))->toBe(['subaccount_balance', 'system_admin_balance', 'total_transaction_volume', 'transaction_counts', 'last_transaction', 'split_data_coverage'])
        ->and($result['system_admin_balance'])->toBe(35.0)
        ->and($result['total_transaction_volume'])->toBe(350.0)
        ->and($result['transaction_counts'])->toBe(['total' => 3, 'successful' => 1, 'pending' => 1])
        ->and($result['last_transaction']['reference'])->toBe('B')
        ->and($result['last_transaction']['amount'])->toBe(200.0)
        ->and($result['split_data_coverage'])->toBe(33.3);
    $info->refresh();
    expect((float) $info->subaccount_balance)->toBe(315.0)->and($info->successful_transactions)->toBe(1)->and($info->pending_transactions)->toBe(1)
        ->and($info->last_transaction_reference)->toBe('B')->and((float) $info->last_transaction_amount)->toBe(200.0);
    $request = Http::recorded()->map(fn ($p) => $p[0])->first(fn ($r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/transaction'));
    expect($request->data())->toMatchArray(['subaccount' => $info->paystack_subaccount_code, 'page' => 1, 'perPage' => 200, 'status' => 'success']);
});

it('F2-122 an empty transaction list gives zeroes and a missing last transaction', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create());
    Http::fake(['api.paystack.co/transaction*' => Http::response(['status' => true, 'data' => []])]);

    $result = app(PaystackBalanceService::class)->syncSubaccountBalances($info);

    expect($result['transaction_counts'])->toBe(['total' => 0, 'successful' => 0, 'pending' => 0])
        ->and($result['last_transaction'])->toBeNull()
        ->and($result['split_data_coverage'])->toBe(0)
        ->and($result['total_transaction_volume'])->toBe(0.0);
});

it('F2-123 the balance service explains its failures and syncAll keeps its row shape', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create());
    Http::fake(['api.paystack.co/transaction*' => Http::sequence()
        ->push('server error', 500)
        ->push(['status' => false, 'message' => 'Rate limited'])
        ->push(['status' => false], 200)]);
    $service = app(PaystackBalanceService::class);

    expect(fn () => $service->syncSubaccountBalances($info))->toThrow(Exception::class, 'Failed to fetch transactions: server error');
    expect(fn () => $service->syncSubaccountBalances($info))->toThrow(Exception::class, 'Paystack API error: Rate limited');
    expect(fn () => $service->syncSubaccountBalances($info))->toThrow(Exception::class, 'Paystack API error: Unknown error');

    PaymentInfo::query()->update(['is_active' => false]);
    $good = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create());
    PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['paystack_subaccount_code' => null]));
    Http::swap(new Factory);
    Http::fake(['api.paystack.co/transaction*' => Http::response(['status' => true, 'data' => []])]);

    $rows = $service->syncAllSubaccountBalances();

    expect($rows)->toHaveCount(1)
        ->and(array_keys($rows[0]))->toBe(['payment_info_id', 'organizer_name', 'success', 'balances'])
        ->and($rows[0]['payment_info_id'])->toBe($good->id)
        ->and($rows[0]['organizer_name'])->toBe($good->organizer->name)
        ->and($rows[0]['success'])->toBeTrue();
});

it('F2-124 single transaction processing returns its changes and counts statuses', function () {
    $info = PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['percentage_charge' => 10]));
    $service = app(PaystackBalanceService::class);

    $changes = $service->processTransactionForBalance($info, ['reference' => 'P1', 'amount' => 10000, 'status' => 'pending', 'created_at' => '2026-10-01T10:00:00.000Z', 'fees_split' => ['subaccount' => 9500, 'integration' => 500]]);

    expect($changes)->toBe(['total_amount' => 100.0, 'subaccount_amount' => 95.0, 'system_admin_amount' => 5.0, 'has_fees_split_data' => true]);
    $info->refresh();
    expect($info->pending_transactions)->toBe(1)->and($info->successful_transactions)->toBe(0)->and($info->total_transactions)->toBe(1)
        ->and($info->last_transaction_reference)->toBe('P1')->and((float) $info->last_transaction_amount)->toBe(100.0);

    $fallback = $service->processTransactionForBalance($info, ['reference' => 'P2', 'amount' => 20000, 'status' => 'success', 'created_at' => '2026-10-02T10:00:00.000Z', 'fees_split' => ['subaccount' => 18000]]);
    expect($fallback['system_admin_amount'])->toBe(0.0)->and($fallback['subaccount_amount'])->toBe(180.0);
    $info->refresh();
    expect($info->successful_transactions)->toBe(1)->and($info->total_transactions)->toBe(2);
});

it('F2-125 the system summary excludes inactive subaccounts and ones without a code count as not active', function () {
    PaymentInfo::query()->update(['is_active' => false]);
    expect(app(PaystackBalanceService::class)->getSystemBalanceSummary())->toBe([
        'total_subaccount_balance' => 0.0, 'total_system_admin_balance' => 0.0, 'total_transaction_volume' => 0.0,
        'total_balance' => 0.0, 'total_transactions' => 0, 'active_subaccounts' => 0,
        'average_transaction_value' => 0, 'system_admin_percentage' => 0,
    ]);

    PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['paystack_subaccount_code' => null, 'subaccount_balance' => 10, 'total_transaction_volume' => 10, 'total_transactions' => 1]));
    PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['subaccount_balance' => 20, 'total_transaction_volume' => 30, 'total_transactions' => 2]));
    $summary = app(PaystackBalanceService::class)->getSystemBalanceSummary();

    expect($summary['active_subaccounts'])->toBe(1)->and($summary['total_transactions'])->toBe(3)->and($summary['total_subaccount_balance'])->toBe(30.0)
        ->and($summary['average_transaction_value'])->toBe(13.33)->and($summary['system_admin_percentage'])->toBe(0.0);
});

it('F2-126 a ticket type and an order keep the numbers they derive', function () {
    $type = TicketType::factory()->stock(7)->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
    expect($type->available_quantity)->toBe(7)->and($type->total_capacity)->toBe(7)->and($type->sold_tickets_count)->toBe(0);
});
