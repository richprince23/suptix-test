<?php

// use App\Models\Discount;
// use App\Models\Event;
// use App\Models\Order;
// use App\Models\Payment;
// use App\Models\Ticket;
// use App\Models\TicketResponse;
// use App\Models\TicketType;

// /*
//  * Flow F1: purchase and pricing. Case identifiers (F1-xx) refer to
//  * docs/testing/TEST_PLAN.md, Appendix A.1.
//  */

// beforeEach(function () {
//     [$this->organizer, $this->event, $this->ticketType] = makeCheckout();
//     fakePaystackInitialize();
// });

// function purchase(Event $event, array $payload)
// {
//     return test()->post(route('events.tickets.purchase', $event), $payload);
// }

// // ---------------------------------------------------------------- request validation

// it('F1-01 creates a pending order and ticket, reserves stock and redirects to Paystack', function () {
//     $response = purchase($this->event, purchasePayload([[$this->ticketType, 1]]));

//     $response->assertRedirect();
//     expect($response->headers->get('Location'))->toStartWith('https://checkout.paystack.test/');

//     $order = Order::firstOrFail();
//     expect($order->status)->toBe('pending')
//         ->and((float) $order->total_amount)->toBe(100.0)
//         ->and($order->tickets)->toHaveCount(1)
//         ->and($order->tickets->first()->status)->toBe('pending')
//         ->and($this->ticketType->fresh()->quantity)->toBe(49)
//         ->and(Payment::where('order_id', $order->id)->value('status'))->toBe('pending');
// });

// it('F1-02 rejects a missing buyer email', function () {
//     $payload = purchasePayload([[$this->ticketType, 1]]);
//     unset($payload['buyer_email']);

//     purchase($this->event, $payload)->assertSessionHasErrors('buyer_email');
//     expect(Order::count())->toBe(0);
// });

// it('F1-03 rejects an invalid buyer email', function () {
//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['buyer_email' => 'not-an-email']))
//         ->assertSessionHasErrors('buyer_email');
//     expect(Order::count())->toBe(0);
// });

// it('F1-04 rejects an empty tickets array', function () {
//     purchase($this->event, ['buyer_email' => 'buyer@example.com', 'tickets' => []])
//         ->assertSessionHasErrors('tickets');
//     expect(Order::count())->toBe(0);
// });

// it('F1-05 to F1-08 applies the quantity boundaries 0, 1, 10 and 11', function (int $quantity, bool $accepted) {
//     $response = purchase($this->event, purchasePayload([[$this->ticketType, $quantity]]));

//     if ($accepted) {
//         $response->assertSessionHasNoErrors();
//         expect(Order::count())->toBe(1)
//             ->and(Ticket::count())->toBe($quantity)
//             ->and($this->ticketType->fresh()->quantity)->toBe(50 - $quantity);
//     } else {
//         $response->assertSessionHasErrors();
//         expect(Order::count())->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(50);
//     }
// })->with([
//     'F1-05 zero' => [0, false],
//     'F1-06 one' => [1, true],
//     'F1-07 ten' => [10, true],
//     'F1-08 eleven' => [11, false],
// ]);

// it('F1-09 rejects an attendee count that differs from the quantity and leaves stock unchanged', function () {
//     $payload = purchasePayload([[$this->ticketType, 2]]);
//     $payload['tickets'][0]['attendees'] = [['name' => 'Only One']];

//     purchase($this->event, $payload)->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(50);
// });

// it('F1-10 rejects a missing attendee name', function () {
//     $payload = purchasePayload([[$this->ticketType, 1]]);
//     $payload['tickets'][0]['attendees'] = [['email' => 'a@example.com']];

//     purchase($this->event, $payload)->assertSessionHasErrors();
//     expect(Order::count())->toBe(0);
// });

// it('F1-11 rejects a filled honeypot with a generic message and creates nothing', function () {
//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['honeypot' => 'bot']))
//         ->assertSessionHasErrors(['error' => 'Unable to process your request at this time. Please try again.']);

//     expect(Order::count())->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(50);
// });

// it('F1-12 rejects an unknown ticket type', function () {
//     $payload = purchasePayload([[$this->ticketType, 1]]);
//     $payload['tickets'][0]['ticket_type_id'] = 999999999;

