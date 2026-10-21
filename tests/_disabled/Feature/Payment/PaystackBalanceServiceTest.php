<?php

// use App\Models\PaymentInfo;
// use App\Services\PaystackBalanceService;
// use Illuminate\Support\Facades\Http;

// /*
//  * Flow F2: balance synchronisation and single-transaction processing. Case
//  * identifiers (F2-8x) refer to docs/testing/TEST_PLAN.md, Appendix A.2.5.
//  */

// function balanceInfo(float $percentage = 10.0, array $attributes = []): PaymentInfo
// {
//     return PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(array_merge([
//         'percentage_charge' => $percentage,
//     ], $attributes)));
// }

// function paystackTransaction(string $reference, int $pesewas, array $extra = []): array
// {
//     return array_merge([
//         'reference' => $reference,
//         'amount' => $pesewas,
//         'status' => 'success',
//         'created_at' => '2026-10-01T10:00:00.000Z',
//     ], $extra);
// }

// it('F2-80 syncs balances from the transaction list using the verified split data', function () {
//     $info = balanceInfo();
//     Http::fake([
//         'api.paystack.co/transaction/verify/REF1' => Http::response(['status' => true, 'data' => paystackTransaction('REF1', 10000, ['fees_split' => ['subaccount' => 9000, 'integration' => 1000]])]),
//         'api.paystack.co/transaction/verify/REF2' => Http::response(['status' => true, 'data' => paystackTransaction('REF2', 20000, ['created_at' => '2026-10-02T10:00:00.000Z'])]),
//         'api.paystack.co/transaction*' => Http::response(['status' => true, 'data' => [
//             paystackTransaction('REF1', 10000),
//             paystackTransaction('REF2', 20000),
//         ]]),
//     ]);

//     $result = app(PaystackBalanceService::class)->syncSubaccountBalances($info);

//     $info->refresh();
//     expect($result['total_transaction_volume'])->toBe(300.0)
//         ->and((float) $info->total_transaction_volume)->toBe(300.0)
//         ->and((float) $info->subaccount_balance)->toBe(270.0)
//         ->and((float) $info->system_admin_balance)->toBe(30.0)
//         ->and($info->total_transactions)->toBe(2)
//         ->and($result['split_data_coverage'])->toBe(50.0)
//         ->and($result['last_transaction']['reference'])->toBe('REF2');
// });

// it('F2-81 refuses to sync a payment info without a subaccount code', function () {
//     $info = balanceInfo(10.0, ['paystack_subaccount_code' => null]);

//     expect(fn () => app(PaystackBalanceService::class)->syncSubaccountBalances($info))
//         ->toThrow(Exception::class, 'Subaccount code not found');
// });

// it('F2-82 fails clearly when the transaction list cannot be fetched', function () {
//     $info = balanceInfo();
//     Http::fake(['api.paystack.co/transaction*' => Http::response(['status' => false, 'message' => 'Invalid key'], 401)]);

//     expect(fn () => app(PaystackBalanceService::class)->syncSubaccountBalances($info))
//         ->toThrow(Exception::class, 'Failed to fetch transactions');
//     expect((float) $info->fresh()->total_transaction_volume)->toBe(0.0);
// });

// it('F2-82 fails clearly when Paystack answers with status false', function () {
//     $info = balanceInfo();
//     Http::fake(['api.paystack.co/transaction*' => Http::response(['status' => false, 'message' => 'Rate limited'])]);

//     expect(fn () => app(PaystackBalanceService::class)->syncSubaccountBalances($info))
//         ->toThrow(Exception::class, 'Rate limited');
// });

// it('F2-83 falls back to the basic transaction data when verification fails', function () {
//     $info = balanceInfo(10.0);
//     Http::fake([
//         'api.paystack.co/transaction/verify/*' => Http::response(['status' => false], 404),
//         'api.paystack.co/transaction*' => Http::response(['status' => true, 'data' => [paystackTransaction('REFX', 5000)]]),
//     ]);

//     app(PaystackBalanceService::class)->syncSubaccountBalances($info);

//     $info->refresh();
//     expect((float) $info->subaccount_balance)->toBe(45.0)->and((float) $info->system_admin_balance)->toBe(5.0);
// });

// it('F2-84 reads the available and ledger balance of a subaccount', function () {
//     Http::fake(['api.paystack.co/subaccount/*' => Http::response(['status' => true, 'data' => ['available_balance' => 150050, 'ledger_balance' => 200000]])]);

//     $balances = app(PaystackBalanceService::class)->getSubaccountBalance('ACCT_x');

//     expect($balances)->toBe(['available_balance' => 1500.5, 'ledger_balance' => 2000.0]);
// });

