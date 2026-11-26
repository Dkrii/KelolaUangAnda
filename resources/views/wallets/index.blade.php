<x-app-layout>
    <x-slot:title>Dompet - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Dompet</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="rounded-xl border border-neutral-200/80 bg-white px-5 py-4 shadow-xs">
                <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Total Saldo</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-neutral-900">
                    <x-money :amount="$totalBalance" />
                </p>
                <p class="mt-1 text-xs text-neutral-500">{{ $wallets->count() }} dompet terdaftar</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-button variant="indigo" :href="route('transfers.create')">Transfer</x-button>
                <x-button :href="route('wallets.create')">Tambah Dompet</x-button>
            </div>
        </div>

        @if ($wallets->isEmpty())
            <x-empty-state
                title="Belum ada dompet"
                description="Tambahkan dompet tunai, rekening bank, atau e-wallet untuk mulai mencatat keuangan Anda."
            >
                <x-button :href="route('wallets.create')">Tambah Dompet Pertama</x-button>
            </x-empty-state>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($wallets as $wallet)
                    <div class="flex flex-col rounded-xl border border-neutral-200/80 bg-white p-5 shadow-xs transition hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-semibold"
                                    style="background-color: {{ $wallet->color ?? '#10b981' }}1f; color: {{ $wallet->color ?? '#10b981' }};"
                                >
                                    {{ mb_substr($wallet->name, 0, 1) }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-neutral-900">{{ $wallet->name }}</p>
                                    <p class="text-xs text-neutral-500">
                                        {{ $wallet->type->label() }}
                                        @if ($wallet->institution)
                                            &middot; {{ $wallet->institution }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @unless ($wallet->is_active)
                                <x-badge>Nonaktif</x-badge>
                            @endunless
                        </div>

                        <div class="mt-4">
                            <p class="text-xs text-neutral-500">Saldo saat ini</p>
                            <p class="mt-0.5 text-xl font-semibold tracking-tight {{ \App\Support\Money::isNegative($balances->get($wallet->id, '0.00')) ? 'text-rose-600' : 'text-neutral-900' }}">
                                <x-money :amount="$balances->get($wallet->id, '0.00')" />
                            </p>
                            <p class="mt-1 text-xs text-neutral-400">
                                Saldo awal <x-money :amount="$wallet->initial_balance" />
                                @if ($wallet->account_number)
                                    &middot; {{ $wallet->account_number }}
                                @endif
                            </p>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-neutral-100 pt-3">
                            <p class="text-xs text-neutral-400">
                                {{ $wallet->transactions_count + $wallet->incoming_transfers_count }} transaksi
                            </p>
                            <div class="flex items-center gap-1">
                                <a
                                    href="{{ route('wallets.edit', $wallet) }}"
                                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900"
                                >
                                    Ubah
                                </a>
                                <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" data-confirm-delete="Hapus dompet ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
