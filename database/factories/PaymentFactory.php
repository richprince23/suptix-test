<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'organizer_id' => fn (array $attributes) => Order::find($attributes['order_id'])->organizer_id,
            'amount' => 100.00,
            'payment_method' => 'paystack',
            'status' => 'pending',
        ];
    }
}
