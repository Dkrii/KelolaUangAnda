@php
$navItems = [
    [
        'name' => 'Dashboard',
        'route' => 'dashboard',
        'active' => request()->routeIs('dashboard'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    ],
    [
        'name' => 'Dompet',
        'route' => 'wallets.index',
        'active' => request()->routeIs('wallets.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
    ],
    [
        'name' => 'Transaksi',
        'route' => 'transactions.index',
        'active' => request()->routeIs('transactions.*', 'transfers.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>',
    ],
    [
        'name' => 'Kategori',
        'route' => 'categories.index',
        'active' => request()->routeIs('categories.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
    ],
    [
        'name' => 'Anggaran',
        'route' => 'budgets.index',
        'active' => request()->routeIs('budgets.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
    ],
    [
        'name' => 'Scan Struk (OCR)',
        'route' => 'receipts.index',
        'active' => request()->routeIs('receipts.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ],
    [
        'name' => 'Laporan',
        'route' => 'reports.index',
        'active' => request()->routeIs('reports.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    ],
    [
        'name' => 'Target Tabungan',
        'route' => 'saving-goals.index',
        'active' => request()->routeIs('saving-goals.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
    ],
    [
        'name' => 'Profil & Akun',
        'route' => 'profile.edit',
        'active' => request()->routeIs('profile.*'),
        'available' => true,
        'badge' => null,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ],
];
@endphp

<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-1">
        @foreach ($navItems as $item)
            <li>
                @if ($item['available'])
                    <a
                        href="{{ route($item['route']) }}"
                        class="group flex items-center justify-between gap-x-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'bg-emerald-50 text-emerald-700' : 'text-neutral-700 hover:bg-neutral-100 hover:text-neutral-900' }}"
                    >
                        <div class="flex items-center gap-x-3">
                            <svg class="h-5 w-5 shrink-0 {{ $item['active'] ? 'text-emerald-600' : 'text-neutral-400 group-hover:text-neutral-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $item['icon'] !!}
                            </svg>
                            <span>{{ $item['name'] }}</span>
                        </div>
                    </a>
                @else
                    <div class="flex items-center justify-between gap-x-3 rounded-lg px-3 py-2 text-sm font-medium text-neutral-400 cursor-not-allowed select-none">
                        <div class="flex items-center gap-x-3">
                            <svg class="h-5 w-5 shrink-0 text-neutral-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $item['icon'] !!}
                            </svg>
                            <span>{{ $item['name'] }}</span>
                        </div>
                        @if ($item['badge'])
                            <span class="rounded bg-neutral-100 px-1.5 py-0.5 text-[10px] font-medium text-neutral-500">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
