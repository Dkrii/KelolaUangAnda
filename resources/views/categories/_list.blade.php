<x-card>
    <div class="flex items-center justify-between border-b border-neutral-200/80 pb-4">
        <div>
            <h2 class="text-sm font-semibold text-neutral-900">{{ $title }}</h2>
            <p class="text-xs text-neutral-500">{{ $categories->count() }} kategori</p>
        </div>
        <a
            href="{{ route('categories.create', ['type' => $type->value]) }}"
            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50"
        >
            Tambah
        </a>
    </div>

    @if ($categories->isEmpty())
        <p class="py-8 text-center text-sm text-neutral-500">
            Belum ada kategori {{ $type->label() }}.
        </p>
    @else
        <ul role="list" class="divide-y divide-neutral-100">
            @foreach ($categories as $category)
                <li class="flex items-center justify-between gap-3 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span
                            class="h-2.5 w-2.5 shrink-0 rounded-full"
                            style="background-color: {{ $category->color ?? '#6b7280' }};"
                        ></span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-neutral-900">{{ $category->name }}</p>
                            <p class="text-xs text-neutral-500">{{ $category->transactions_count }} transaksi</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <a
                            href="{{ route('categories.edit', $category) }}"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900"
                        >
                            Ubah
                        </a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                Hapus
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-card>
