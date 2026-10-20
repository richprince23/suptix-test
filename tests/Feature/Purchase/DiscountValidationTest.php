<?php

use App\Models\Discount;

/*
 * Flow F1: discount validation endpoint. Case identifiers (F1-6x) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.1.4.
 */

beforeEach(function () {
    [$this->organizer, $this->event] = makeCheckout();
});

function validateCode($test, string $code, float|int|string $subtotal = 100, $eventId = null)
{
    return $test->postJson(route('discounts.validate'), [
        'code' => $code,
        'event_id' => $eventId ?? $test->event->id,
        'subtotal' => $subtotal,
    ]);
}

function codeFor($test, array $attributes = []): Discount
{
    return Discount::factory()->create(array_merge([
        'organizer_id' => $test->organizer->id,
        'event_id' => $test->event->id,
        'code' => 'SAVE10',
    ], $attributes));
}

it('F1-60 accepts a valid code and returns the amount and the new total', function () {
    codeFor($this, ['type' => 'percentage', 'value' => 10]);

    validateCode($this, 'SAVE10')
        ->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('discount.amount', 10)
        ->assertJsonPath('discount.total', 90);
});

it('F1-61 rejects an unknown code with 422', function () {
    validateCode($this, 'NOSUCHCODE')
        ->assertStatus(422)
        ->assertJsonPath('valid', false)
        ->assertJsonPath('message', 'Invalid discount code');
});

it('F1-61 rejects an inactive code as unknown', function () {
    codeFor($this, ['is_active' => false]);

    validateCode($this, 'SAVE10')->assertStatus(422)->assertJsonPath('message', 'Invalid discount code');
});

it('F1-62 rejects a code that belongs to another event', function () {
    [, $otherEvent] = makeCheckout();
    codeFor($this, ['event_id' => $otherEvent->id]);

    validateCode($this, 'SAVE10')->assertStatus(422)->assertJsonPath('valid', false);
});

it('F1-63 rejects a code that is not yet active', function () {
    codeFor($this, ['valid_from' => now()->addDay(), 'valid_until' => now()->addMonth()]);

    validateCode($this, 'SAVE10')->assertStatus(422)->assertJsonPath('message', 'This discount code is not yet active');
});

it('F1-64 rejects an expired code', function () {
    codeFor($this, ['valid_from' => now()->subMonth(), 'valid_until' => now()->subDay()]);

    validateCode($this, 'SAVE10')->assertStatus(422)->assertJsonPath('message', 'This discount code has expired');
});

it('F1-64 accepts a code on its last valid day', function () {
    codeFor($this, ['valid_from' => now()->subMonth(), 'valid_until' => now()->toDateString()]);

    validateCode($this, 'SAVE10')->assertOk()->assertJsonPath('valid', true);
});

it('F1-65 rejects a code that has reached its usage limit', function () {
    codeFor($this, ['usage_limit' => 3, 'used_count' => 3]);

    validateCode($this, 'SAVE10')->assertStatus(422)
        ->assertJsonPath('message', 'This discount code has reached its usage limit');
});

it('F1-66 rejects a negative subtotal', function () {
    codeFor($this);

    validateCode($this, 'SAVE10', -1)->assertStatus(422);
});

it('F1-67 calculates the discount amount and total for each type and boundary', function (string $type, float $value, float $subtotal, float $amount, float $total) {
    codeFor($this, ['type' => $type, 'value' => $value]);

    $response = validateCode($this, 'SAVE10', $subtotal)->assertOk();

    expect((float) $response->json('discount.amount'))->toBe($amount)
        ->and((float) $response->json('discount.total'))->toBe($total);
})->with([
    'percentage 10% of 100' => ['percentage', 10, 100, 10.0, 90.0],
    'percentage 0%' => ['percentage', 0, 100, 0.0, 100.0],
    'percentage 100%' => ['percentage', 100, 100, 100.0, 0.0],
    'percentage 50% of 33.33' => ['percentage', 50, 33.33, 16.665, 16.665],
    'fixed below subtotal' => ['fixed_amount', 30, 100, 30.0, 70.0],
    'fixed equal to subtotal' => ['fixed_amount', 100, 100, 100.0, 0.0],
    'fixed above subtotal' => ['fixed_amount', 500, 100, 100.0, 0.0],
    'zero subtotal' => ['percentage', 10, 0, 0.0, 0.0],
]);

it('F1-44 caps a percentage above 100 at the subtotal in the validation response', function () {
    codeFor($this, ['type' => 'percentage', 'value' => 150]);

    $response = validateCode($this, 'SAVE10', 100)->assertOk();

    expect((float) $response->json('discount.amount'))->toBe(100.0)
        ->and((float) $response->json('discount.total'))->toBe(0.0);
});
