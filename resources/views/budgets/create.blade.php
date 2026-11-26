<x-app-layout>
    <x-slot:title>Buat Anggaran - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Tambah Anggaran</x-slot:header>

    <div class="max-w-2xl">
        @if ($categories->isEmpty())
            <x-alert type="warning" title="Belum ada kategori pengeluaran">
                Buat kategori pengeluaran terlebih dahulu sebelum menetapkan anggaran.
                <a href="{{ route('categories.create', ['type' => 'expense']) }}" class="font-medium underline">
                    Tambah kategori
                </a>
            </x-alert>
        @else
            <x-card>
                <div class="mb-5 border-b border-neutral-200/80 pb-4">
                    <h2 class="text-sm font-semibold text-neutral-900">Detail Anggaran</h2>
                    <p class="text-xs text-neutral-500">Satu anggaran per kategori setiap bulan.</p>
                </div>

                @include('budgets._form', [
                    'categories' => $categories,
                    'month' => $month,
                    'action' => route('budgets.store'),
                ])
            </x-card>
        @endif
    </div>
</x-app-layout>
