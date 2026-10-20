<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'code' => strtoupper(fake()->unique()->bothify('SAVE-####')),
            'name' => 'Test discount',
            'type' => 'percentage',
            'value' => 10,
            'used_count' => 0,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ];
    }

    public function percentage(float $value): static
    {
        return $this->state(fn () => ['type' => 'percentage', 'value' => $value]);
    }

    public function fixed(float $value): static
    {
        return $this->state(fn () => ['type' => 'fixed_amount', 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'valid_from' => now()->subMonth()->toDateString(),
            'valid_until' => now()->subDay()->toDateString(),
        ]);
    }

    public function notYetValid(): static
    {
        return $this->state(fn () => [
            'valid_from' => now()->addDay()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
        ]);
    }

    public function limit(int $limit, int $used = 0): static
    {
        return $this->state(fn () => ['usage_limit' => $limit, 'used_count' => $used]);
    }
}
