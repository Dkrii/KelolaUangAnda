@props(['transaction' => null, 'wallets', 'categories', 'action', 'method' => 'POST', 'submitLabel' => 'Simpan Transaksi'])

@php
$currentType = old('type', $transaction?->type?->value ?? request()->string('type')->toString());
if (! in_array($currentType, ['income', 'expense', 'transfer'], true)) {
    $currentType = 'expense';
}
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-5"
    x-data="{ type: '{{ $currentType }}' }"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="type" x-bind:value="type">

    <div>
        <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Jenis Transaksi</p>
        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
            @foreach (\App\Enums\TransactionType::cases() as $type)
                <button
                    type="button"
                    @click="type = '{{ $type->value }}'"
                    x-bind:class="type === '{{ $type->value }}'
                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-500'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:border-neutral-300 hover:bg-neutral-50'"
                    class="rounded-lg border px-3 py-2.5 text-sm font-medium transition"
                >
                    {{ $type->label() }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($wallets->isEmpty())
        <x-alert type="warning" title="Belum ada dompet">
            Tambahkan dompet terlebih dahulu sebelum mencatat transaksi.
        </x-alert>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-label for="wallet_id" value="Dompet Sumber" />
            <x-select
                id="wallet_id"
                name="wallet_id"
                class="mt-1"
                :error="$errors->has('wallet_id')"
                required
            >
                @foreach ($wallets as $wallet)
                    <option value="{{ $wallet->id }}" @selected(old('wallet_id', $transaction?->wallet_id) == $wallet->id)>
                        {{ $wallet->name }} ({{ $wallet->type->label() }})
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('wallet_id')" class="mt-1.5" />
        </div>

        <div x-show="type === 'transfer'" x-cloak>
            <x-label for="destination_wallet_id" value="Ke Dompet" />
            <x-select
                id="destination_wallet_id"
                name="destination_wallet_id"
                class="mt-1"
                x-bind:disabled="type !== 'transfer'"
                :error="$errors->has('destination_wallet_id')"
                x-bind:required="type === 'transfer'"
            >
                <option value="">Pilih dompet tujuan</option>
                @foreach ($wallets as $wallet)
                    <option
                        value="{{ $wallet->id }}"
                        @selected(old('destination_wallet_id', $transaction?->destination_wallet_id) == $wallet->id)
                    >
                        {{ $wallet->name }} ({{ $wallet->type->label() }})
                    </option>
                @endforeach
            </x-select>
            <p class="mt-1.5 text-xs text-neutral-500">Transfer memindahkan saldo antar dompet, bukan pemasukan atau pengeluaran.</p>
            <x-input-error :messages="$errors->get('destination_wallet_id')" class="mt-1.5" />
        </div>

        <div class="sm:col-span-2" x-show="type === 'income'" x-cloak>
            <x-label for="category_income" value="Kategori Pemasukan" />
            <x-select
                id="category_income"
                name="category_id"
                class="mt-1"
                x-bind:disabled="type !== 'income'"
                :error="$errors->has('category_id')"
                x-bind:required="type === 'income'"
            >
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    @if ($category->type === \App\Enums\CategoryType::Income)
                        <option value="{{ $category->id }}" @selected(old('category_id', $transaction?->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endif
                @endforeach
            </x-select>
        </div>

        <div class="sm:col-span-2" x-show="type === 'expense'" x-cloak>
            <x-label for="category_expense" value="Kategori Pengeluaran" />
            <x-select
                id="category_expense"
                name="category_id"
                class="mt-1"
                x-bind:disabled="type !== 'expense'"
                :error="$errors->has('category_id')"
                x-bind:required="type === 'expense'"
            >
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    @if ($category->type === \App\Enums\CategoryType::Expense)
                        <option value="{{ $category->id }}" @selected(old('category_id', $transaction?->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endif
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
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
                :value="old('amount', $transaction?->amount)"
                placeholder="0.00"
                :error="$errors->has('amount')"
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
                :value="old('transaction_date', $transaction?->transaction_date?->format('Y-m-d') ?? now()->toDateString())"
                :error="$errors->has('transaction_date')"
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
                placeholder="Contoh: Belanja bulanan di supermarket"
                :error="$errors->has('description')"
            >{{ old('description', $transaction?->description) }}</x-textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-neutral-100 pt-4">
        <x-button variant="secondary" :href="route('transactions.index')">Batal</x-button>
        <x-button type="submit" :disabled="$wallets->isEmpty()">{{ $submitLabel }}</x-button>
    </div>
</form>
