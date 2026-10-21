<?php

// use App\Models\Discount;
// use App\Models\Event;
// use App\Models\Order;
// use App\Models\Organizer;
// use App\Models\Payment;
// use App\Models\PaymentInfo;
// use App\Models\Ticket;
// use App\Models\TicketType;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Notification;
// use Illuminate\Support\Facades\Route;

// /*
//  * End-to-end tests for flows F1 (purchase and pricing) and F2 (payment
//  * confirmation) driven through a real browser. Paystack's API is faked, and
//  * the "authorization_url" it returns points at a stand-in checkout page served
//  * by this test (a card form with a Pay button) that sends the browser back to
//  * the application's own callback route. The buyer therefore goes through the
//  * whole payment step without an external page or real money.
//  */

// beforeEach(function () {
//     // The browser server runs in this process, so turn off server-side rendering,
//     // which would otherwise call a Node SSR server through the faked HTTP client.
//     config(['inertia.ssr.enabled' => false]);

//     Mail::fake();
//     Notification::fake();

//     $this->organizer = Organizer::factory()->create();
//     PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create([
//         'organizer_id' => $this->organizer->id,
//     ]));

//     $this->event = Event::factory()->create(['organizer_id' => $this->organizer->id]);
//     $this->ticketType = TicketType::factory()->priced(100)->stock(20)->create([
//         'event_id' => $this->event->id,
//         'organizer_id' => $this->organizer->id,
//         'name' => 'Regular',
//     ]);

//     $this->reference = null;

//     // Stand-in for Paystack's hosted checkout page
//     Route::get('/fake-paystack/checkout/{reference}', function (string $reference) {
//         $order = Order::where('payment_reference', $reference)->firstOrFail();
//         $amount = number_format((float) $order->total_amount, 2);
//         $callback = route('payment.callback');

//         return response(<<<HTML
//         <!doctype html>
//         <html lang="en"><head><meta charset="utf-8"><title>Paystack checkout (test mode)</title></head>
//         <body style="font-family: sans-serif; max-width: 420px; margin: 60px auto;">
//             <h1>Paystack checkout (test mode)</h1>
//             <p>Order {$order->order_number}</p>
//             <form method="get" action="{$callback}">
//                 <input type="hidden" name="reference" value="{$reference}">
//                 <p><label>Card number<br><input id="card-number" style="width:100%"></label></p>
//                 <p><label>Expiry<br><input id="card-expiry" style="width:100%"></label></p>
//                 <p><label>CVV<br><input id="card-cvv" style="width:100%"></label></p>
//                 <button type="submit" style="padding: 10px 20px;">Pay GHS {$amount}</button>
//             </form>
//         </body></html>
//         HTML);
//     });

//     Http::fake([
//         'api.paystack.co/transaction/initialize' => function ($request) {
//             $this->reference = $request['reference'];

//             return Http::response([
//                 'status' => true,
//                 'message' => 'Authorization URL created',
//                 'data' => [
//                     'authorization_url' => url('/fake-paystack/checkout/'.$request['reference']),
//                     'access_code' => 'test_access_code',
//                     'reference' => $request['reference'],
//                 ],
//             ]);
//         },
//         'api.paystack.co/transaction/verify/*' => function () {
//             $order = Order::where('payment_reference', $this->reference)->firstOrFail();

//             return Http::response([
//                 'status' => true,
//                 'message' => 'Verification successful',
//                 'data' => [
//                     'status' => 'success',
//                     'reference' => $this->reference,
//                     'amount' => (int) round($order->total_amount * 100),
//                     'currency' => 'GHS',
//                     'channel' => 'card',
//                     'paid_at' => now()->toIso8601String(),
//                     'fees' => 0,
//                     'metadata' => [
//                         'order_id' => $order->id,
//                         'order_number' => $order->order_number,
//                         'organizer_id' => $order->organizer_id,
//                         'event_id' => $order->event_id,
//                     ],
//                 ],
//             ]);
//         },
//         '*' => Http::response([], 200),
//     ]);
// });

// it('lets a guest buy two tickets, pay at the checkout and see the purchased tickets', function () {
//     $page = visit(route('events.show', $this->event));

