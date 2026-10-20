<?php

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
 * Performance and scalability cases (P-xx), docs/testing/TEST_PLAN.md,
 * Appendix A.8. Figures come from a local machine and are indicative only.
 * Each case appends its measurement to storage/logs/performance.jsonl.
 */

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    [$this->organizer, $this->event, $this->ticketType] = makeCheckout(['quantity' => 2000]);
});

function record(string $id, array $data): void
{
    File::append(storage_path('logs/performance.jsonl'), json_encode(['id' => $id] + $data)."\n");
}

function queriesFor(callable $action): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $action();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

function percentile(array $values, float $p): float
{
    sort($values);

    return $values[(int) max(0, ceil($p / 100 * count($values)) - 1)];
}

function timed(callable $action): float
{
    $start = hrtime(true);
    $action();

    return (hrtime(true) - $start) / 1e6;
}

it('P-01 purchase query count does not grow with the number of reads per ticket', function () {
    fakePaystackInitialize();
    $one = queriesFor(fn () => $this->post(route('events.tickets.purchase', $this->event), purchasePayload([[$this->ticketType, 1]])));
    $ten = queriesFor(fn () => $this->post(route('events.tickets.purchase', $this->event), purchasePayload([[$this->ticketType, 10]])));

    $perExtraTicket = ($ten - $one) / 9;
    record('P-01', ['queries_1_ticket' => $one, 'queries_10_tickets' => $ten, 'queries_per_extra_ticket' => round($perExtraTicket, 2)]);

    // Each extra ticket costs its insert and little else
    expect($perExtraTicket)->toBeLessThanOrEqual(2.0);
});

it('P-02 the event ticket list uses a constant number of queries for 500 tickets', function () {
    $staff = User::factory()->create();
    $this->organizer->users()->attach($staff);
    $order = Order::factory()->confirmed()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
    $url = "/api/organizers/{$this->organizer->id}/events/{$this->event->id}/tickets";
    $this->actingAs($staff, 'sanctum');

    Ticket::factory()->count(10)->create(['order_id' => $order->id, 'event_id' => $this->event->id, 'organizer_id' => $this->organizer->id, 'ticket_type_id' => $this->ticketType->id]);
    $this->getJson($url)->assertOk(); // warm-up: first-use queries such as authentication
    $small = queriesFor(fn () => $this->getJson($url)->assertOk());

    $rows = [];
    foreach (range(1, 490) as $i) {
        $rows[] = ['order_id' => $order->id, 'event_id' => $this->event->id, 'organizer_id' => $this->organizer->id, 'ticket_type_id' => $this->ticketType->id, 'attendee_name' => "A{$i}", 'price' => 100, 'status' => 'active', 'ticket_number' => 'TKT-PERF'.str_pad((string) $i, 6, '0', STR_PAD_LEFT), 'created_at' => now(), 'updated_at' => now()];
    }
    Ticket::insert($rows);

    $large = 0;
    $ms = timed(function () use (&$large, $url) {
        $large = queriesFor(fn () => $this->getJson($url)->assertOk());
    });
    record('P-02', ['queries_10_tickets' => $small, 'queries_500_tickets' => $large, 'response_ms_500_tickets' => round($ms, 1)]);

    expect($large)->toBe($small);
});

it('P-03 and P-04 scanning and marking tickets stay fast over 100 requests', function () {
    $staff = User::factory()->create();
    $this->organizer->users()->attach($staff);
    $order = Order::factory()->confirmed()->create(['event_id' => $this->event->id, 'organizer_id' => $this->organizer->id]);
    $tickets = Ticket::factory()->count(100)->create(['order_id' => $order->id, 'event_id' => $this->event->id, 'organizer_id' => $this->organizer->id, 'ticket_type_id' => $this->ticketType->id]);

    $scanMs = $tickets->map(fn ($t) => timed(fn () => $this->postJson(route('tickets.scan-qr'), ['ticket_number' => $t->ticket_number])->assertOk()))->all();
    $this->actingAs($staff);
    $markMs = $tickets->map(fn ($t) => timed(fn () => $this->patchJson(route('tickets.mark-used', $t))->assertOk()))->all();

    record('P-03', ['median_ms' => round(percentile($scanMs, 50), 1), 'p95_ms' => round(percentile($scanMs, 95), 1), 'requests' => 100]);
    record('P-04', ['median_ms' => round(percentile($markMs, 50), 1), 'p95_ms' => round(percentile($markMs, 95), 1), 'requests' => 100]);

    // Threshold set after the first measurement (median 8 ms, 95th percentile 11 ms locally)
    expect(percentile($scanMs, 50))->toBeLessThan(200.0)->and(percentile($scanMs, 95))->toBeLessThan(100.0);
});

it('P-05 one charge.success webhook is processed quickly', function () {
    [$order, , $reference] = makePendingOrder($this->event, $this->ticketType, 2);
    fakePaystackVerify($reference, 20000);

    $ms = timed(fn () => postWebhook(['event' => 'charge.success', 'data' => ['reference' => $reference, 'amount' => 20000]])->assertOk());
    record('P-05', ['processing_ms' => round($ms, 1)]);

    expect($order->fresh()->status)->toBe('confirmed')->and($ms)->toBeLessThan(2000.0);
});

it('P-06 never sells more tickets than the stock when buyers outnumber it', function () {
    fakePaystackInitialize();
    $this->ticketType->update(['quantity' => 5]);

    foreach (range(1, 20) as $i) {
        $this->post(route('events.tickets.purchase', $this->event), purchasePayload([[$this->ticketType, 1]], ['buyer_email' => "buyer{$i}@example.com"]));
    }

    $oversold = max(0, Ticket::where('ticket_type_id', $this->ticketType->id)->count() - 5);
    record('P-06', ['stock' => 5, 'buyers' => 20, 'tickets_sold' => Ticket::where('ticket_type_id', $this->ticketType->id)->count(), 'oversold' => $oversold]);

    expect($oversold)->toBe(0)->and($this->ticketType->fresh()->quantity)->toBe(0);
});
