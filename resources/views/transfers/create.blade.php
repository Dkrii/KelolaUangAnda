<x-app-layout>
    <x-slot:title>Transfer - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Transfer Antar Dompet</x-slot:header>

    <div class="mx-auto max-w-2xl space-y-6">
        <x-alert type="info" title="Tentang transfer">
            Transfer memindahkan saldo dari satu dompet ke dompet lain. Transaksi ini tidak dihitung sebagai pemasukan maupun pengeluaran.
        </x-alert>

        <x-card>
            @if ($wallets->isEmpty())
                <x-alert type="warning" title="Belum ada dompet">
                    Tambahkan minimal dua dompet terlebih dahulu sebelum melakukan transfer.
                </x-alert>
            @endif

            <form method="POST" action="{{ route('transfers.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-label for="from_wallet_id" value="Dari Dompet" />
                        <x-select
                            id="from_wallet_id"
                            name="from_wallet_id"
                            class="mt-1"
                            :error="$errors->has('from_wallet_id')"
                            :disabled="$wallets->count() < 2"
                            required
                        >
                            <option value="">Pilih dompet asal</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}" @selected(old('from_wallet_id') == $wallet->id)>
                                    {{ $wallet->name }} ({{ $wallet->type->label() }})
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('from_wallet_id')" class="mt-1.5" />
                    </div>

                    <div>
                        <x-label for="to_wallet_id" value="Ke Dompet" />
                        <x-select
                            id="to_wallet_id"
                            name="to_wallet_id"
                            class="mt-1"
                            :error="$errors->has('to_wallet_id')"
                            :disabled="$wallets->count() < 2"
                            required
                        >
                            <option value="">Pilih dompet tujuan</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}" @selected(old('to_wallet_id') == $wallet->id)>
                                    {{ $wallet->name }} ({{ $wallet->type->label() }})
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('to_wallet_id')" class="mt-1.5" />
                    </div>

                    <div>
                        <x-label for="amount" value="Nominal" />
                        <x-input
                            id="amount"
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="mt-1"
                            data-nominal
                            :value="old('amount')"
                            placeholder="0.00"
                            :error="$errors->has('amount')"
                            :disabled="$wallets->count() < 2"
                            required
                        />
                        <x-input-error :messages="$errors->get('amount')" class="mt-1.5" />
                    </div>

                    <div>
                        <x-label for="transaction_date" value="Tanggal" />
                        <x-input
                            id="transaction_date"
                            name="transaction_date"
                            type="date"
                            class="mt-1"
                            :value="old('transaction_date', now()->toDateString())"
                            :error="$errors->has('transaction_date')"
                            :disabled="$wallets->count() < 2"
                            required
                        />
                        <x-input-error :messages="$errors->get('transaction_date')" class="mt-1.5" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-label for="description" value="Keterangan (opsional)" />
                        <x-textarea
                            id="description"
                            name="description"
                            rows="2"
                            class="mt-1"
                            placeholder="Contoh: Pindah dana ke rekening tabungan"
                            :error="$errors->has('description')"
                            :disabled="$wallets->count() < 2"
                        >{{ old('description') }}</x-textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-neutral-100 pt-4">
                    <x-button variant="secondary" :href="route('transactions.index')">Batal</x-button>
                    <x-button variant="indigo" type="submit" :disabled="$wallets->count() < 2">Simpan Transfer</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
