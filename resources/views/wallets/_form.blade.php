@props(['wallet' => null, 'action', 'method' => 'POST', 'submitLabel' => 'Simpan Dompet'])

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-label for="name" value="Nama Dompet" />
            <x-input
                id="name"
                name="name"
                type="text"
                class="mt-1"
                :value="old('name', $wallet?->name)"
                placeholder="Contoh: Dompet Utama, BCA, GoPay"
                :error="$errors->has('name')"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <x-label for="type" value="Jenis" />
            <x-select id="type" name="type" class="mt-1" :error="$errors->has('type')" required>
                @foreach (\App\Enums\WalletType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $wallet?->type?->value ?? 'cash') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('type')" class="mt-1.5" />
        </div>

        <div>
            <x-label for="color" value="Warna" />
            <input
                id="color"
                name="color"
                type="color"
                value="{{ old('color', $wallet?->color ?? '#10b981') }}"
                class="mt-1 h-10 w-16 cursor-pointer rounded-lg border border-neutral-300 bg-white p-1"
            >
            <x-input-error :messages="$errors->get('color')" class="mt-1.5" />
        </div>

        <div>
            <x-label for="institution" value="Institusi (opsional)" />
            <x-input
                id="institution"
                name="institution"
                type="text"
                class="mt-1"
                :value="old('institution', $wallet?->institution)"
                placeholder="Contoh: BCA, Mandiri, OVO"
                :error="$errors->has('institution')"
            />
            <x-input-error :messages="$errors->get('institution')" class="mt-1.5" />
        </div>

        <div>
            <x-label for="account_number" value="Nomor Rekening / Telepon (opsional)" />
            <x-input
                id="account_number"
                name="account_number"
                type="text"
                class="mt-1"
                :value="old('account_number', $wallet?->account_number)"
                :error="$errors->has('account_number')"
            />
            <x-input-error :messages="$errors->get('account_number')" class="mt-1.5" />
        </div>

        <div class="sm:col-span-2">
            <x-label for="initial_balance" value="Saldo Awal" />
            <x-input
                id="initial_balance"
                name="initial_balance"
                type="number"
                step="0.01"
                min="0"
                class="mt-1"
                data-nominal
                :value="old('initial_balance', $wallet?->initial_balance ?? 0)"
                :error="$errors->has('initial_balance')"
                required
            />
            <p class="mt-1.5 text-xs text-neutral-500">
                Saldo awal adalah kondisi dompet sebelum transaksi dicatat. Saldo berjalan dihitung otomatis dari transaksi.
            </p>
            <x-input-error :messages="$errors->get('initial_balance')" class="mt-1.5" />
        </div>

        <div class="sm:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-neutral-700">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $wallet?->is_active ?? true))
                    class="h-4 w-4 rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500"
                >
                Dompet aktif (dapat dipilih saat mencatat transaksi)
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-neutral-100 pt-4">
        <x-button variant="secondary" :href="route('wallets.index')">Batal</x-button>
        <x-button type="submit">{{ $submitLabel }}</x-button>
    </div>
</form>
