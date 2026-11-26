<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $data = $this->normalize($data);

            return $user->transactions()->create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $data = $this->normalize($data);
            $transaction->update($data);

            return $transaction->refresh();
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(fn () => $transaction->delete());
    }

    /**
     * Keep type-specific fields coherent: transfers have a destination and no
     * category, while income and expense rows always clear the destination.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $type = $data['type'] instanceof TransactionType
            ? $data['type']
            : TransactionType::from($data['type']);

        if ($type->isTransfer()) {
            $data['category_id'] = null;
        } else {
            $data['destination_wallet_id'] = null;
        }

        return $data;
    }
}
