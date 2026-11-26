<x-app-layout>
    <x-slot:title>Ubah Target Tabungan - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Ubah Target Tabungan</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            @include('saving-goals._form', ['goal' => $goal])
        </x-card>
    </div>
</x-app-layout>
