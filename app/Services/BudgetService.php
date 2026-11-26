<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class BudgetService
{
    /**
     * Get budgets and their usage for a given month.
     *
     * @return array{
     *     budgets: Collection<int, Budget>,
     *     totalBudget: string,
     *     totalSpent: string,
     *     remaining: string
     * }
     */
    public function summaryFor(User $user, Carbon $month): array
    {
        $month = $month->copy()->startOfMonth();
        $start = $month->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $start)
            ->orderBy('id')
            ->get();

        $spentByCategory = $user->transactions()
            ->where('type', TransactionType::Expense->value)
            ->whereBetween('transaction_date', [$start, $end])
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as spent')
            ->groupBy('category_id')
            ->pluck('spent', 'category_id');

        $totalBudget = '0.00';
        $totalSpent = '0.00';

        foreach ($budgets as $budget) {
            $spent = Money::normalize($spentByCategory->get($budget->category_id, 0));
            $percentage = $this->percentage($spent, (string) $budget->amount);

            $budget->setAttribute('spent', $spent);
            $budget->setAttribute('remaining', Money::sub($budget->amount, $spent));
            $budget->setAttribute('percentage', $percentage);
            $budget->setAttribute('status', $this->status($percentage));

            $totalBudget = Money::add($totalBudget, $budget->amount);
            $totalSpent = Money::add($totalSpent, $spent);
        }

        return [
            'budgets' => $budgets,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'remaining' => Money::sub($totalBudget, $totalSpent),
        ];
    }

    private function percentage(string $spent, string $amount): float
    {
        return (float) bcmul(bcdiv($spent, $amount, 4), '100', 2);
    }

    private function status(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'exceeded',
            $percentage >= 80 => 'warning',
            default => 'normal',
        };
    }
}
