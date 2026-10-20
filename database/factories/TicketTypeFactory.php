<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'organizer_id' => fn (array $attributes) => Event::find($attributes['event_id'])->organizer_id,
            'name' => fake()->randomElement(['Regular', 'VIP', 'Early Bird']),
            'price' => 100.00,
            'quantity' => 50,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => ['price' => 0]);
    }

    public function priced(float $price): static
    {
        return $this->state(fn () => ['price' => $price]);
    }

    public function stock(int $quantity): static
    {
        return $this->state(fn () => ['quantity' => $quantity]);
    }

    public function salesNotStarted(): static
    {
        return $this->state(fn () => ['sales_start' => now()->addDay()]);
    }

    public function salesEnded(): static
    {
        return $this->state(fn () => ['sales_end' => now()->subDay()]);
    }
}
