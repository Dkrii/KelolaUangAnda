@props(['category' => null, 'action', 'method' => 'POST', 'submitLabel' => 'Simpan Kategori'])

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($category)
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Jenis</p>
            <div class="mt-1.5">
                <x-badge :variant="$category->type === \App\Enums\CategoryType::Income ? 'emerald' : 'rose'">
                    {{ $category->type->label() }}
                </x-badge>
            </div>
            <input type="hidden" name="type" value="{{ old('type', $category->type->value) }}">
            <p class="mt-1.5 text-xs text-neutral-500">Jenis kategori tidak dapat diubah setelah dibuat.</p>
        </div>
    @else
        <div>
            <x-label for="type" value="Jenis" />
            <x-select id="type" name="type" class="mt-1" :error="$errors->has('type')" required>
                @foreach (\App\Enums\CategoryType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $defaultType?->value ?? 'expense') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('type')" class="mt-1.5" />
        </div>
    @endif

    <div>
        <x-label for="name" value="Nama Kategori" />
        <x-input
            id="name"
            name="name"
            type="text"
            class="mt-1"
            :value="old('name', $category?->name)"
            placeholder="Contoh: Makanan & Minuman"
            :error="$errors->has('name')"
            required
            autofocus
        />
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <x-label for="color" value="Warna" />
        <input
            id="color"
            name="color"
            type="color"
            value="{{ old('color', $category?->color ?? '#6b7280') }}"
            class="mt-1 h-10 w-16 cursor-pointer rounded-lg border border-neutral-300 bg-white p-1"
        >
        <x-input-error :messages="$errors->get('color')" class="mt-1.5" />
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-neutral-100 pt-4">
        <x-button variant="secondary" :href="route('categories.index')">Batal</x-button>
        <x-button type="submit">{{ $submitLabel }}</x-button>
    </div>
</form>
