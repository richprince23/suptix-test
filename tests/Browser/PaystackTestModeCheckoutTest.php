<?php

use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Payment;
use App\Models\PaymentInfo;
use App\Models\TicketType;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
 * Manual-style end-to-end test against the real Paystack test environment (no
 * money moves). The application calls Paystack's real API with the test secret
 * key from .env, the browser is sent to Paystack's hosted checkout, pays with
 * Paystack's published test card and returns to the application.
 *
 * Opt in with PAYSTACK_LIVE=1 (composer run test:browser:paystack). It is
 * skipped otherwise, and always skipped unless the key is a test key.
 */

beforeEach(function () {
    if (! getenv('PAYSTACK_LIVE')) {
        $this->markTestSkipped('Set PAYSTACK_LIVE=1 to run against the Paystack test environment.');
    }

    // phpunit.xml sets a dummy key for every test, so read the real test key from .env
    $env = Dotenv::parse((string) file_get_contents(base_path('.env')));
    $key = $env['PAYSTACK_SECRET_KEY'] ?? '';

    if (! str_starts_with($key, 'sk_test_')) {
        $this->markTestSkipped('PAYSTACK_SECRET_KEY in .env is not a Paystack test key (sk_test_...).');
    }

    config(['services.paystack.secret_key' => $key, 'inertia.ssr.enabled' => false]);

    // A real subaccount of the test account is needed to start a split payment
    $list = Http::withToken($key)->get('https://api.paystack.co/subaccount', ['perPage' => 1])->json();
    $subaccount = $list['data'][0]['subaccount_code'] ?? null;

    if (! $subaccount) {
        $this->markTestSkipped('The Paystack test account has no subaccount to pay into.');
    }

    Mail::fake();
    Notification::fake();

    $this->organizer = Organizer::factory()->create();
    PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create([
        'organizer_id' => $this->organizer->id,
        'paystack_subaccount_code' => $subaccount,
        'percentage_charge' => 7,
    ]));
    $this->event = Event::factory()->create(['organizer_id' => $this->organizer->id]);
    $this->ticketType = TicketType::factory()->priced(10)->stock(20)->create([
        'event_id' => $this->event->id,
        'organizer_id' => $this->organizer->id,
        'name' => 'Regular',
    ]);
});

/**
 * Poll a JavaScript condition in the page until it is true or the time is up.
 */
function waitUntil($page, string $condition, int $seconds): bool
{
    for ($i = 0; $i < $seconds; $i++) {
        try {
            if ($page->script($condition)) {
                return true;
            }
        } catch (Throwable) {
            // the page is navigating; try again
        }
        sleep(1);
    }

    return false;
}

/** Tag the first element matching one of the candidates so it can be typed into. */
function tag($page, string $name, array $candidates): string
{
    $js = 'const c = '.json_encode($candidates).'; for (const q of c) { const e = document.querySelector(q); '
        .'if (e) { e.setAttribute("data-test", '.json_encode($name).'); return true; } } return false;';
    waitUntil($page, '(() => { '.$js.' })()', 20);

    return '[data-test="'.$name.'"]';
}

it('buys tickets and pays on the real Paystack test checkout', function () {
    $page = visit(route('events.show', $this->event));

    $page->assertSee($this->event->title)
        ->click('button:has-text("+")')
        ->wait(pause())
        ->click('button:has-text("+")')
        ->wait(pause())
        ->click('button:has-text("Continue - GHS")')
        ->wait(pause())
        ->assertSee('Buyer Information')
        ->typeSlowly('#buyer_email', 'buyer@example.com', typingDelay())
        ->click('#showInfo')
        ->wait(pause())
        ->typeSlowly('#attendee_name_0_0', 'Ama Mensah', typingDelay())
        ->typeSlowly('#attendee_name_0_1', 'Kofi Boateng', typingDelay())
        ->wait(pause())
        ->click('button:has-text("Proceed to Payment")');

    // The application has called Paystack and the browser is on Paystack's checkout
    expect(waitUntil($page, 'location.hostname.endsWith("paystack.com")', 40))->toBeTrue('The browser never reached Paystack checkout');

    // Paystack's checkout sits behind a Cloudflare human check. The test never
    // touches it: in a headed run a person ticks the box, then the test goes on.
    $challenge = 'document.body && document.body.innerText.includes("Verify you are human")';
    if ($page->script($challenge)) {
        fwrite(STDERR, "\n>>> Tick 'Verify you are human' in the browser window. The test continues by itself (waiting up to 2 minutes).\n");
        if (! waitUntil($page, '!('.$challenge.')', 120)) {
            $page->screenshot(filename: 'paystack_human_check');
            $this->markTestSkipped("Paystack's Cloudflare human check was not completed; run headed (composer run test:browser:paystack) and tick the box.");
        }
    }

    // Choose to pay by card if Paystack offers a choice of channels
    waitUntil($page, 'document.body.innerText.toLowerCase().includes("card")', 40);
    $page->script('(() => { const b = [...document.querySelectorAll("button, [role=button], li, div")].find(e => /^\s*(pay with )?card\s*$/i.test(e.innerText || "")); if (b) b.click(); return !!b; })()');
    $page->wait(pause());

    $number = tag($page, 'card-number', ['input[autocomplete="cc-number"]', 'input[name*="card" i][name*="number" i]', 'input[placeholder*="0000"]', 'input[id*="card" i]']);
    $expiry = tag($page, 'card-expiry', ['input[autocomplete="cc-exp"]', 'input[placeholder*="MM" i]', 'input[name*="expiry" i]', 'input[id*="expiry" i]']);
    $cvv = tag($page, 'card-cvv', ['input[autocomplete="cc-csc"]', 'input[placeholder="123"]', 'input[name*="cvv" i]', 'input[id*="cvv" i]']);

    // Paystack's published test card (successful payment)
    $page->typeSlowly($number, '4084 0840 8408 4081', typingDelay())
        ->typeSlowly($expiry, '12/30', typingDelay())
        ->typeSlowly($cvv, '408', typingDelay())
        ->wait(pause());
    $page->script('(() => { const b = [...document.querySelectorAll("button")].find(e => /pay\s+(ghs|gh₵|₵)/i.test(e.innerText)); if (b) b.click(); return !!b; })()');

    // Paystack may ask for a PIN or OTP for some cards; the test card above does not
    // Back in the application: payment confirmed
    expect(waitUntil($page, 'location.hostname === "127.0.0.1" || location.hostname === "localhost"', 120))->toBeTrue('Paystack did not send the browser back to the application');
    $page->assertSee('Payment Successful!')
        ->wait(pause() * 3)
        ->click('a:has-text("View Order Details")')
        ->assertSee('Ama Mensah')
        ->assertSee('Kofi Boateng')
        ->wait(pause() * 3);

    $order = Order::where('buyer_email', 'buyer@example.com')->firstOrFail();

    expect($order->status)->toBe('confirmed')
        ->and((float) $order->total_amount)->toBe(20.0)
        ->and($order->tickets->pluck('status')->unique()->all())->toBe(['active'])
        ->and(Payment::where('order_id', $order->id)->value('status'))->toBe('completed');
});
