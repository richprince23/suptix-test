<?php

use App\Services\PaystackService;

/*
 * Flow F2: webhook signature check. Case identifiers (F2-5x) refer to
 * docs/testing/TEST_PLAN.md, Appendix A.2.4.
 */

function sign(string $payload, ?string $secret = null): string
{
    return hash_hmac('sha512', $payload, $secret ?? config('services.paystack.secret_key'));
}

it('F2-50 accepts the correct HMAC-SHA512 of the payload', function () {
    $payload = '{"event":"charge.success","data":{"reference":"R1"}}';

    expect(app(PaystackService::class)->verifyWebhookSignature($payload, sign($payload)))->toBeTrue();
});

it('F2-51 rejects a payload that differs by one byte', function () {
    $payload = '{"event":"charge.success","data":{"reference":"R1"}}';
    $tampered = '{"event":"charge.success","data":{"reference":"R2"}}';

    expect(app(PaystackService::class)->verifyWebhookSignature($tampered, sign($payload)))->toBeFalse();
});

it('F2-51 rejects a signature made with a different secret', function () {
    $payload = '{"event":"charge.success"}';

    expect(app(PaystackService::class)->verifyWebhookSignature($payload, sign($payload, 'other-secret')))->toBeFalse();
});

it('F2-53 accepts an empty payload with a matching signature', function () {
    expect(app(PaystackService::class)->verifyWebhookSignature('', sign('')))->toBeTrue();
});
