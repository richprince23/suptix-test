<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(DatabaseTransactions::class)
    ->in('Feature', 'Unit', 'Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Shared helpers for the ticketing flow tests
|--------------------------------------------------------------------------
*/

use App\Models\Event;
use App\Models\Organizer;
use App\Models\PaymentInfo;
use App\Models\TicketType;
use Illuminate\Support\Facades\Http;

/**
 * An organiser with approved payment info, a published event and one ticket
 * type (GHS 100, 50 in stock). Returns [organizer, event, ticketType].
 */
function makeCheckout(array $ticketAttributes = [], array $eventAttributes = [], float $percentageCharge = 10.0): array
{
    $organizer = Organizer::factory()->create();

    PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create([
        'organizer_id' => $organizer->id,
        'percentage_charge' => $percentageCharge,
    ]));

    $event = Event::factory()->create(array_merge(['organizer_id' => $organizer->id], $eventAttributes));
    $ticketType = TicketType::factory()->create(array_merge([
        'event_id' => $event->id,
        'organizer_id' => $organizer->id,
        'price' => 100,
        'quantity' => 50,
    ], $ticketAttributes));

    return [$organizer, $event, $ticketType];
}

/** Request body for the purchase endpoint with one line per [ticketType, quantity] pair. */
function purchasePayload(array $lines, array $extra = []): array
{
    $tickets = [];
    foreach ($lines as [$ticketType, $quantity]) {
        $tickets[] = [
            'ticket_type_id' => $ticketType->id,
            'quantity' => $quantity,
            'attendees' => array_map(fn ($i) => ['name' => "Attendee {$i}"], range(1, max($quantity, 1))),
        ];
    }

    return array_merge(['buyer_email' => 'buyer@example.com', 'tickets' => $tickets], $extra);
}

/** Fake Paystack initialisation; the reference sent by the app is echoed back. */
function fakePaystackInitialize(): void
{
    Http::fake([
        'api.paystack.co/transaction/initialize' => function ($request) {
            return Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.test/'.$request['reference'],
                    'access_code' => 'test_access_code',
                    'reference' => $request['reference'],
                ],
            ]);
        },
    ]);
}

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Client\Factory;

/**
 * A pending order with pending tickets and a pending payment that carry the
 * same Paystack reference. Returns [order, payment, reference].
 */
function makePendingOrder(Event $event, TicketType $ticketType, int $quantity = 1, ?float $total = null): array
{
    $reference = 'SUP_TEST_'.bin2hex(random_bytes(6));
    $total ??= (float) $ticketType->price * $quantity;

    $order = Order::factory()->create([
        'event_id' => $event->id,
        'organizer_id' => $event->organizer_id,
        'subtotal_amount' => $total,
        'total_amount' => $total,
        'status' => 'pending',
        'payment_reference' => $reference,
        'payment_method' => 'paystack',
    ]);

    Ticket::factory()->count($quantity)->pending()->create([
        'order_id' => $order->id,
        'event_id' => $event->id,
        'organizer_id' => $event->organizer_id,
        'ticket_type_id' => $ticketType->id,
        'price' => $ticketType->price,
    ]);

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'organizer_id' => $event->organizer_id,
        'amount' => $total,
        'transaction_id' => $reference,
        'status' => 'pending',
    ]);

    return [$order, $payment, $reference];
}

/** Fake Paystack's verify endpoint. Amount is in pesewas, as Paystack sends it. */
function fakePaystackVerify(string $reference, int $amountPesewas, string $status = 'success', array $extra = []): void
{
    // Start from a clean HTTP fake: stubs registered earlier would otherwise win
    Http::swap(new Factory);

    Http::fake([
        'api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => array_merge([
                'status' => $status,
                'reference' => $reference,
                'amount' => $amountPesewas,
                'currency' => 'GHS',
                'channel' => 'card',
                'metadata' => [],
            ], $extra),
        ]),
    ]);
}

/** POST a webhook body signed with the test secret key (or a supplied signature). */
function postWebhook(array $payload, ?string $signature = null, bool $withHeader = true)
{
    $body = json_encode($payload);
    $signature ??= hash_hmac('sha512', $body, config('services.paystack.secret_key'));

    $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
    if ($withHeader) {
        $server['HTTP_X_PAYSTACK_SIGNATURE'] = $signature;
    }

    return test()->call('POST', route('paystack.webhook'), [], [], [], $server, $body);
}

/*
 * Browser tests normally run headless and fast. Set BROWSER_DEMO=1 and add
 * --headed to watch them: the browser opens, types keystroke by keystroke and
 * pauses between steps, like a Selenium demo.
 */
function typingDelay(): int
{
    return env('BROWSER_DEMO') ? 70 : 0;
}

function pause(): float
{
    return env('BROWSER_DEMO') ? 1.0 : 0.0;
}
