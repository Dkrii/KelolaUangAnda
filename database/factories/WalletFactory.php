<?php

namespace Database\Factories;

use App\Enums\WalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
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
            'name' => 'Dompet '.fake()->unique()->word(),
            'type' => WalletType::Cash,
            'institution' => null,
            'account_number' => null,
            'initial_balance' => 0,
            'color' => '#10b981',
            'is_active' => true,
        ];
    }

    public function bank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletType::Bank,
            'institution' => fake()->company(),
            'account_number' => (string) fake()->numerify('##########'),
        ]);
    }

    public function ewallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletType::EWallet,
            'institution' => fake()->randomElement(['GoPay', 'OVO', 'DANA', 'ShopeePay']),
            'account_number' => fake()->numerify('08##########'),
        ]);
    }
}
