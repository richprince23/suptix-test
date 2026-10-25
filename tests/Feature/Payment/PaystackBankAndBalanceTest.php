<?php

use App\Models\PaymentInfo;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/*
 * Flow F2: bank list, account verification and balance sync endpoints.
 * Case identifiers (F2-7x) refer to docs/testing/TEST_PLAN.md, Appendix A.2.6.
 */

beforeEach(function () {
    [$this->organizer, $this->event] = makeCheckout();
    $this->staff = User::factory()->create();
    $this->organizer->users()->attach($this->staff);
});

it('F2-70 verifies a valid account number and returns the account name', function () {
    Http::fake(['api.paystack.co/bank/resolve*' => Http::response([
        'status' => true,
        'data' => ['account_name' => 'KWAME MENSAH', 'account_number' => '0123456789', 'bank_id' => 9],
    ])]);

    $this->actingAs($this->staff)
        ->postJson(route('paystack.verify-account'), ['account_number' => '0123456789', 'bank_code' => '040100'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('account_name', 'KWAME MENSAH');
});

it('F2-71 rejects an account number that is too short or too long', function (string $number) {
    $this->actingAs($this->staff)
        ->postJson(route('paystack.verify-account'), ['account_number' => $number, 'bank_code' => '040100'])
        ->assertStatus(422);
})->with(['F2-71 three digits' => '123', 'F2-71 eighteen digits' => '123456789012345678']);

it('F2-72 returns 400 when Paystack cannot resolve the account', function () {
    Http::fake(['api.paystack.co/bank/resolve*' => Http::response(['status' => false, 'message' => 'Could not resolve account name'], 422)]);

    $this->actingAs($this->staff)
        ->postJson(route('paystack.verify-account'), ['account_number' => '0123456789', 'bank_code' => '040100'])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Could not resolve account name');
});

it('F2-73 lists banks with code, name and slug', function () {
    Http::fake(['api.paystack.co/bank*' => Http::response([
        'status' => true,
        'data' => [['code' => '040100', 'name' => 'GCB Bank Limited', 'slug' => 'gcb-bank-limited']],
    ])]);

    $this->actingAs($this->staff)
        ->getJson(route('paystack.banks'))
        ->assertOk()
        ->assertJsonPath('banks.0.code', '040100')
        ->assertJsonPath('banks.0.name', 'GCB Bank Limited');
});

it('F2-73 returns 400 when the bank list cannot be fetched', function () {
    Http::fake(['api.paystack.co/bank*' => Http::response(['status' => false, 'message' => 'Service unavailable'], 503)]);

    $this->actingAs($this->staff)->getJson(route('paystack.banks'))->assertStatus(400);
});

it('F2-74 bank endpoints require sign-in', function () {
    $this->getJson(route('paystack.banks'))->assertStatus(401);
});

it('F2-75 an organiser user can sync the balance of their own subaccount', function () {
    $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    Http::fake(['api.paystack.co/balance*' => Http::response([
        'status' => true,
        'data' => [['currency' => 'GHS', 'balance' => 123456]],
    ])]);

    $this->actingAs($this->staff)
        ->postJson(route('paystack.sync-balance', $paymentInfo))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect((float) $paymentInfo->fresh()->available_balance)->toBe(1234.56);
});

it('F2-76 a user of another organiser cannot sync this subaccount', function () {
    $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    $outsider = User::factory()->create();
    makeCheckout()[0]->users()->attach($outsider);
    Http::fake();

    $this->actingAs($outsider)->postJson(route('paystack.sync-balance', $paymentInfo))->assertStatus(403);

    Http::assertNothingSent();
});

it('F2-77 a failed balance sync answers 400', function () {
    $paymentInfo = PaymentInfo::where('organizer_id', $this->organizer->id)->firstOrFail();
    Http::fake(['api.paystack.co/balance*' => Http::response(['status' => false, 'message' => 'Not found'], 404)]);

    $this->actingAs($this->staff)
        ->postJson(route('paystack.sync-balance', $paymentInfo))
        ->assertStatus(400)
        ->assertJsonPath('success', false);
});
