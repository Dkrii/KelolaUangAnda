<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get financial summary for user.
     *
     * @return array{
     *     balance: string,
     *     income: string,
     *     expense: string,
     *     remainingBudget: string,
     *     recentTransactions: Collection<int, mixed>,
     *     chartData: array{labels: array<string>, income: array<float>, expense: array<float>}
     * }
     */
    public function getSummary(User $user): array
    {
        // ponytail: computed aggregates wired in Phase 2/3. Clean zero-state returned now.
        return [
            'balance' => '0.00',
            'income' => '0.00',
            'expense' => '0.00',
            'remainingBudget' => '0.00',
            'recentTransactions' => collect(),
            'chartData' => [
                'labels' => [],
                'income' => [],
                'expense' => [],
            ],
        ];
    }
}
