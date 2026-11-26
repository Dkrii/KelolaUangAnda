<x-app-layout>
    <x-slot:title>Anggaran - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Anggaran</x-slot:header>

    @php
        $statusLabels = ['normal' => 'Normal', 'warning' => 'Peringatan', 'exceeded' => 'Terlampaui'];
        $statusVariants = ['normal' => 'emerald', 'warning' => 'amber', 'exceeded' => 'rose'];
        $barClasses = ['normal' => 'bg-emerald-500', 'warning' => 'bg-amber-500', 'exceeded' => 'bg-rose-500'];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-neutral-500">
                Batasi pengeluaran per kategori. Status berubah saat pemakaian mencapai 80%.
            </p>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <form method="GET" action="{{ route('budgets.index') }}" class="flex items-end gap-2">
                    <div>
                        <x-label for="month" value="Periode" />
                        <x-input id="month" name="month" type="month" class="mt-1" :value="$month->format('Y-m')" />
                    </div>
                    <x-button variant="secondary" type="submit">Lihat</x-button>
                </form>
                <x-button :href="route('budgets.create')">Tambah Anggaran</x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card
                title="Total Anggaran"
                :value="'Rp ' . number_format((float) $summary['totalBudget'], 0, ',', '.')"
                variant="emerald"
                subtext="{{ $month->format('M Y') }}"
            />
            <x-stat-card
                title="Terpakai"
                :value="'Rp ' . number_format((float) $summary['totalSpent'], 0, ',', '.')"
                variant="rose"
                subtext="Dari transaksi pengeluaran"
            />
            <x-stat-card
                title="Sisa Anggaran"
                :value="'Rp ' . number_format((float) $summary['remaining'], 0, ',', '.')"
                variant="indigo"
                subtext="Total anggaran dikurangi terpakai"
            />
        </div>

        <x-card>
            <div class="mb-4 border-b border-neutral-200/80 pb-4">
                <h2 class="text-sm font-semibold text-neutral-900">Daftar Anggaran</h2>
                <p class="text-xs text-neutral-500">Periode {{ $month->format('F Y') }}</p>
            </div>

            @if ($summary['budgets']->isEmpty())
                <x-empty-state
                    title="Belum ada anggaran"
                    description="Tetapkan batas pengeluaran untuk memantau kondisi keuangan Anda."
                >
                    <x-button :href="route('budgets.create')">Buat Anggaran Pertama</x-button>
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-xs uppercase tracking-wider text-neutral-500">
                                <th class="py-2.5 pr-4 font-medium">Kategori</th>
                                <th class="py-2.5 pr-4 text-right font-medium">Anggaran</th>
                                <th class="py-2.5 pr-4 text-right font-medium">Terpakai</th>
                                <th class="py-2.5 pr-4 font-medium">Progres</th>
                                <th class="py-2.5 pr-4 text-right font-medium">Sisa</th>
                                <th class="py-2.5 pr-4 font-medium">Status</th>
                                <th class="py-2.5 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($summary['budgets'] as $budget)
                                <tr class="hover:bg-neutral-50/60">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $budget->category?->color ?? '#6b7280' }}"></span>
                                            <span class="font-medium text-neutral-900">{{ $budget->category?->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-right whitespace-nowrap text-neutral-700">
                                        <x-money :amount="$budget->amount" />
                                    </td>
                                    <td class="py-3 pr-4 text-right whitespace-nowrap text-neutral-700">
                                        <x-money :amount="$budget->spent" />
                                    </td>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-24 overflow-hidden rounded-full bg-neutral-100 sm:w-32">
                                                <div
                                                    class="h-full rounded-full {{ $barClasses[$budget->status] }}"
                                                    style="width: {{ min(100, (float) $budget->percentage) }}%"
                                                ></div>
                                            </div>
                                            <span class="text-xs text-neutral-500">{{ number_format((float) $budget->percentage, 0, ',', '.') }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-right whitespace-nowrap font-medium">
                                        <span class="{{ \App\Support\Money::isNegative($budget->remaining) ? 'text-rose-600' : 'text-neutral-700' }}">
                                            @if (\App\Support\Money::isNegative($budget->remaining)) &minus; @endif
                                            <x-money :amount="$budget->remaining" />
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-badge :variant="$statusVariants[$budget->status]">
                                            {{ $statusLabels[$budget->status] }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a
                                                href="{{ route('budgets.edit', $budget) }}"
                                                class="rounded-lg px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50"
                                            >
                                                Ubah
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('budgets.destroy', $budget) }}"
                                                data-confirm-delete="Hapus anggaran ini?"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <x-button variant="danger" type="submit">Hapus</x-button>
                                            </form>
                                        </div>
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
