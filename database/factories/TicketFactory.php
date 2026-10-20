<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'event_id' => fn (array $attributes) => Order::find($attributes['order_id'])->event_id,
            'organizer_id' => fn (array $attributes) => Order::find($attributes['order_id'])->organizer_id,
            'ticket_type_id' => fn (array $attributes) => TicketType::factory()->create([
                'event_id' => $attributes['event_id'],
            ])->id,
            'attendee_name' => fake()->name(),
            'attendee_email' => fake()->safeEmail(),
            'price' => 100.00,
            'status' => 'active',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function used(): static
    {
        return $this->state(fn () => ['status' => 'used', 'used_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled', 'cancelled_at' => now()]);
    }
}
