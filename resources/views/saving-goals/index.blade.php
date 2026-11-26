<x-app-layout>
    <x-slot:title>Target Tabungan - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Target Tabungan</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-neutral-500">Catat target dan setoran tabungan Anda.</p>
            <x-button :href="route('saving-goals.create')">+ Target Baru</x-button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card title="Total Target" :value="'Rp '.number_format((float) $totalTarget, 0, ',', '.')" variant="indigo" />
            <x-stat-card title="Sudah Terkumpul" :value="'Rp '.number_format((float) $totalSaved, 0, ',', '.')" variant="emerald" />
            <x-stat-card title="Sisa Target" :value="'Rp '.number_format((float) $remaining, 0, ',', '.')" variant="rose" />
        </div>

        @if ($goals->isEmpty())
            <x-empty-state title="Belum ada target tabungan" description="Buat target pertama untuk mulai memantau progres tabungan Anda.">
                <x-button :href="route('saving-goals.create')">Buat Target</x-button>
            </x-empty-state>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($goals as $goal)
                    @php
                        $status = $goal->status();
                        $progress = min(100, $goal->progressPercentage());
                    @endphp
                    <x-card>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-semibold text-neutral-900">{{ $goal->name }}</h2>
                                <p class="mt-1 text-sm text-neutral-500">
                                    {{ $goal->deadline ? 'Tenggat '.$goal->deadline->format('d M Y') : 'Tanpa tenggat' }}
                                </p>
                            </div>
                            <x-badge :variant="$status->badgeVariant()">{{ $status->label() }}</x-badge>
                        </div>

                        <div class="mt-5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-neutral-600"><x-money :amount="$goal->saved_amount" /></span>
                                <span class="font-medium text-neutral-900"><x-money :amount="$goal->target_amount" /></span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-neutral-100">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-neutral-500">
                                <span>{{ number_format($goal->progressPercentage(), 1, ',', '.') }}% tercapai</span>
                                <span>Sisa <x-money :amount="$goal->remainingAmount()" /></span>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-button variant="secondary" :href="route('saving-goals.show', $goal)">Detail</x-button>
                            <x-button variant="secondary" :href="route('saving-goals.edit', $goal)">Ubah</x-button>
                            <form method="POST" action="{{ route('saving-goals.destroy', $goal) }}" onsubmit="return confirm('Hapus target tabungan ini beserta riwayat setorannya?');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger">Hapus</x-button>
                            </form>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
