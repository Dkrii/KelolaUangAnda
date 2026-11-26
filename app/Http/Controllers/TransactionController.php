<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactions) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $type = $request->string('type')->toString();
        $walletId = $request->integer('wallet_id');
        $categoryId = $request->integer('category_id');
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();
        $search = $request->string('search')->trim()->toString();

        $query = $user->transactions()->with(['wallet', 'destinationWallet', 'category']);

        if (in_array($type, array_column(TransactionType::cases(), 'value'), true)) {
            $query->where('type', $type);
        }

        if ($walletId > 0) {
            $query->where(function ($query) use ($walletId) {
                $query->where('wallet_id', $walletId)
                    ->orWhere('destination_wallet_id', $walletId);
            });
        }

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $query->whereDate('transaction_date', '>=', $from);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $query->whereDate('transaction_date', '<=', $to);
        }

        if ($search !== '') {
            $query->where('description', 'like', '%'.$search.'%');
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'wallets' => $user->wallets()->orderBy('name')->get(),
            'categories' => $user->categories()->orderBy('name')->get(),
            'filters' => [
                'type' => $type,
                'wallet_id' => $walletId ?: null,
                'category_id' => $categoryId ?: null,
                'from' => $from,
                'to' => $to,
                'search' => $search,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('transactions.create', $this->formData($request));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->transactions->create($request->user(), $request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil ditambahkan.');
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['wallet', 'destinationWallet', 'category']);

        return view('transactions.show', [
            'transaction' => $transaction,
        ]);
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return view('transactions.edit', [
            ...$this->formData($request),
            'transaction' => $transaction,
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $this->transactions->update($transaction, $request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $this->transactions->delete($transaction);

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi berhasil dihapus.');
    }

    /**
     * @return array{wallets: Collection<int, Wallet>, categories: Collection<int, Category>}
     */
    private function formData(Request $request): array
    {
        return [
            'wallets' => $request->user()->wallets()->where('is_active', true)->orderBy('name')->get(),
            'categories' => $request->user()->categories()->orderBy('name')->get(),
        ];
    }
}
