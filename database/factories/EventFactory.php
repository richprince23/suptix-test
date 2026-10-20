<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'mode' => 'in-person',
            'venue_name' => fake()->company(),
            'venue_address' => fake()->address(),
            'start_date' => now()->addMonth()->toDateString(),
            'start_time' => '18:00:00',
            'end_date' => now()->addMonth()->toDateString(),
            'end_time' => '22:00:00',
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subMonth()->toDateString(),
        ]);
    }
}
