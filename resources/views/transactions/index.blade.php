<x-app-layout>
    <x-slot:title>Transaksi - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Transaksi</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-neutral-500">Catat pemasukan, pengeluaran, dan transfer antar dompet.</p>
            <div class="flex flex-wrap gap-2">
                <x-button variant="secondary" :href="route('transfers.create')">Transfer</x-button>
                <x-button :href="route('transactions.create')">Tambah Transaksi</x-button>
            </div>
        </div>

        <x-card>
            <form method="GET" action="{{ route('transactions.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <x-label for="filter_type" value="Jenis" />
                        <x-select id="filter_type" name="type" class="mt-1">
                            <option value="">Semua jenis</option>
                            @foreach (\App\Enums\TransactionType::cases() as $type)
                                <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label for="filter_wallet" value="Dompet" />
                        <x-select id="filter_wallet" name="wallet_id" class="mt-1">
                            <option value="">Semua dompet</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}" @selected($filters['wallet_id'] === $wallet->id)>
                                    {{ $wallet->name }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label for="filter_category" value="Kategori" />
                        <x-select id="filter_category" name="category_id" class="mt-1">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label for="filter_search" value="Cari keterangan" />
                        <x-input
                            id="filter_search"
                            name="search"
                            type="search"
                            class="mt-1"
                            :value="$filters['search']"
                            placeholder="Kata kunci..."
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-label for="filter_from" value="Dari tanggal" />
                        <x-input id="filter_from" name="from" type="date" class="mt-1" :value="$filters['from']" />
                    </div>
                    <div>
                        <x-label for="filter_to" value="Sampai tanggal" />
                        <x-input id="filter_to" name="to" type="date" class="mt-1" :value="$filters['to']" />
                    </div>
                </div>

                <div class="flex items-center gap-2 border-t border-neutral-100 pt-4">
                    <x-button type="submit" variant="secondary">Terapkan Filter</x-button>
                    <a
                        href="{{ route('transactions.index') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-500 hover:text-neutral-800"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </x-card>

        <x-card>
            <div class="mb-4 flex items-center justify-between border-b border-neutral-200/80 pb-4">
                <div>
                    <h2 class="text-sm font-semibold text-neutral-900">Daftar Transaksi</h2>
                    <p class="text-xs text-neutral-500">{{ $transactions->total() }} transaksi ditemukan</p>
                </div>
            </div>

            @if ($transactions->isEmpty())
                <x-empty-state
                    title="Belum ada transaksi"
                    description="Mulai catat pemasukan atau pengeluaran pertama Anda, atau ubah filter pencarian."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-xs uppercase tracking-wider text-neutral-500">
                                <th class="py-2.5 pr-4 font-medium">Tanggal</th>
                                <th class="py-2.5 pr-4 font-medium">Keterangan</th>
                                <th class="py-2.5 pr-4 font-medium">Dompet</th>
                                <th class="py-2.5 pr-4 font-medium">Jenis</th>
                                <th class="py-2.5 pr-4 text-right font-medium">Nominal</th>
                                <th class="py-2.5 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($transactions as $transaction)
                                @php
                                    $variant = match ($transaction->type) {
                                        \App\Enums\TransactionType::Income => 'emerald',
                                        \App\Enums\TransactionType::Expense => 'rose',
                                        \App\Enums\TransactionType::Transfer => 'indigo',
                                    };
                                @endphp
                                <tr class="hover:bg-neutral-50/60">
                                    <td class="py-3 pr-4 whitespace-nowrap text-neutral-600">
                                        {{ $transaction->transaction_date->format('d M Y') }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-neutral-900">
                                            {{ $transaction->description ?: 'Tanpa keterangan' }}
                                        </p>
                                        @if ($transaction->category)
                                            <p class="text-xs text-neutral-400">{{ $transaction->category->name }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        {{ $transaction->wallet?->name ?? '-' }}
                                        @if ($transaction->type->isTransfer() && $transaction->destinationWallet)
                                            <span class="text-neutral-400">&rarr;</span>
                                            {{ $transaction->destinationWallet->name }}
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-badge :variant="$variant">{{ $transaction->type->label() }}</x-badge>
                                    </td>
                                    <td class="py-3 pr-4 text-right whitespace-nowrap font-medium">
                                        <span class="{{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-600' : ($transaction->type === \App\Enums\TransactionType::Expense ? 'text-rose-600' : 'text-neutral-700') }}">
                                            @if ($transaction->type === \App\Enums\TransactionType::Income)
                                                +
                                            @elseif ($transaction->type === \App\Enums\TransactionType::Expense)
                                                &minus;
                                            @endif
                                            <x-money :amount="$transaction->amount" />
                                        </span>
                                    </td>
                                    <td class="py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <a
                                                href="{{ route('transactions.show', $transaction) }}"
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-neutral-600 hover:bg-neutral-100"
                                            >
                                                Lihat
                                            </a>
                                            <a
                                                href="{{ route('transactions.edit', $transaction) }}"
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-neutral-600 hover:bg-neutral-100"
                                            >
                                                Ubah
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('transactions.destroy', $transaction) }}"
                                                onsubmit="return confirm('Hapus transaksi ini? Saldo akan disesuaikan.');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="rounded-lg px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50"
                                                >
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-t border-neutral-100 pt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
