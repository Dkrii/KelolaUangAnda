<x-app-layout>
    <x-slot:title>Tambah Transaksi - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Tambah Transaksi</x-slot:header>

    <div class="max-w-3xl">
        <x-card>
            @include('transactions._form', [
                'action' => route('transactions.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Transaksi',
            ])
        </x-card>
    </div>
</x-app-layout>
