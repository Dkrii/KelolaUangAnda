<x-app-layout>
    <x-slot:title>Tambah Kategori - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Tambah Kategori</x-slot:header>

    <div class="max-w-xl">
        <x-card>
            @include('categories._form', [
                'defaultType' => $defaultType,
                'action' => route('categories.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Kategori',
            ])
        </x-card>
    </div>
</x-app-layout>