//     purchase($this->event, $payload)->assertSessionHasErrors();
//     expect(Order::count())->toBe(0);
// });

// it('F1-13 rejects a custom field value over 1000 characters', function () {
//     $payload = purchasePayload([[$this->ticketType, 1]]);
//     $payload['tickets'][0]['attendees'][0]['custom_fields'] = [1 => str_repeat('a', 1001)];

//     purchase($this->event, $payload)->assertSessionHasErrors();
//     expect(Order::count())->toBe(0);
// });

// it('F1-14 stores custom field responses as TicketResponse rows', function () {
//     $field = $this->event->formFields()->create([
//         'organizer_id' => $this->organizer->id,
//         'label' => 'T-shirt size',
//         'field_type' => 'text',
//     ]);

//     $payload = purchasePayload([[$this->ticketType, 1]]);
//     $payload['tickets'][0]['attendees'][0]['custom_fields'] = [$field->id => 'Large'];

//     purchase($this->event, $payload)->assertSessionHasNoErrors();

//     expect(TicketResponse::where('event_form_field_id', $field->id)->value('value'))->toBe('Large');
// });

// it('F1-15 accepts the quantity as a numeric string from an ordinary form post', function () {
//     $payload = purchasePayload([[$this->ticketType, 2]]);
//     $payload['tickets'][0]['quantity'] = '2';

//     purchase($this->event, $payload)->assertSessionHasNoErrors();
//     expect(Order::count())->toBe(1)->and(Ticket::count())->toBe(2);
// });

// // ---------------------------------------------------------------- event and ticket-type rules

// it('F1-20 and F1-21 refuse to sell for a draft or cancelled event', function (string $status) {
//     $this->event->update(['status' => $status]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]]))->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(50);
// })->with(['F1-20 draft' => 'draft', 'F1-21 canceled' => 'canceled']);

// it('F1-22 sells for published and live events', function (string $status) {
//     $this->event->update(['status' => $status]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]]))->assertSessionHasNoErrors();
//     expect(Order::count())->toBe(1);
// })->with(['F1-22 published' => 'published', 'F1-22 live' => 'live']);

// it('F1-25 refuses a ticket type whose sales have not started', function () {
//     $this->ticketType->update(['sales_start' => now()->addDay()]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]]))
//         ->assertSessionHasErrors(['error' => 'No tickets are currently available for this event.']);
//     expect(Order::count())->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(50);
// });

// it('F1-26 refuses a ticket type whose sales have ended', function () {
//     $this->ticketType->update(['sales_end' => now()->subDay()]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]]))
//         ->assertSessionHasErrors(['error' => 'No tickets are currently available for this event.']);
//     expect(Order::count())->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(50);
// });

// it('F1-25 and F1-26 name the closed ticket type when another type is still on sale', function (string $window, string $message) {
//     $closed = TicketType::factory()->create([
//         'event_id' => $this->event->id,
//         'organizer_id' => $this->organizer->id,
//         'name' => 'Closed',
//         ...($window === 'not started' ? ['sales_start' => now()->addDay()] : ['sales_end' => now()->subDay()]),
//     ]);

//     purchase($this->event, purchasePayload([[$closed, 1]]))->assertSessionHasErrors(['error' => $message]);
//     expect(Order::count())->toBe(0)->and($closed->fresh()->quantity)->toBe(50);
// })->with([
//     'F1-25 not started' => ['not started', 'Sales for Closed have not started yet'],
//     'F1-26 ended' => ['ended', 'Sales for Closed have ended'],
// ]);

// it('F1-28 rejects a ticket type that belongs to a different event', function () {
//     [, , $foreignType] = makeCheckout();

//     purchase($this->event, purchasePayload([[$foreignType, 1]]))->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0)->and($foreignType->fresh()->quantity)->toBe(50);
// });

// it('F1-29 rejects a ticket type with no stock left', function () {
//     $this->ticketType->update(['quantity' => 0]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]]))->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0);
// });

// it('F1-30 accepts a quantity equal to the remaining stock and leaves none', function () {
//     $this->ticketType->update(['quantity' => 3]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 3]]))->assertSessionHasNoErrors();
//     expect($this->ticketType->fresh()->quantity)->toBe(0)->and(Ticket::count())->toBe(3);
// });

