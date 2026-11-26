<x-app-layout>
    <x-slot:title>Detail Transaksi - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Detail Transaksi</x-slot:header>

    @php
        $variant = match ($transaction->type) {
            \App\Enums\TransactionType::Income => 'emerald',
            \App\Enums\TransactionType::Expense => 'rose',
            \App\Enums\TransactionType::Transfer => 'indigo',
        };
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-button variant="secondary" :href="route('transactions.index')">&larr; Kembali</x-button>
            <div class="flex gap-2">
                <x-button variant="secondary" :href="route('transactions.edit', $transaction)">Ubah</x-button>
                <form
                    method="POST"
                    action="{{ route('transactions.destroy', $transaction) }}"
                    data-confirm-delete="Hapus transaksi ini? Saldo akan disesuaikan."
                >
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Hapus</x-button>
                </form>
            </div>
        </div>

        <x-card>
            <div class="flex items-start justify-between gap-4 border-b border-neutral-200/80 pb-5">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Nominal</p>
                    <p class="mt-1 text-3xl font-semibold tracking-tight text-neutral-900">
                        <x-money :amount="$transaction->amount" />
                    </p>
                </div>
                <x-badge :variant="$variant">{{ $transaction->type->label() }}</x-badge>
            </div>

            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Tanggal</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $transaction->transaction_date->format('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Kategori</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $transaction->category?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Dompet Sumber</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $transaction->wallet?->name ?? '-' }}</dd>
                </div>
                @if ($transaction->type->isTransfer())
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Dompet Tujuan</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $transaction->destinationWallet?->name ?? '-' }}</dd>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Keterangan</dt>
                    <dd class="mt-1 text-sm text-neutral-900">{{ $transaction->description ?: '-' }}</dd>
                </div>
            </dl>
        </x-card>
    </div>
</x-app-layout>
