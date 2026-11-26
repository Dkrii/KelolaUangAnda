<x-app-layout>
    <x-slot:title>Kategori - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Kategori</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-neutral-500">
                Kelompokkan transaksi agar laporan dan anggaran lebih mudah dibaca.
            </p>
            <div class="flex flex-wrap gap-2">
                <x-button variant="secondary" :href="route('categories.create', ['type' => 'income'])">Tambah Pemasukan</x-button>
                <x-button :href="route('categories.create', ['type' => 'expense'])">Tambah Pengeluaran</x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @include('categories._list', [
                'title' => 'Kategori Pemasukan',
                'categories' => $incomeCategories,
                'type' => \App\Enums\CategoryType::Income,
            ])

            @include('categories._list', [
                'title' => 'Kategori Pengeluaran',
                'categories' => $expenseCategories,
                'type' => \App\Enums\CategoryType::Expense,
            ])
        </div>
    </div>
</x-app-layout>
