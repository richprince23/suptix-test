<?php

namespace Database\Factories;

use App\Models\Organizer;
use App\Models\PaymentInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentInfo>
 */
class PaymentInfoFactory extends Factory
{
    protected $model = PaymentInfo::class;

    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'paystack_subaccount_code' => 'ACCT_'.fake()->unique()->bothify('????????'),
            'business_name' => fake()->company(),
            'settlement_bank' => 'GCB Bank Limited',
            'bank_code' => '040100',
            'account_number' => fake()->numerify('##########'),
            'account_name' => fake()->name(),
            'payment_channel' => 'bank',
            'percentage_charge' => 10.0,
            'transaction_fee_payer' => 'account',
            'is_verified' => true,
            'is_active' => true,
            'status' => 'approved',
        ];
    }
}