// it('F1-31 rejects a quantity one above the remaining stock and leaves stock unchanged', function () {
//     $this->ticketType->update(['quantity' => 3]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 4]]))->assertSessionHasErrors('error');
//     expect($this->ticketType->fresh()->quantity)->toBe(3)->and(Order::count())->toBe(0);
// });

// it('F1-32 prices two ticket types as the sum of price times quantity', function () {
//     $vip = TicketType::factory()->priced(250)->stock(10)->create([
//         'event_id' => $this->event->id,
//         'organizer_id' => $this->organizer->id,
//         'name' => 'VIP',
//     ]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 2], [$vip, 1]]))->assertSessionHasNoErrors();

//     $order = Order::firstOrFail();
//     expect((float) $order->total_amount)->toBe(450.0)
//         ->and($order->tickets)->toHaveCount(3)
//         ->and($this->ticketType->fresh()->quantity)->toBe(48)
//         ->and($vip->fresh()->quantity)->toBe(9);
// });

// it('F1-33 reserves nothing when the second ticket type lacks stock', function () {
//     $scarce = TicketType::factory()->stock(1)->create([
//         'event_id' => $this->event->id,
//         'organizer_id' => $this->organizer->id,
//         'name' => 'Scarce',
//     ]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 2], [$scarce, 2]]))->assertSessionHasErrors('error');

//     expect(Order::count())->toBe(0)
//         ->and(Ticket::count())->toBe(0)
//         ->and($this->ticketType->fresh()->quantity)->toBe(50)
//         ->and($scarce->fresh()->quantity)->toBe(1);
// });

// // ---------------------------------------------------------------- totals and discounts

// function discountFor($test, array $attributes = []): Discount
// {
//     return Discount::factory()->create(array_merge([
//         'organizer_id' => $test->organizer->id,
//         'event_id' => $test->event->id,
//     ], $attributes));
// }

// it('F1-40 totals the ticket prices when there is no discount', function () {
//     purchase($this->event, purchasePayload([[$this->ticketType, 3]]));

//     $order = Order::firstOrFail();
//     expect((float) $order->total_amount)->toBe(300.0)->and((float) $order->discount_amount)->toBe(0.0);
// });

// it('F1-41 applies a 10% discount: discount 10, total 90, subtotal 100', function () {
//     $discount = discountFor($this, ['type' => 'percentage', 'value' => 10]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     $order = Order::firstOrFail();
//     expect((float) $order->discount_amount)->toBe(10.0)
//         ->and((float) $order->total_amount)->toBe(90.0)
//         ->and((float) $order->subtotal_amount)->toBe(100.0);
// });

// it('F1-43 a 100% discount gives a total of 0', function () {
//     $discount = discountFor($this, ['type' => 'percentage', 'value' => 100]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     expect((float) Order::firstOrFail()->total_amount)->toBe(0.0);
// });

// it('F1-44 caps a percentage above 100 at the order value', function () {
//     $discount = discountFor($this, ['type' => 'percentage', 'value' => 150]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     $order = Order::firstOrFail();
//     expect((float) $order->discount_amount)->toBe(100.0)
//         ->and((float) $order->subtotal_amount)->toBe(100.0)
//         ->and((float) $order->total_amount)->toBe(0.0);
// });

// it('F1-45 subtracts a fixed discount smaller than the subtotal', function () {
//     $discount = discountFor($this, ['type' => 'fixed_amount', 'value' => 30]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     $order = Order::firstOrFail();
//     expect((float) $order->discount_amount)->toBe(30.0)->and((float) $order->total_amount)->toBe(70.0);
// });

// it('F1-46 caps a fixed discount larger than the subtotal', function () {
//     $discount = discountFor($this, ['type' => 'fixed_amount', 'value' => 500]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     $order = Order::firstOrFail();
//     expect((float) $order->discount_amount)->toBe(100.0)->and((float) $order->total_amount)->toBe(0.0);
// });

