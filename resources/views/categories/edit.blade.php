<x-app-layout>
    <x-slot:title>Ubah Kategori - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Ubah Kategori</x-slot:header>

    <div class="max-w-xl">
        <x-card>
            @include('categories._form', [
                'category' => $category,
                'action' => route('categories.update', $category),
                'method' => 'PATCH',
                'submitLabel' => 'Perbarui Kategori',
            ])
        </x-card>
    </div>
</x-app-layout>
