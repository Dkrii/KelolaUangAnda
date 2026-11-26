@php
    $editing = isset($goal) && $goal !== null;
@endphp

<form method="POST" action="{{ $editing ? route('saving-goals.update', $goal) : route('saving-goals.store') }}" class="space-y-5">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div>
        <x-label for="name">Nama Target</x-label>
        <x-input id="name" name="name" type="text" value="{{ old('name', $goal->name ?? '') }}" class="mt-1" :error="$errors->has('name')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-label for="target_amount">Target Nominal</x-label>
            <x-input id="target_amount" name="target_amount" type="number" min="0.01" step="0.01" value="{{ old('target_amount', $goal->target_amount ?? '') }}" class="mt-1" data-nominal :error="$errors->has('target_amount')" required />
            <x-input-error :messages="$errors->get('target_amount')" />
        </div>
        <div>
            <x-label for="deadline">Tenggat (opsional)</x-label>
            <x-input id="deadline" name="deadline" type="date" value="{{ old('deadline', $goal->deadline?->format('Y-m-d') ?? '') }}" class="mt-1" :error="$errors->has('deadline')" />
            <x-input-error :messages="$errors->get('deadline')" />
        </div>
    </div>

    <div>
        <x-label for="description">Catatan (opsional)</x-label>
        <x-textarea id="description" name="description" rows="3" class="mt-1" :error="$errors->has('description')">{{ old('description', $goal->description ?? '') }}</x-textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>

    <div class="flex items-center gap-2">
        <x-button type="submit">{{ $editing ? 'Simpan Perubahan' : 'Buat Target' }}</x-button>
        <x-button variant="secondary" :href="$editing ? route('saving-goals.show', $goal) : route('saving-goals.index')">Batal</x-button>
    </div>
</form>