// it('F1-47 rounds a fractional percentage discount to two decimals', function () {
//     $ticketType = TicketType::factory()->priced(10)->create([
//         'event_id' => $this->event->id,
//         'organizer_id' => $this->organizer->id,
//     ]);
//     $discount = discountFor($this, ['type' => 'percentage', 'value' => 33.33]);

//     purchase($this->event, purchasePayload([[$ticketType, 1]], ['discount_id' => $discount->id]));

//     $stored = DB::table('orders')->first();
//     expect((string) $stored->discount_amount)->toBe('3.33')->and((string) $stored->total_amount)->toBe('6.67');
// });

// it('F1-48 rejects an inactive discount', function () {
//     $discount = discountFor($this, ['is_active' => false]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]))
//         ->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0);
// });

// it('F1-49 rejects a discount that belongs to another event', function () {
//     [, $otherEvent] = makeCheckout();
//     $discount = Discount::factory()->create([
//         'organizer_id' => $otherEvent->organizer_id,
//         'event_id' => $otherEvent->id,
//     ]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]))
//         ->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0);
// });

// it('F1-50 applies the usage-limit boundary: limit minus one is accepted, the limit is rejected', function (int $used, bool $accepted) {
//     $discount = discountFor($this, ['usage_limit' => 5, 'used_count' => $used]);

//     $response = purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     if ($accepted) {
//         $response->assertSessionHasNoErrors();
//         expect(Order::count())->toBe(1);
//     } else {
//         $response->assertSessionHasErrors('error');
//         expect(Order::count())->toBe(0);
//     }
// })->with([
//     'F1-50 limit minus one' => [4, true],
//     'F1-50 at limit' => [5, false],
// ]);

// it('F1-51 rejects an expired discount at purchase', function () {
//     $discount = discountFor($this, ['valid_from' => now()->subMonth(), 'valid_until' => now()->subDay()]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]))
//         ->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0);
// });

// it('F1-52 rejects a not-yet-valid discount at purchase', function () {
//     $discount = discountFor($this, ['valid_from' => now()->addDay(), 'valid_until' => now()->addMonth()]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]))
//         ->assertSessionHasErrors('error');
//     expect(Order::count())->toBe(0);
// });

// it('F1-53 increments the discount usage count by one', function () {
//     $discount = discountFor($this, ['used_count' => 2]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));

//     expect($discount->fresh()->used_count)->toBe(3);
// });

// it('F1-55 ignores a client-supplied price and uses the database price', function () {
//     $payload = purchasePayload([[$this->ticketType, 1]]);
//     $payload['tickets'][0]['price'] = 1;
//     $payload['total_amount'] = 1;

//     purchase($this->event, $payload);

//     expect((float) Order::firstOrFail()->total_amount)->toBe(100.0)
//         ->and((float) Ticket::firstOrFail()->price)->toBe(100.0);
// });

// it('X-01 ignores mass-assigned order status and amount fields', function () {
//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], [
//         'status' => 'confirmed',
//         'total_amount' => 0,
//         'payment_reference' => 'FORGED',
//     ]));

//     $order = Order::firstOrFail();
//     expect($order->status)->toBe('pending')->and((float) $order->total_amount)->toBe(100.0);
// });

// // ---------------------------------------------------------------- concurrency and stock

// it('F1-70 sells the last ticket once: the second buyer is rejected and stock ends at zero', function () {
//     $this->ticketType->update(['quantity' => 1]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]]))->assertSessionHasNoErrors();
//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['buyer_email' => 'second@example.com']))
//         ->assertSessionHasErrors('error');

//     expect(Order::count())->toBe(1)->and(Ticket::count())->toBe(1)->and($this->ticketType->fresh()->quantity)->toBe(0);
// });

// it('F1-72 gives a one-use discount to only one of two buyers', function () {
//     $discount = discountFor($this, ['usage_limit' => 1, 'used_count' => 0]);

//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], ['discount_id' => $discount->id]));
//     purchase($this->event, purchasePayload([[$this->ticketType, 1]], [
//         'discount_id' => $discount->id,
//         'buyer_email' => 'second@example.com',
//     ]));

//     expect(Order::where('discount_id', $discount->id)->count())->toBe(1)
//         ->and($discount->fresh()->used_count)->toBe(1);
// });
