<x-app-layout>
    <x-slot:title>Ubah Transaksi - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Ubah Transaksi</x-slot:header>

    <div class="max-w-3xl">
        <x-card>
            @include('transactions._form', [
                'transaction' => $transaction,
                'action' => route('transactions.update', $transaction),
                'method' => 'PATCH',
                'submitLabel' => 'Perbarui Transaksi',
            ])
        </x-card>
    </div>
</x-app-layout>
