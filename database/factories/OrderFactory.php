<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'organizer_id' => fn (array $attributes) => Event::find($attributes['event_id'])->organizer_id,
            'buyer_email' => fake()->safeEmail(),
            'subtotal_amount' => 100.00,
            'discount_amount' => 0,
            'total_amount' => 100.00,
            'status' => 'pending',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'purchased_at' => now()]);
    }
}
