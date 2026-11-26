<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'type' => TransactionType::Expense,
            'wallet_id' => fn (array $attributes) => Wallet::factory()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'destination_wallet_id' => null,
            'category_id' => fn (array $attributes) => Category::factory()->create([
                'user_id' => $attributes['user_id'],
                'type' => CategoryType::Expense,
            ])->id,
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'transaction_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'description' => fake()->sentence(3),
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Income,
            'category_id' => fn (array $attributes) => Category::factory()->create([
                'user_id' => $attributes['user_id'],
                'type' => CategoryType::Income,
            ])->id,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Expense,
        ]);
    }

    public function transfer(Wallet $destination): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Transfer,
            'destination_wallet_id' => $destination->id,
            'category_id' => null,
        ]);
    }
}
