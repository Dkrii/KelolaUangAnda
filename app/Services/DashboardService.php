<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DashboardService
{
    private const MONTH_LABELS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function __construct(private WalletService $wallets, private BudgetService $budgets) {}

    /**
     * Get financial summary for user.
     *
     * @return array{
     *     balance: string,
     *     income: string,
     *     expense: string,
     *     remainingBudget: string,
     *     recentTransactions: Collection<int, Transaction>,
     *     chartData: array{
     *         cashFlow: array{
     *             labels: array<string>,
     *             income: array<float>,
     *             expense: array<float>
     *         },
     *         categories: array{labels: array<string>, amounts: array<float>, colors: array<string>}
     *     }
     * }
     */
    public function getSummary(User $user): array
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totals = $user->transactions()
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereIn('type', [TransactionType::Income->value, TransactionType::Expense->value])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense")
            ->first();

        $recentTransactions = $user->transactions()
            ->with(['wallet', 'destinationWallet', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return [
            'balance' => $this->wallets->totalBalance($user),
            'income' => Money::normalize($totals->income ?? 0),
            'expense' => Money::normalize($totals->expense ?? 0),
            'remainingBudget' => $this->budgets->summaryFor($user, Carbon::now())['remaining'],
            'recentTransactions' => $recentTransactions,
            'chartData' => [
                'cashFlow' => [
                    'labels' => $this->cashFlowLabels(),
                    ...$this->cashFlowAmounts($user),
                ],
                'categories' => $this->expenseCategories($user, $startOfMonth, $endOfMonth),
            ],
        ];
    }

    /**
     * @return array<string>
     */
    private function cashFlowLabels(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(5);

        return collect(range(0, 5))
            ->map(fn (int $offset) => self::MONTH_LABELS[$start->copy()->addMonths($offset)->month - 1])
            ->all();
    }

    /**
     * @return array{income: array<float>, expense: array<float>}
     */
    private function cashFlowAmounts(User $user): array
    {
        $income = [];
        $expense = [];
        $start = Carbon::now()->startOfMonth()->subMonths(5);

        foreach (range(0, 5) as $offset) {
            $month = $start->copy()->addMonths($offset);

            $totals = $user->transactions()
                ->whereBetween('transaction_date', [
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString(),
                ])
                ->whereIn('type', [TransactionType::Income->value, TransactionType::Expense->value])
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income")
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense")
                ->first();

            $income[] = (float) Money::normalize($totals->income ?? 0);
            $expense[] = (float) Money::normalize($totals->expense ?? 0);
        }

        return ['income' => $income, 'expense' => $expense];
    }

    /**
     * @return array{labels: array<string>, amounts: array<float>, colors: array<string>}
     */
    private function expenseCategories(User $user, string $start, string $end): array
    {
        $rows = $user->transactions()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.type', TransactionType::Expense->value)
            ->whereBetween('transactions.transaction_date', [$start, $end])
            ->whereNull('categories.deleted_at')
            ->selectRaw('categories.name as name, categories.color as color, SUM(transactions.amount) as total')
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('name')->map(fn ($name) => (string) $name)->all(),
            'amounts' => $rows->map(fn ($row) => (float) Money::normalize($row->total))->all(),
            'colors' => $rows->pluck('color')->map(fn ($color) => $color ?: '#6b7280')->all(),
        ];
    }
}
