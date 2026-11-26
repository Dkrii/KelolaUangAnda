<?php

namespace Database\Factories;

use App\Models\SavingGoal;
use App\Models\SavingGoalContribution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingGoalContribution>
 */
class SavingGoalContributionFactory extends Factory
{
    protected $model = SavingGoalContribution::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'saving_goal_id' => SavingGoal::factory(),
            'amount' => fake()->randomFloat(2, 10000, 1000000),
            'contribution_date' => now()->toDateString(),
            'note' => null,
        ];
    }
}
