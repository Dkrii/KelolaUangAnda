<x-app-layout>
    <x-slot:title>Ubah Anggaran - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Ubah Anggaran</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            <div class="mb-5 border-b border-neutral-200/80 pb-4">
                <h2 class="text-sm font-semibold text-neutral-900">Nominal Anggaran</h2>
                <p class="text-xs text-neutral-500">Perubahan langsung memengaruhi sisa anggaran bulan ini.</p>
            </div>

            @include('budgets._form', [
                'budget' => $budget,
                'action' => route('budgets.update', $budget),
                'method' => 'PUT',
                'submitLabel' => 'Perbarui Anggaran',
            ])
        </x-card>
    </div>
</x-app-layout>
