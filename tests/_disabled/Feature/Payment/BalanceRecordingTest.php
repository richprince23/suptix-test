<?php

// use App\Models\PaymentInfo;

// /*
//  * Flow F2: balance recording after a confirmed payment. Case identifiers
//  * (F2-6x) refer to docs/testing/TEST_PLAN.md, Appendix A.2.5.
//  */

// function paymentInfoWithCharge(float $percentage): PaymentInfo
// {
//     return PaymentInfo::withoutEvents(fn () => PaymentInfo::factory()->create(['percentage_charge' => $percentage]));
// }

// function record(PaymentInfo $info, float $amount, array $extra = [], string $status = 'success'): void
// {
//     $info->refresh()->recordTransaction(array_merge([
//         'amount' => $amount,
//         'reference' => 'REF_'.bin2hex(random_bytes(4)),
//         'status' => $status,
//     ], $extra));
// }

// it('F2-60 takes the split from Paystack fees_split, converted from pesewas', function () {
//     $info = paymentInfoWithCharge(10);

//     record($info, 200.0, ['fees_split' => ['subaccount' => 17500, 'integration' => 2500]]);

//     $info->refresh();
//     expect((float) $info->subaccount_balance)->toBe(175.0)->and((float) $info->system_admin_balance)->toBe(25.0);
// });

// it('F2-61 without fees_split the percentage charge decides the split: 5% of 200 gives 10 and 190', function () {
//     $info = paymentInfoWithCharge(5);

//     record($info, 200.0);

//     $info->refresh();
//     expect((float) $info->system_admin_balance)->toBe(10.0)->and((float) $info->subaccount_balance)->toBe(190.0);
// });

// it('F2-62 the two parts add up to the transaction amount', function (float $amount) {
//     $info = paymentInfoWithCharge(7);

//     record($info, $amount);

//     $info->refresh();
//     expect(round((float) $info->subaccount_balance + (float) $info->system_admin_balance, 2))->toBe(round($amount, 2));
// })->with([10.0, 33.33, 99.99, 250.5, 1000.0]);

// it('F2-63 balances and counters accumulate over two transactions', function () {
//     $info = paymentInfoWithCharge(10);

//     record($info, 100.0);
//     record($info, 50.0);

//     $info->refresh();
//     expect((float) $info->total_transaction_volume)->toBe(150.0)
//         ->and((float) $info->subaccount_balance)->toBe(135.0)
//         ->and((float) $info->system_admin_balance)->toBe(15.0)
//         ->and($info->total_transactions)->toBe(2)
//         ->and($info->successful_transactions)->toBe(2);
// });

// it('F2-64 handles the boundary charges 0% and 100%', function (float $percentage, float $subaccount, float $system) {
//     $info = paymentInfoWithCharge($percentage);

//     record($info, 100.0);

//     $info->refresh();
//     expect((float) $info->subaccount_balance)->toBe($subaccount)->and((float) $info->system_admin_balance)->toBe($system);
// })->with([
//     'F2-64 0%' => [0.0, 100.0, 0.0],
//     'F2-64 100%' => [100.0, 0.0, 100.0],
// ]);

// it('F2-65 a pending transaction raises the pending counter, not the successful one', function () {
//     $info = paymentInfoWithCharge(10);

//     record($info, 100.0, [], 'pending');

//     $info->refresh();
//     expect($info->pending_transactions)->toBe(1)->and($info->successful_transactions)->toBe(0);
// });

// it('F2-66 the recorded parts still add up to the volume after 100 small transactions', function () {
//     $info = paymentInfoWithCharge(10);

//     foreach (range(1, 100) as $i) {
//         record($info, 0.15);
//     }

//     $info->refresh();
//     $parts = (float) $info->subaccount_balance + (float) $info->system_admin_balance;
//     expect(abs($parts - (float) $info->total_transaction_volume))->toBeLessThan(0.01);
// });