//     $page->assertSee($this->event->title)
//         ->click('button:has-text("+")')
//         ->wait(pause())
//         ->click('button:has-text("+")')
//         ->wait(pause())
//         ->assertSee('Continue - GHS 200')
//         ->click('button:has-text("Continue - GHS")')
//         ->wait(pause())
//         ->assertSee('Buyer Information')
//         ->typeSlowly('#buyer_email', 'buyer@example.com', typingDelay())
//         ->typeSlowly('#buyer_phone', '0201234567', typingDelay())
//         ->assertAttribute('#showInfo', 'aria-checked', 'false')
//         ->click('#showInfo')
//         ->wait(pause())
//         ->assertAttribute('#showInfo', 'aria-checked', 'true')
//         ->assertVisible('#attendee_name_0_0')
//         ->typeSlowly('#attendee_name_0_0', 'Ama Mensah', typingDelay())
//         ->typeSlowly('#attendee_name_0_1', 'Kofi Boateng', typingDelay())
//         ->wait(pause())
//         ->click('button:has-text("Proceed to Payment")')
//         // payment step at the (stand-in) Paystack checkout
//         ->assertSee('Paystack checkout (test mode)')
//         ->typeSlowly('#card-number', '4084 0840 8408 4081', typingDelay())
//         ->typeSlowly('#card-expiry', '12/30', typingDelay())
//         ->typeSlowly('#card-cvv', '408', typingDelay())
//         ->wait(pause())
//         ->click('button:has-text("Pay GHS 200.00")')
//         // back in the application: payment confirmed
//         ->assertSee('Payment Successful!')
//         ->wait(pause() * 3)
//         ->click('a:has-text("View Order Details")')
//         ->assertSee('Ama Mensah')
//         ->assertSee('Kofi Boateng')
//         ->wait(pause() * 3);

//     $order = Order::where('buyer_email', 'buyer@example.com')->firstOrFail();

//     expect($order->status)->toBe('confirmed')
//         ->and((float) $order->total_amount)->toBe(200.0)
//         ->and($order->buyer_phone)->toBe('0201234567')
//         ->and($order->tickets->pluck('attendee_name')->sort()->values()->all())->toBe(['Ama Mensah', 'Kofi Boateng'])
//         ->and($order->tickets->pluck('status')->unique()->all())->toBe(['active'])
//         ->and($this->ticketType->fresh()->quantity)->toBe(18)
//         ->and(Payment::where('order_id', $order->id)->value('status'))->toBe('completed');
// });

// it('keeps the attendee fields hidden until the buyer ticks the checkbox and then uses default names', function () {
//     $page = visit(route('events.show', $this->event));

//     $page->click('button:has-text("+")')
//         ->click('button:has-text("Continue - GHS")')
//         ->assertSee('Buyer Information')
//         ->assertAttributeContains('#attendee-information', 'style', 'display: none')
//         ->click('#showInfo')
//         ->wait(pause())
//         ->assertVisible('#attendee_name_0_0')
//         ->click('#showInfo')
//         ->wait(pause())
//         ->assertAttributeContains('#attendee-information', 'style', 'display: none')
//         ->typeSlowly('#buyer_email', 'quick@example.com', typingDelay())
//         ->click('button:has-text("Proceed to Payment")')
//         ->typeSlowly('#card-number', '4084 0840 8408 4081', typingDelay())
//         ->click('button:has-text("Pay GHS 100.00")')
//         ->assertSee('Payment Successful!')
//         ->wait(pause() * 2);

//     $order = Order::where('buyer_email', 'quick@example.com')->firstOrFail();
//     expect($order->status)->toBe('confirmed')->and($order->tickets->pluck('attendee_name')->all())->toBe(['Guest']);
// });

// it('applies a percentage discount code in the browser before checkout', function () {
//     Discount::factory()->percentage(10)->create([
//         'organizer_id' => $this->organizer->id,
//         'event_id' => $this->event->id,
//         'code' => 'SAVE10',
//         'name' => 'Ten percent',
//     ]);

//     visit(route('events.show', $this->event))
//         ->click('button:has-text("+")')
//         ->wait(pause())
//         ->click('button:has-text("+")')
//         ->wait(pause())
//         ->typeSlowly('input[placeholder="Discount code (optional)"]', 'SAVE10', typingDelay())
//         ->click('button:has-text("Apply")')
//         ->wait(pause())
//         ->assertSee('Ten percent discount applied')
//         ->assertSee('Continue - GHS 180');
// });

// it('rejects an unknown discount code and keeps the full price', function () {
//     visit(route('events.show', $this->event))
//         ->click('button:has-text("+")')
//         ->wait(pause())
//         ->typeSlowly('input[placeholder="Discount code (optional)"]', 'NOSUCHCODE', typingDelay())
//         ->click('button:has-text("Apply")')
//         ->wait(pause())
//         ->assertDontSee('discount applied')
//         ->assertSee('Continue - GHS 100');
// });

// it('does not create an order until the buyer submits the form', function () {
//     visit(route('events.show', $this->event))
//         ->click('button:has-text("+")')
//         ->wait(pause())
//         ->click('button:has-text("Continue - GHS")')
//         ->wait(pause())
//         ->assertSee('Buyer Information');

//     expect(Order::count())->toBe(0)
//         ->and(Ticket::count())->toBe(0)
//         ->and($this->ticketType->fresh()->quantity)->toBe(20);
// });
