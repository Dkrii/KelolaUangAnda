<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Enums\WalletType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    /**
     * Create the starting wallet and category set for a new user.
     */
    public function initialize(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->wallets()->create([
                'name' => 'Tunai',
                'type' => WalletType::Cash,
                'initial_balance' => 0,
                'color' => '#10b981',
                'is_active' => true,
            ]);

            foreach ($this->defaultCategories() as $category) {
                $user->categories()->create($category);
            }
        });
    }

    /**
     * @return array<int, array{name: string, type: CategoryType, color: string}>
     */
    private function defaultCategories(): array
    {
        return [
            ['name' => 'Makanan & Minuman', 'type' => CategoryType::Expense, 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => CategoryType::Expense, 'color' => '#3b82f6'],
            ['name' => 'Belanja', 'type' => CategoryType::Expense, 'color' => '#ec4899'],
            ['name' => 'Tagihan & Utilitas', 'type' => CategoryType::Expense, 'color' => '#eab308'],
            ['name' => 'Hiburan', 'type' => CategoryType::Expense, 'color' => '#8b5cf6'],
            ['name' => 'Kesehatan', 'type' => CategoryType::Expense, 'color' => '#14b8a6'],
            ['name' => 'Pendidikan', 'type' => CategoryType::Expense, 'color' => '#6366f1'],
            ['name' => 'Lainnya', 'type' => CategoryType::Expense, 'color' => '#6b7280'],
            ['name' => 'Gaji', 'type' => CategoryType::Income, 'color' => '#10b981'],
            ['name' => 'Bonus', 'type' => CategoryType::Income, 'color' => '#22c55e'],
            ['name' => 'Investasi', 'type' => CategoryType::Income, 'color' => '#0ea5e9'],
            ['name' => 'Lainnya', 'type' => CategoryType::Income, 'color' => '#6b7280'],
        ];
    }
}
