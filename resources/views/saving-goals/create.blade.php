<x-app-layout>
    <x-slot:title>Target Tabungan Baru - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Target Tabungan Baru</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            @include('saving-goals._form')
        </x-card>
    </div>
</x-app-layout>
