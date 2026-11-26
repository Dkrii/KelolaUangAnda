<x-app-layout>
    <x-slot:title>Tambah Dompet - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Tambah Dompet</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            @include('wallets._form', [
                'action' => route('wallets.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Dompet',
            ])
        </x-card>
    </div>
</x-app-layout>
