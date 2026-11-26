<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $wallets = $request->user()
            ->wallets()
            ->withCount(['transactions', 'incomingTransfers'])
            ->orderBy('name')
            ->get();

        $balances = $this->wallets->balancesFor($request->user());

        return view('wallets.index', [
            'wallets' => $wallets,
            'balances' => $balances,
            'totalBalance' => Money::add(...$balances->values()->all()),
        ]);
    }

    public function create(): View
    {
        return view('wallets.create');
    }

    public function store(StoreWalletRequest $request): RedirectResponse
    {
        $request->user()->wallets()->create($request->validated());

        return redirect()
            ->route('wallets.index')
            ->with('status', 'Dompet berhasil ditambahkan.');
    }

    public function edit(Request $request, Wallet $wallet): View
    {
        $this->authorize('update', $wallet);

        return view('wallets.edit', [
            'wallet' => $wallet,
            'balance' => $this->wallets->balanceFor($wallet),
        ]);
    }

    public function update(UpdateWalletRequest $request, Wallet $wallet): RedirectResponse
    {
        $this->authorize('update', $wallet);

        $wallet->update($request->validated());

        return redirect()
            ->route('wallets.index')
            ->with('status', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $this->authorize('delete', $wallet);

        if ($wallet->transactions()->exists() || $wallet->incomingTransfers()->exists()) {
            return back()->with('error', 'Dompet tidak bisa dihapus karena masih memiliki transaksi terkait.');
        }

        $wallet->delete();

        return redirect()
            ->route('wallets.index')
            ->with('status', 'Dompet berhasil dihapus.');
    }
}
