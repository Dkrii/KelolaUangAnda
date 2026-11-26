<x-app-layout>
    <x-slot:title>Detail Target Tabungan - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Detail Target Tabungan</x-slot:header>

    @php
        $status = $goal->status();
        $progress = min(100, $goal->progressPercentage());
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-button variant="secondary" :href="route('saving-goals.index')">&larr; Kembali</x-button>
            <div class="flex gap-2">
                <x-button variant="secondary" :href="route('saving-goals.edit', $goal)">Ubah</x-button>
                <form method="POST" action="{{ route('saving-goals.destroy', $goal) }}" onsubmit="return confirm('Hapus target tabungan ini beserta riwayat setorannya?');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Hapus</x-button>
                </form>
            </div>
        </div>

        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-neutral-900">{{ $goal->name }}</h1>
                    @if ($goal->description)
                        <p class="mt-1 text-sm text-neutral-500">{{ $goal->description }}</p>
                    @endif
                    <p class="mt-2 text-sm text-neutral-500">
                        {{ $goal->deadline ? 'Tenggat '.$goal->deadline->format('d M Y') : 'Tanpa tenggat' }}
                    </p>
                </div>
                <x-badge :variant="$status->badgeVariant()">{{ $status->label() }}</x-badge>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Target</p>
                    <p class="mt-1 text-lg font-semibold text-neutral-900"><x-money :amount="$goal->target_amount" /></p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Terkumpul</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-700"><x-money :amount="$goal->saved_amount" /></p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-neutral-500">Sisa</p>
                    <p class="mt-1 text-lg font-semibold text-neutral-900"><x-money :amount="$goal->remainingAmount()" /></p>
                </div>
            </div>

            <div class="mt-6">
                <div class="h-3 overflow-hidden rounded-full bg-neutral-100">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $progress }}%"></div>
                </div>
                <p class="mt-2 text-xs text-neutral-500">{{ number_format($goal->progressPercentage(), 1, ',', '.') }}% dari target tercapai</p>
            </div>
        </x-card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Catat Setoran</h2>
                <form method="POST" action="{{ route('saving-goals.contributions.store', $goal) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-label for="amount">Nominal Setoran</x-label>
                        <x-input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" class="mt-1" :error="$errors->has('amount')" required />
                        <x-input-error :messages="$errors->get('amount')" />
                    </div>
                    <div>
                        <x-label for="contribution_date">Tanggal Setoran</x-label>
                        <x-input id="contribution_date" name="contribution_date" type="date" value="{{ old('contribution_date', now()->toDateString()) }}" class="mt-1" :error="$errors->has('contribution_date')" required />
                        <x-input-error :messages="$errors->get('contribution_date')" />
                    </div>
                    <div>
                        <x-label for="note">Catatan (opsional)</x-label>
                        <x-input id="note" name="note" type="text" value="{{ old('note') }}" class="mt-1" :error="$errors->has('note')" />
                        <x-input-error :messages="$errors->get('note')" />
                    </div>
                    <x-button type="submit">Simpan Setoran</x-button>
                </form>
            </x-card>

            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Riwayat Setoran</h2>
                @if ($goal->contributions->isEmpty())
                    <p class="mt-4 text-sm text-neutral-500">Belum ada setoran.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wider text-neutral-500">
                                    <th class="py-2 pr-4 font-medium">Tanggal</th>
                                    <th class="py-2 pr-4 font-medium">Catatan</th>
                                    <th class="py-2 text-right font-medium">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach ($goal->contributions as $contribution)
                                    <tr>
                                        <td class="whitespace-nowrap py-3 pr-4 text-neutral-600">{{ $contribution->contribution_date->format('d M Y') }}</td>
                                        <td class="py-3 pr-4 text-neutral-900">{{ $contribution->note ?: '-' }}</td>
                                        <td class="whitespace-nowrap py-3 text-right font-medium text-neutral-900"><x-money :amount="$contribution->amount" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
