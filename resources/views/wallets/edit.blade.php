<x-app-layout>
    <x-slot:title>Ubah Dompet - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Ubah Dompet</x-slot:header>

    <div class="max-w-2xl space-y-4">
        <x-alert type="info">
            Saldo saat ini <span class="font-semibold"><x-money :amount="$balance" /></span>. Mengubah saldo awal akan menggeser saldo berjalan dompet ini.
        </x-alert>

        <x-card>
            @include('wallets._form', [
                'wallet' => $wallet,
                'action' => route('wallets.update', $wallet),
                'method' => 'PATCH',
                'submitLabel' => 'Perbarui Dompet',
            ])
        </x-card>
    </div>
</x-app-layout>
