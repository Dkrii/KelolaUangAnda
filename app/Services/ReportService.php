<?php

namespace App\Services;

use App\Enums\ReportPeriod;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ReportService
{
    private const MONTH_LABELS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private const DAY_LABELS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    /**
     * @return array{
     *     period: ReportPeriod,
     *     start: Carbon,
     *     end: Carbon,
     *     income: string,
     *     expense: string,
     *     net: string,
     *     transactionCount: int,
     *     categoriesIncome: array<int, array{name: string, color: string, amount: string, percentage: float}>,
     *     categoriesExpense: array<int, array{name: string, color: string, amount: string, percentage: float}>,
     *     trend: array{labels: array<int, string>, income: array<int, string>, expense: array<int, string>},
     *     transactions: Collection<int, Transaction>
     * }
     */
    public function build(User $user, ReportPeriod $period, Carbon $date): array
    {
        [$start, $end] = $period->range($date);

        $transactions = $user->transactions()
            ->with('category')
            ->whereIn('type', [TransactionType::Income->value, TransactionType::Expense->value])
            ->whereDate('transaction_date', '>=', $start->toDateString())
            ->whereDate('transaction_date', '<=', $end->toDateString())
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $income = '0.00';
        $expense = '0.00';
        $incomeCategories = [];
        $expenseCategories = [];

        foreach ($transactions as $transaction) {
            $isIncome = $transaction->type === TransactionType::Income;
            $key = $transaction->category_id ?? 0;
            $categories = $isIncome ? $incomeCategories : $expenseCategories;

            if (! isset($categories[$key])) {
                $categories[$key] = [
                    'name' => $transaction->category?->name ?? 'Tanpa Kategori',
                    'color' => $transaction->category?->color ?? '#6b7280',
                    'amount' => '0.00',
                ];
            }

            $categories[$key]['amount'] = Money::add($categories[$key]['amount'], $transaction->amount);

            if ($isIncome) {
                $incomeCategories = $categories;
                $income = Money::add($income, $transaction->amount);
            } else {
                $expenseCategories = $categories;
                $expense = Money::add($expense, $transaction->amount);
            }
        }

        return [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'income' => $income,
            'expense' => $expense,
            'net' => Money::sub($income, $expense),
            'transactionCount' => $transactions->count(),
            'categoriesIncome' => $this->categoryRows($incomeCategories, $income),
            'categoriesExpense' => $this->categoryRows($expenseCategories, $expense),
            'trend' => $this->trend($transactions, $period, $start, $end),
            'transactions' => $transactions,
        ];
    }

    /**
     * @param  array<int, array{name: string, color: string, amount: string}>  $categories
     * @return array<int, array{name: string, color: string, amount: string, percentage: float}>
     */
    private function categoryRows(array $categories, string $total): array
    {
        $rows = [];

        foreach ($categories as $category) {
            $percentage = bccomp($total, '0', 2) === 0
                ? 0.0
                : (float) bcmul(bcdiv($category['amount'], $total, 4), '100', 2);

            $rows[] = [...$category, 'percentage' => $percentage];
        }

        usort($rows, fn (array $left, array $right) => bccomp($right['amount'], $left['amount'], 2));

        return $rows;
    }

    /**
     * @param  Collection<int, Transaction>  $transactions
     * @return array{labels: array<int, string>, income: array<int, string>, expense: array<int, string>}
     */
    private function trend(Collection $transactions, ReportPeriod $period, Carbon $start, Carbon $end): array
    {
        $buckets = [];
        $isYearly = $period === ReportPeriod::Yearly;

        foreach ($transactions as $transaction) {
            $key = $isYearly
                ? $transaction->transaction_date->format('Y-m')
                : $transaction->transaction_date->toDateString();

            if (! isset($buckets[$key])) {
                $buckets[$key] = ['income' => '0.00', 'expense' => '0.00'];
            }

            $type = $transaction->type === TransactionType::Income ? 'income' : 'expense';
            $buckets[$key][$type] = Money::add($buckets[$key][$type], $transaction->amount);
        }

        $labels = [];
        $keys = [];

        if ($isYearly) {
            for ($month = 1; $month <= 12; $month++) {
                $labels[] = self::MONTH_LABELS[$month - 1];
                $keys[] = $start->copy()->month($month)->format('Y-m');
            }
        } elseif ($period === ReportPeriod::Monthly) {
            for ($day = 1; $day <= $start->daysInMonth(); $day++) {
                $labels[] = (string) $day;
                $keys[] = $start->copy()->day($day)->toDateString();
            }
        } elseif ($period === ReportPeriod::Weekly) {
            for ($offset = 0; $offset < 7; $offset++) {
                $day = $start->copy()->addDays($offset);
                $labels[] = self::DAY_LABELS[$offset];
                $keys[] = $day->toDateString();
            }
        } else {
            $labels[] = $start->format('d M Y');
            $keys[] = $start->toDateString();
        }

        $income = [];
        $expense = [];

        foreach ($keys as $key) {
            $income[] = $buckets[$key]['income'] ?? '0.00';
            $expense[] = $buckets[$key]['expense'] ?? '0.00';
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense];
    }
}
