<x-app-layout>
    <x-slot:title>Dashboard - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Ringkasan Keuangan</x-slot:header>

    <div class="space-y-6">
        <!-- 4 Financial Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Saldo -->
            <x-stat-card
                title="Total Saldo"
                :value="'Rp ' . number_format((float) $summary['balance'], 0, ',', '.')"
                variant="emerald"
                subtext="Seluruh dompet aktif"
            >
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </x-slot:icon>
            </x-stat-card>

            <!-- Pemasukan -->
            <x-stat-card
                title="Pemasukan Bulan Ini"
                :value="'Rp ' . number_format((float) $summary['income'], 0, ',', '.')"
                variant="emerald"
                subtext="Total transaksi masuk"
            >
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                </x-slot:icon>
            </x-stat-card>

            <!-- Pengeluaran -->
            <x-stat-card
                title="Pengeluaran Bulan Ini"
                :value="'Rp ' . number_format((float) $summary['expense'], 0, ',', '.')"
                variant="rose"
                subtext="Total transaksi keluar"
            >
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                </x-slot:icon>
            </x-stat-card>

            <!-- Sisa Anggaran -->
            <x-stat-card
                title="Sisa Anggaran"
                :value="'Rp ' . number_format((float) $summary['remainingBudget'], 0, ',', '.')"
                variant="indigo"
                subtext="Batas pengeluaran tersisa"
            >
                <x-slot:icon>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </x-slot:icon>
            </x-stat-card>
        </div>

        <!-- Grid: Charts area & recent transactions -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Chart placeholder (Phase 3) -->
            <div class="lg:col-span-2">
                <x-card>
                    <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4 mb-4">
                        <div>
                            <h2 class="text-sm font-semibold text-neutral-900">Arus Kas Masuk vs Keluar</h2>
                            <p class="text-xs text-neutral-500">Perbandingan pemasukan dan pengeluaran</p>
                        </div>
                        <span class="rounded bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-600">
                            Phase 3
                        </span>
                    </div>

                    <x-empty-state
                        title="Grafik akan aktif di Phase 3"
                        description="Visualisasi Chart.js akan membaca data transaksi nyata setelah modul Transaksi diimplementasikan pada Phase 2."
                    />
                </x-card>
            </div>

            <!-- Expense Category Breakdown (Phase 3) -->
            <div>
                <x-card>
                    <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4 mb-4">
                        <div>
                            <h2 class="text-sm font-semibold text-neutral-900">Pengeluaran per Kategori</h2>
                            <p class="text-xs text-neutral-500">Bulan ini</p>
                        </div>
                        <span class="rounded bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-600">
                            Phase 3
                        </span>
                    </div>

                    <x-empty-state
                        title="Belum ada data kategori"
                        description="Kategori pengeluaran akan muncul setelah Anda mulai mencatat transaksi."
                    />
                </x-card>
            </div>
        </div>

        <!-- Recent Transactions -->
        <x-card>
            <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4 mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-neutral-900">Transaksi Terbaru</h2>
                    <p class="text-xs text-neutral-500">Aktivitas keuangan paling akhir</p>
                </div>
                <span class="rounded bg-emerald-50 text-emerald-700 px-2 py-0.5 text-[11px] font-medium border border-emerald-100">
                    Phase 2: CRUD Transaksi
                </span>
            </div>

            @if ($summary['recentTransactions']->isEmpty())
                <x-empty-state
                    title="Belum ada transaksi"
                    description="Seluruh pencatatan pemasukan, pengeluaran, dan transfer antar dompet akan muncul di sini."
                />
            @else
                {{-- Table will render in Phase 2 when transactions exist --}}
            @endif
        </x-card>
    </div>
</x-app-layout>
