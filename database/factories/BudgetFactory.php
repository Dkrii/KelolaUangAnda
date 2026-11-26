<?php

namespace Database\Factories;

use App\Enums\BudgetPeriod;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => fn (array $attributes) => Category::factory()->expense()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'amount' => fake()->randomFloat(2, 100000, 5000000),
            'period' => BudgetPeriod::Monthly,
            'month' => now()->startOfMonth()->toDateString(),
        ];
    }
}
