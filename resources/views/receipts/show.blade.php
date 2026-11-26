<x-app-layout>
    <x-slot:title>Detail Struk - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Detail Struk</x-slot:header>

    @php
        $ocrData = is_array($receipt->ocr_data) ? $receipt->ocr_data : [];
        $warnings = is_array($ocrData['warnings'] ?? null) ? $ocrData['warnings'] : [];
        $suggestedCategoryId = null;
        if (is_string($ocrData['suggested_category'] ?? null)) {
            $suggestedCategoryId = $categories->firstWhere('name', $ocrData['suggested_category'])?->id;
        }
    @endphp

    <div
        class="space-y-6"
        data-receipt-status="{{ $receipt->status->value }}"
        data-receipt-status-url="{{ route('receipts.status', $receipt) }}"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-button variant="secondary" :href="route('receipts.index')">&larr; Kembali</x-button>
            <x-badge :variant="$receipt->status->badgeVariant()">{{ $receipt->status->label() }}</x-badge>
        </div>

        @if ($receipt->status === \App\Enums\ReceiptStatus::Pending)
            <x-alert type="info" title="Sedang diproses">
                <div
                    class="flex items-center gap-2"
                    role="status"
                    aria-live="polite"
                    data-ocr-loading
                >
                    <span
                        class="size-4 shrink-0 animate-spin rounded-full border-2 border-blue-200 border-t-blue-600 motion-reduce:animate-none"
                        aria-hidden="true"
                    ></span>
                    <span>Membaca struk dengan OCR. Halaman ini akan diperbarui otomatis setelah selesai.</span>
                </div>
            </x-alert>
        @endif

        @if ($receipt->status === \App\Enums\ReceiptStatus::Failed)
            <x-alert type="danger" title="Pemindaian gagal">
                {{ $ocrData['error'] ?? 'Layanan OCR tidak dapat memproses gambar ini.' }}
            </x-alert>

            <form method="POST" action="{{ route('receipts.retry', $receipt) }}">
                @csrf
                <x-button type="submit" variant="secondary">Proses Ulang</x-button>
            </form>
        @endif

        @if ($receipt->status === \App\Enums\ReceiptStatus::Confirmed && $receipt->transaction)
            <x-alert type="success" title="Struk dikonfirmasi">
                Transaksi sudah dibuat.
                <a
                    href="{{ route('transactions.show', $receipt->transaction) }}"
                    class="font-medium underline underline-offset-2"
                >Lihat transaksi</a>.
            </x-alert>
        @endif

        @if ($receipt->status === \App\Enums\ReceiptStatus::Review)
            <x-alert type="info" title="Periksa hasil pembacaan">
                Periksa data di bawah ini. Anda wajib mengonfirmasi manual sebelum transaksi pengeluaran dibuat.
            </x-alert>

            @if (count($warnings) > 0)
                <x-alert type="warning" title="Perhatikan hasil OCR">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($warnings as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <x-card>
                <h2 class="text-sm font-semibold text-neutral-900">Gambar Struk</h2>
                <div class="mt-4 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-50">
                    <img
                        src="{{ route('receipts.image', $receipt) }}"
                        alt="Foto struk"
                        class="mx-auto max-h-[36rem] w-full object-contain"
                    >
                </div>
                <p class="mt-3 text-xs text-neutral-500">{{ $receipt->original_name }}</p>
            </x-card>

            <div class="space-y-6">
                <x-card>
                    <h2 class="text-sm font-semibold text-neutral-900">Hasil Pembacaan</h2>
                    <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Toko</dt>
                            <dd class="mt-1 text-sm text-neutral-900">{{ $receipt->merchant ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Tanggal</dt>
                            <dd class="mt-1 text-sm text-neutral-900">
                                {{ $receipt->transaction_date?->format('d F Y') ?? '-' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wider text-neutral-500">Total Terbaca</dt>
                            <dd class="mt-1 text-xl font-semibold text-neutral-900">
                                @if ($receipt->total !== null)
                                    <x-money :amount="$receipt->total" />
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if ($receipt->receiptItems->isNotEmpty())
                        <div class="mt-5 overflow-x-auto border-t border-neutral-200/80 pt-4">
                            <table class="min-w-full divide-y divide-neutral-100 text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                        <th scope="col" class="py-2 pr-3">Item</th>
                                        <th scope="col" class="py-2 pr-3 text-right">Qty</th>
                                        <th scope="col" class="py-2 pr-3 text-right">Harga</th>
                                        <th scope="col" class="py-2 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @foreach ($receipt->receiptItems as $item)
                                        <tr>
                                            <td class="py-2 pr-3 text-neutral-900">{{ $item->name }}</td>
                                            <td class="py-2 pr-3 text-right text-neutral-600">{{ $item->quantity }}</td>
                                            <td class="py-2 pr-3 text-right text-neutral-600">
                                                <x-money :amount="$item->unit_price" />
                                            </td>
                                            <td class="py-2 text-right text-neutral-900">
                                                <x-money :amount="$item->subtotal" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if (is_string($ocrData['raw_text'] ?? null) && $ocrData['raw_text'] !== '')
                        <details class="mt-5 border-t border-neutral-200/80 pt-4">
                            <summary class="cursor-pointer text-xs font-medium text-neutral-600">Teks mentah OCR</summary>
                            <pre class="mt-2 max-h-48 overflow-auto rounded-lg bg-neutral-50 p-3 text-xs text-neutral-600">{{ $ocrData['raw_text'] }}</pre>
                        </details>
                    @endif
                </x-card>

                @if ($receipt->status === \App\Enums\ReceiptStatus::Review)
                    <x-card>
                        <h2 class="text-sm font-semibold text-neutral-900">Konfirmasi Transaksi</h2>

                        @if ($wallets->isEmpty() || $categories->isEmpty())
                            <div class="mt-4">
                                <x-alert type="warning" title="Data pendukung belum ada">
                                    Tambahkan dompet aktif dan kategori pengeluaran terlebih dahulu.
                                </x-alert>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('receipts.confirm', $receipt) }}"
                            class="mt-4 space-y-4"
                        >
                            @csrf

                            <div>
                                <x-label for="wallet_id" value="Dompet" />
                                <x-select
                                    id="wallet_id"
                                    name="wallet_id"
                                    required
                                    :error="$errors->has('wallet_id')"
                                >
                                    <option value="">Pilih dompet</option>
                                    @foreach ($wallets as $wallet)
                                        <option value="{{ $wallet->id }}" @selected(old('wallet_id') == $wallet->id)>
                                            {{ $wallet->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                                <x-input-error :messages="$errors->get('wallet_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="category_id" value="Kategori" />
                                <x-select
                                    id="category_id"
                                    name="category_id"
                                    required
                                    :error="$errors->has('category_id')"
                                >
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected(old('category_id', $suggestedCategoryId) == $category->id)
                                        >{{ $category->name }}</option>
                                    @endforeach
                                </x-select>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="amount" value="Nominal" />
                                <x-input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    data-nominal
                                    :value="old('amount', $receipt->total !== null ? number_format((float) $receipt->total, 2, '.', '') : '')"
                                    :error="$errors->has('amount')"
                                />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="transaction_date" value="Tanggal" />
                                <x-input
                                    id="transaction_date"
                                    name="transaction_date"
                                    type="date"
                                    required
                                    :value="old('transaction_date', $receipt->transaction_date?->toDateString() ?? '')"
                                    :error="$errors->has('transaction_date')"
                                />
                                <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="description" value="Keterangan" />
                                <x-textarea
                                    id="description"
                                    name="description"
                                    rows="2"
                                    :error="$errors->has('description')"
                                >{{ old('description', $receipt->merchant ?: '') }}</x-textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="flex justify-end border-t border-neutral-200/80 pt-4">
                                <x-button type="submit" variant="primary" :disabled="$wallets->isEmpty() || $categories->isEmpty()">
                                    Konfirmasi &amp; Buat Transaksi
                                </x-button>
                            </div>
                        </form>
                    </x-card>
                @endif
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('receipts.destroy', $receipt) }}"
            data-confirm-delete="Hapus struk ini? Transaksi yang sudah dibuat tetap tersimpan."
        >
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">Hapus Struk</x-button>
        </form>
    </div>
</x-app-layout>