// it('F2-84 throws when the subaccount cannot be read', function () {
//     Http::fake(['api.paystack.co/subaccount/*' => Http::response(['status' => false, 'message' => 'Not found'], 404)]);

//     expect(fn () => app(PaystackBalanceService::class)->getSubaccountBalance('ACCT_x'))
//         ->toThrow(Exception::class, 'Failed to fetch subaccount details');
// });

// it('F2-85 syncAll reports a success and a failure separately', function () {
//     $good = balanceInfo();
//     $bad = balanceInfo();
//     Http::fake(function ($request) use ($bad) {
//         if (str_contains($request->url(), $bad->paystack_subaccount_code)) {
//             return Http::response(['status' => false, 'message' => 'Denied'], 403);
//         }

//         return Http::response(['status' => true, 'data' => []]);
//     });

//     $results = collect(app(PaystackBalanceService::class)->syncAllSubaccountBalances())->keyBy('payment_info_id');

//     expect($results[$good->id]['success'])->toBeTrue()->and($results[$bad->id]['success'])->toBeFalse();
// });

// it('F2-86 processes one transaction that carries its split data', function () {
//     $info = balanceInfo();

//     $changes = app(PaystackBalanceService::class)->processTransactionForBalance($info, paystackTransaction('R1', 20000, ['fees_split' => ['subaccount' => 17500, 'integration' => 2500]]));

//     $info->refresh();
//     expect($changes['has_fees_split_data'])->toBeTrue()
//         ->and((float) $info->subaccount_balance)->toBe(175.0)
//         ->and((float) $info->system_admin_balance)->toBe(25.0)
//         ->and($info->total_transactions)->toBe(1);
// });

// it('F2-86 looks the transaction up by reference when only the reference is known', function () {
//     $info = balanceInfo(5.0);
//     Http::fake(['api.paystack.co/transaction/verify/R2' => Http::response(['status' => true, 'data' => paystackTransaction('R2', 20000)])]);

//     app(PaystackBalanceService::class)->processTransactionForBalance($info, ['reference' => 'R2']);

//     $info->refresh();
//     expect((float) $info->system_admin_balance)->toBe(10.0)->and((float) $info->subaccount_balance)->toBe(190.0);
// });

// it('F2-86 throws when the transaction cannot be found', function () {
//     $info = balanceInfo();
//     Http::fake(['api.paystack.co/transaction/verify/*' => Http::response(['status' => false], 404)]);

//     expect(fn () => app(PaystackBalanceService::class)->processTransactionForBalance($info, ['reference' => 'GONE']))
//         ->toThrow(Exception::class, 'Could not get transaction verification data');
// });

// it('F2-87 the single-transaction split adds up to the amount', function (int $pesewas, float $percentage) {
//     $info = balanceInfo($percentage);
//     Http::fake(['api.paystack.co/transaction/verify/R3' => Http::response(['status' => true, 'data' => paystackTransaction('R3', $pesewas)])]);

//     $changes = app(PaystackBalanceService::class)->processTransactionForBalance($info, ['reference' => 'R3']);

//     expect(round($changes['subaccount_amount'] + $changes['system_admin_amount'], 2))->toBe(round($pesewas / 100, 2));
// })->with([
//     'F2-87 250.50 at 7%' => [25050, 7.0],
//     'F2-87 33.33 at 3.5%' => [3333, 3.5],
//     'F2-87 0.15 at 10%' => [15, 10.0],
// ]);

// it('F2-88 summarises the balances of all approved subaccounts', function () {
//     PaymentInfo::query()->update(['is_active' => false]);
//     balanceInfo(10.0, ['subaccount_balance' => 90, 'system_admin_balance' => 10, 'total_transaction_volume' => 100, 'total_transactions' => 2]);
//     balanceInfo(10.0, ['subaccount_balance' => 270, 'system_admin_balance' => 30, 'total_transaction_volume' => 300, 'total_transactions' => 2]);

//     $summary = app(PaystackBalanceService::class)->getSystemBalanceSummary();

//     expect($summary['total_subaccount_balance'])->toBe(360.0)
//         ->and($summary['total_system_admin_balance'])->toBe(40.0)
//         ->and($summary['total_transaction_volume'])->toBe(400.0)
//         ->and($summary['total_balance'])->toBe(400.0)
//         ->and($summary['total_transactions'])->toBe(4)
//         ->and($summary['active_subaccounts'])->toBe(2)
//         ->and($summary['average_transaction_value'])->toBe(100.0)
//         ->and($summary['system_admin_percentage'])->toBe(10.0);
// });
