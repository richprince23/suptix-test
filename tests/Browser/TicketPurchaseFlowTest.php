<?php

use App\Models\Discount;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Payment;
use App\Models\PaymentInfo;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
 * End-to-end tests for flows F1 (purchase and pricing) and F2 (payment
 * confirmation) driven through a real browser. Paystack is faked: the
 * "authorization_url" it returns points back at this application's own
 * callback route, so no external page or real money is involved.
 */

beforeEach(function () {
    // The browser server runs in this process, so turn off server-side rendering,
    // which would otherwise call a Node SSR server through the faked HTTP client.
    config(['inertia.ssr.enabled' => false]);

    Mail::fake();
    Notification::fake();

    $this->organizer = Organizer::factory()->create();
    PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create([
        'organizer_id' => $this->organizer->id,
    ]));

    $this->event = Event::factory()->create(['organizer_id' => $this->organizer->id]);
    $this->ticketType = TicketType::factory()->priced(100)->stock(20)->create([
        'event_id' => $this->event->id,
        'organizer_id' => $this->organizer->id,
        'name' => 'Regular',
    ]);

    $this->reference = null;

    Http::fake([
        'api.paystack.co/transaction/initialize' => function ($request) {
            $this->reference = $request['reference'];

            return Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => route('payment.callback', ['reference' => $request['reference']]),
                    'access_code' => 'test_access_code',
                    'reference' => $request['reference'],
                ],
            ]);
        },
        'api.paystack.co/transaction/verify/*' => function () {
            $order = Order::where('payment_reference', $this->reference)->firstOrFail();

            return Http::response([
                'status' => true,
                'message' => 'Verification successful',
                'data' => [
                    'status' => 'success',
                    'reference' => $this->reference,
                    'amount' => (int) round($order->total_amount * 100),
                    'currency' => 'GHS',
                    'channel' => 'card',
                    'paid_at' => now()->toIso8601String(),
                    'fees' => 0,
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'organizer_id' => $order->organizer_id,
                        'event_id' => $order->event_id,
                    ],
                ],
            ]);
        },
        '*' => Http::response([], 200),
    ]);
});

it('lets a guest buy two tickets and confirms the order after payment', function () {
    $page = visit(route('events.show', $this->event));

    $page->assertSee($this->event->title)
        ->click('button:has-text("+")')
        ->click('button:has-text("+")')
        ->assertSee('Continue - GHS 200')
        ->click('button:has-text("Continue - GHS")')
        ->assertSee('Buyer Information')
        ->type('#buyer_email', 'buyer@example.com')
        ->type('#attendee_name_0_0', 'Ama Mensah')
        ->type('#attendee_name_0_1', 'Kofi Boateng')
        ->click('button:has-text("Proceed to Payment")')
        ->assertSee('Payment Successful!');

    $order = Order::where('buyer_email', 'buyer@example.com')->firstOrFail();

    expect($order->status)->toBe('confirmed')
        ->and((float) $order->total_amount)->toBe(200.0)
        ->and($order->tickets)->toHaveCount(2)
        ->and($order->tickets->pluck('status')->unique()->all())->toBe(['active'])
        ->and($this->ticketType->fresh()->quantity)->toBe(18)
        ->and(Payment::where('order_id', $order->id)->value('status'))->toBe('completed');
});

it('applies a percentage discount code in the browser before checkout', function () {
    Discount::factory()->percentage(10)->create([
        'organizer_id' => $this->organizer->id,
        'event_id' => $this->event->id,
        'code' => 'SAVE10',
        'name' => 'Ten percent',
    ]);

    visit(route('events.show', $this->event))
        ->click('button:has-text("+")')
        ->click('button:has-text("+")')
        ->type('input[placeholder="Discount code (optional)"]', 'SAVE10')
        ->click('button:has-text("Apply")')
        ->assertSee('Ten percent discount applied')
        ->assertSee('Continue - GHS 180');
});

it('rejects an unknown discount code and keeps the full price', function () {
    visit(route('events.show', $this->event))
        ->click('button:has-text("+")')
        ->type('input[placeholder="Discount code (optional)"]', 'NOSUCHCODE')
        ->click('button:has-text("Apply")')
        ->assertDontSee('discount applied')
        ->assertSee('Continue - GHS 100');
});

it('does not create an order until the buyer submits the form', function () {
    visit(route('events.show', $this->event))
        ->click('button:has-text("+")')
        ->click('button:has-text("Continue - GHS")')
        ->assertSee('Buyer Information');

    expect(Order::count())->toBe(0)
        ->and(Ticket::count())->toBe(0)
        ->and($this->ticketType->fresh()->quantity)->toBe(20);
});
