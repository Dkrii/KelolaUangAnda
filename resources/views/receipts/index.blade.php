<x-app-layout>
    <x-slot:title>Scan Struk - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Scan Struk</x-slot:header>

    <div class="space-y-6">
        <div class="flex justify-end">
            <x-button variant="primary" :href="route('receipts.create')">Unggah Struk</x-button>
        </div>

        <x-card>
            @if ($receipts->isEmpty())
                <x-empty-state
                    title="Belum ada struk"
                    description="Unggah foto struk untuk membaca detail transaksi secara otomatis."
                >
                    <x-button variant="secondary" :href="route('receipts.create')">Unggah Struk</x-button>
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                <th scope="col" class="px-3 py-3">Tanggal</th>
                                <th scope="col" class="px-3 py-3">Toko</th>
                                <th scope="col" class="px-3 py-3">Total</th>
                                <th scope="col" class="px-3 py-3">Status</th>
                                <th scope="col" class="px-3 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($receipts as $receipt)
                                <tr class="hover:bg-neutral-50/60">
                                    <td class="whitespace-nowrap px-3 py-3 text-neutral-600">
                                        {{ $receipt->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-neutral-900">
                                        {{ $receipt->merchant ?: 'Belum terbaca' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-neutral-900">
                                        @if ($receipt->total !== null)
                                            <x-money :amount="$receipt->total" />
                                        @else
                                            <span class="text-neutral-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <x-badge :variant="$receipt->status->badgeVariant()">
                                            {{ $receipt->status->label() }}
                                        </x-badge>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-right">
                                        <a
                                            href="{{ route('receipts.show', $receipt) }}"
                                            class="font-medium text-emerald-700 hover:text-emerald-800"
                                        >Lihat</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $receipts->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
