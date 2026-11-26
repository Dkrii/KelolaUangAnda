<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Support\Money;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Calculate the current balance of every wallet owned by the user.
     *
     * Balance = initial balance + income - expense + incoming transfers - outgoing transfers.
     * Transfers never touch income or expense totals; they only move money between wallets.
     *
     * @return Collection<int, string> Keyed by wallet id.
     */
    public function balancesFor(User $user): Collection
    {
        $rows = DB::table('wallets')
            ->leftJoin('transactions', function (JoinClause $join) {
                $join->where(function (JoinClause $join) {
                    $join->whereColumn('transactions.wallet_id', '=', 'wallets.id')
                        ->orWhereColumn('transactions.destination_wallet_id', '=', 'wallets.id');
                })->whereNull('transactions.deleted_at');
            })
            ->where('wallets.user_id', $user->id)
            ->whereNull('wallets.deleted_at')
            ->groupBy('wallets.id', 'wallets.initial_balance')
            ->select('wallets.id', 'wallets.initial_balance')
            ->selectRaw("COALESCE(SUM(CASE
                WHEN transactions.type = 'income' AND transactions.wallet_id = wallets.id THEN transactions.amount
                WHEN transactions.type = 'expense' AND transactions.wallet_id = wallets.id THEN -transactions.amount
                WHEN transactions.type = 'transfer' AND transactions.destination_wallet_id = wallets.id THEN transactions.amount
                WHEN transactions.type = 'transfer' AND transactions.wallet_id = wallets.id THEN -transactions.amount
                ELSE 0
            END), 0) as balance")
            ->get();

        return $rows->mapWithKeys(fn ($row) => [
            (int) $row->id => Money::add($row->initial_balance, $row->balance),
        ]);
    }

    public function balanceFor(Wallet $wallet): string
    {
        return $this->balancesFor($wallet->user)->get($wallet->id, '0.00');
    }

    public function totalBalance(User $user): string
    {
        return Money::add(...$this->balancesFor($user)->values()->all());
    }
}
