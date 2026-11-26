<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\StoreTransferRequest;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function __construct(private TransactionService $transactions) {}

    public function create(Request $request): View
    {
        return view('transfers.create', [
            'wallets' => $request->user()->wallets()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->transactions->create($request->user(), [
            'type' => TransactionType::Transfer->value,
            'wallet_id' => $data['from_wallet_id'],
            'destination_wallet_id' => $data['to_wallet_id'],
            'category_id' => null,
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transfer berhasil dicatat.');
    }
}
