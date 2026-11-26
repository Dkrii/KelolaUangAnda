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

        <!-- Quick Actions -->
        <x-card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-neutral-900">Aksi Cepat</h2>
                    <p class="text-xs text-neutral-500">Catat aktivitas keuangan Anda dengan cepat.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button variant="secondary" :href="route('transactions.create', ['type' => 'income'])">+ Pemasukan</x-button>
                    <x-button variant="secondary" :href="route('transactions.create', ['type' => 'expense'])">- Pengeluaran</x-button>
                    <x-button variant="secondary" :href="route('transfers.create')">Transfer</x-button>
                </div>
            </div>
        </x-card>

        <!-- Grid: Charts area -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-card>
                    <div class="mb-4 border-b border-neutral-200/80 pb-4">
                        <h2 class="text-sm font-semibold text-neutral-900">Arus Kas Masuk vs Keluar</h2>
                        <p class="text-xs text-neutral-500">Perbandingan 6 bulan terakhir</p>
                    </div>
                    @if (array_sum($summary['chartData']['cashFlow']['income']) === 0.0 && array_sum($summary['chartData']['cashFlow']['expense']) === 0.0)
                        <x-empty-state
                            title="Belum ada arus kas"
                            description="Catat pemasukan atau pengeluaran untuk melihat grafik arus kas Anda."
                        />
                    @else
                        <div class="h-72">
                            <canvas
                                id="cashFlowChart"
                                role="img"
                                aria-label="Grafik arus kas masuk dan keluar 6 bulan terakhir"
                            ></canvas>
                        </div>
                    @endif
                    <script type="application/json" id="cashFlowChartData">
                        @json($summary['chartData']['cashFlow'])
                    </script>
                </x-card>
            </div>

            <div>
                <x-card>
                    <div class="mb-4 border-b border-neutral-200/80 pb-4">
                        <h2 class="text-sm font-semibold text-neutral-900">Pengeluaran per Kategori</h2>
                        <p class="text-xs text-neutral-500">Bulan ini</p>
                    </div>
                    @if (empty($summary['chartData']['categories']['labels']))
                        <x-empty-state
                            title="Belum ada data kategori"
                            description="Pengeluaran per kategori bulan ini akan tampil di sini."
                        />
                    @else
                        <div class="relative h-64">
                            <canvas
                                id="expenseCategoryChart"
                                role="img"
                                aria-label="Grafik pengeluaran per kategori bulan ini"
                            ></canvas>
                        </div>
                    @endif
                    <script type="application/json" id="expenseCategoryChartData">
                        @json($summary['chartData']['categories'])
                    </script>
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
                <a
                    href="{{ route('transactions.index') }}"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50"
                >
                    Lihat semua &rarr;
                </a>
            </div>

            @if ($summary['recentTransactions']->isEmpty())
                <x-empty-state
                    title="Belum ada transaksi"
                    description="Seluruh pencatatan pemasukan, pengeluaran, dan transfer antar dompet akan muncul di sini."
                >
                    <x-button :href="route('transactions.create')">Catat Transaksi Pertama</x-button>
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-xs uppercase tracking-wider text-neutral-500">
                                <th class="py-2.5 pr-4 font-medium">Tanggal</th>
                                <th class="py-2.5 pr-4 font-medium">Keterangan</th>
                                <th class="py-2.5 pr-4 font-medium">Dompet</th>
                                <th class="py-2.5 pr-4 font-medium">Jenis</th>
                                <th class="py-2.5 text-right font-medium">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($summary['recentTransactions'] as $transaction)
                                @php
                                    $variant = match ($transaction->type) {
                                        \App\Enums\TransactionType::Income => 'emerald',
                                        \App\Enums\TransactionType::Expense => 'rose',
                                        \App\Enums\TransactionType::Transfer => 'indigo',
                                    };
                                @endphp
                                <tr class="hover:bg-neutral-50/60">
                                    <td class="py-3 pr-4 whitespace-nowrap text-neutral-600">
                                        {{ $transaction->transaction_date->format('d M Y') }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <a
                                            href="{{ route('transactions.show', $transaction) }}"
                                            class="font-medium text-neutral-900 hover:text-emerald-700"
                                        >
                                            {{ $transaction->description ?: 'Tanpa keterangan' }}
                                        </a>
                                        @if ($transaction->category)
                                            <p class="text-xs text-neutral-400">{{ $transaction->category->name }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        {{ $transaction->wallet?->name ?? '-' }}
                                        @if ($transaction->type->isTransfer() && $transaction->destinationWallet)
                                            <span class="text-neutral-400">&rarr;</span>
                                            {{ $transaction->destinationWallet->name }}
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-badge :variant="$variant">{{ $transaction->type->label() }}</x-badge>
                                    </td>
                                    <td class="py-3 text-right whitespace-nowrap font-medium">
                                        <span class="{{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-600' : ($transaction->type === \App\Enums\TransactionType::Expense ? 'text-rose-600' : 'text-neutral-700') }}">
                                            @if ($transaction->type === \App\Enums\TransactionType::Income)
                                                +
                                            @elseif ($transaction->type === \App\Enums\TransactionType::Expense)
                                                &minus;
                                            @endif
                                            <x-money :amount="$transaction->amount" />
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
