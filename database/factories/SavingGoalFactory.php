<?php

namespace Database\Factories;

use App\Models\SavingGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingGoal>
 */
class SavingGoalFactory extends Factory
{
    protected $model = SavingGoal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 100000, 10000000),
            'saved_amount' => 0,
            'deadline' => now()->addMonths(3)->toDateString(),
            'description' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'saved_amount' => $attributes['target_amount'],
            'completed_at' => now(),
        ]);
    }
}
