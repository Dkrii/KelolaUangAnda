<x-app-layout>
    <x-slot:title>Laporan Keuangan - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Laporan Keuangan</x-slot:header>

    @php
        $filters = ['period' => $period->value, 'date' => $date->format('Y-m-d')];
    @endphp

    <div class="space-y-6">
        <x-card>
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="sm:w-48">
                    <x-label for="period">Periode</x-label>
                    <x-select id="period" name="period" class="mt-1">
                        @foreach (\App\Enums\ReportPeriod::cases() as $option)
                            <option value="{{ $option->value }}" @selected($period === $option)>{{ $option->label() }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="sm:w-56">
                    <x-label for="date">Tanggal Acuan</x-label>
                    <x-input id="date" type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="mt-1" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Tampilkan</x-button>
                    <x-button variant="rose" :href="route('reports.pdf', $filters)">Export PDF</x-button>
                    <x-button variant="emerald" :href="route('reports.excel', $filters)">Export Excel</x-button>
                </div>
            </form>
        </x-card>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card title="Pemasukan" :value="'Rp '.number_format((float) $report['income'], 0, ',', '.')" variant="emerald" />
            <x-stat-card title="Pengeluaran" :value="'Rp '.number_format((float) $report['expense'], 0, ',', '.')" variant="rose" />
            <x-stat-card title="Arus Kas Bersih" :value="'Rp '.number_format((float) $report['net'], 0, ',', '.')" variant="indigo" />
        </div>

        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-neutral-900">Tren Arus Kas</h2>
                    <p class="mt-1 text-sm text-neutral-500">
                        {{ $report['start']->format('d M Y') }} &ndash; {{ $report['end']->format('d M Y') }}
                    </p>
                </div>
                <x-badge variant="neutral">{{ $report['transactionCount'] }} transaksi</x-badge>
            </div>
            <div class="mt-5 h-72">
                <canvas id="reportTrendChart"></canvas>
            </div>
            <script type="application/json" id="reportTrendChartData">@json($report['trend'])</script>
        </x-card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Pemasukan per Kategori</h2>
                @if (count($report['categoriesIncome']) === 0)
                    <p class="mt-4 text-sm text-neutral-500">Belum ada pemasukan pada periode ini.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($report['categoriesIncome'] as $category)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $category['color'] }}"></span>
                                    <span class="truncate text-neutral-700">{{ $category['name'] }}</span>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-neutral-900"><x-money :amount="$category['amount']" /></p>
                                    <p class="text-xs text-neutral-500">{{ number_format($category['percentage'], 1, ',', '.') }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Pengeluaran per Kategori</h2>
                @if (count($report['categoriesExpense']) === 0)
                    <p class="mt-4 text-sm text-neutral-500">Belum ada pengeluaran pada periode ini.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($report['categoriesExpense'] as $category)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $category['color'] }}"></span>
                                    <span class="truncate text-neutral-700">{{ $category['name'] }}</span>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-neutral-900"><x-money :amount="$category['amount']" /></p>
                                    <p class="text-xs text-neutral-500">{{ number_format($category['percentage'], 1, ',', '.') }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        <x-card>
            <h2 class="text-base font-semibold text-neutral-900">Rincian Transaksi</h2>
            @if ($report['transactions']->isEmpty())
                <div class="mt-4">
                    <x-empty-state title="Belum ada transaksi" description="Tidak ada transaksi pada periode ini." />
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-neutral-500">
                                <th class="py-2 pr-4 font-medium">Tanggal</th>
                                <th class="py-2 pr-4 font-medium">Keterangan</th>
                                <th class="py-2 pr-4 font-medium">Kategori</th>
                                <th class="py-2 pr-4 font-medium">Jenis</th>
                                <th class="py-2 text-right font-medium">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($report['transactions'] as $transaction)
                                <tr>
                                    <td class="whitespace-nowrap py-3 pr-4 text-neutral-600">{{ $transaction->transaction_date->format('d M Y') }}</td>
                                    <td class="max-w-xs truncate py-3 pr-4 text-neutral-900">{{ $transaction->description ?: '-' }}</td>
                                    <td class="py-3 pr-4 text-neutral-600">{{ $transaction->category?->name ?? '-' }}</td>
                                    <td class="py-3 pr-4">
                                        <x-badge :variant="$transaction->type === \App\Enums\TransactionType::Income ? 'emerald' : 'rose'">
                                            {{ $transaction->type->label() }}
                                        </x-badge>
                                    </td>
                                    <td class="whitespace-nowrap py-3 text-right font-medium text-neutral-900">
                                        <x-money :amount="$transaction->amount" />
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
