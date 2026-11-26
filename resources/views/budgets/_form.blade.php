@props(['budget' => null, 'categories' => null, 'month' => null, 'action', 'method' => 'POST', 'submitLabel' => 'Simpan Anggaran'])

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($budget)
        <div class="rounded-lg bg-neutral-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Kategori</p>
            <p class="mt-1 text-sm font-medium text-neutral-900">{{ $budget->category?->name ?? '-' }}</p>
            <p class="mt-3 text-xs font-medium uppercase tracking-wider text-neutral-500">Periode</p>
            <p class="mt-1 text-sm text-neutral-700">
                {{ $budget->period->label() }} &middot; {{ $budget->month->format('F Y') }}
            </p>
            <p class="mt-3 text-xs text-neutral-500">Kategori dan periode tidak dapat diubah setelah dibuat.</p>
        </div>
    @else
        <div>
            <x-label for="category_id" value="Kategori Pengeluaran" />
            <x-select id="category_id" name="category_id" class="mt-1" :error="$errors->has('category_id')" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
        </div>

        <div>
            <x-label for="month" value="Periode" />
            <x-input
                id="month"
                name="month"
                type="month"
                class="mt-1"
                :value="old('month', $month?->format('Y-m') ?? now()->format('Y-m'))"
                :error="$errors->has('month')"
                required
            />
            <x-input-error :messages="$errors->get('month')" class="mt-1.5" />
            <p class="mt-1.5 text-xs text-neutral-500">Anggaran berlaku untuk satu bulan penuh.</p>
        </div>
    @endif

    <div>
        <x-label for="amount" value="Nominal Anggaran" />
        <x-input
            id="amount"
            name="amount"
            type="number"
            step="0.01"
            min="0.01"
            class="mt-1"
            :value="old('amount', $budget?->amount)"
            placeholder="Contoh: 1500000"
            :error="$errors->has('amount')"
            required
        />
        <x-input-error :messages="$errors->get('amount')" class="mt-1.5" />
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-neutral-100 pt-4">
        <x-button variant="secondary" :href="route('budgets.index')">Batal</x-button>
        <x-button type="submit">{{ $submitLabel }}</x-button>
    </div>
</form>
