<x-app-layout>
    <x-slot:title>Unggah Struk - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Unggah Struk</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            <x-alert type="info" title="Konfirmasi tetap diperlukan">
                Hasil pembacaan struk hanya sebagai usulan. Transaksi pengeluaran dibuat setelah Anda meninjau dan
                mengonfirmasi sendiri data yang terbaca.
            </x-alert>

            <form
                method="POST"
                action="{{ route('receipts.store') }}"
                enctype="multipart/form-data"
                class="mt-6 space-y-5"
            >
                @csrf

                <div>
                    <x-label for="image" value="Foto Struk" />
                    <x-input
                        id="image"
                        name="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        required
                        :error="$errors->has('image')"
                        class="file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-emerald-700"
                    />
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    <p class="mt-2 text-xs text-neutral-500">Format JPG, PNG, atau WebP. Ukuran maksimal 10 MB.</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-neutral-200/80 pt-5">
                    <x-button variant="secondary" :href="route('receipts.index')">Batal</x-button>
                    <x-button type="submit" variant="primary">Unggah &amp; Pindai</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
