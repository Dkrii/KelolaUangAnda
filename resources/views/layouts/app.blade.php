<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-neutral-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Kelola Uang Anda') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-neutral-900 bg-neutral-50" x-data="{ sidebarOpen: false }">
    <!-- Off-canvas mobile menu -->
    <div
        x-show="sidebarOpen"
        x-cloak
        class="relative z-50 lg:hidden"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-neutral-900/60 backdrop-blur-xs"
            @click="sidebarOpen = false"
        ></div>

        <div class="fixed inset-0 flex">
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative mr-16 flex w-full max-w-xs flex-1"
            >
                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 shadow-xl">
                    <div class="flex h-16 shrink-0 items-center justify-between border-b border-neutral-200">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-neutral-900">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white font-black text-base shadow-sm">
                                K
                            </span>
                            <span class="text-base tracking-tight">Kelola Uang Anda</span>
                        </a>
                        <button
                            type="button"
                            @click="sidebarOpen = false"
                            class="rounded-lg p-1.5 text-neutral-500 hover:bg-neutral-100"
                            aria-label="Tutup navigasi"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @include('layouts.navigation-links')
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop static sidebar -->
    <aside class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col border-r border-neutral-200/80 bg-white">
        <div class="flex h-16 shrink-0 items-center gap-2 px-6 border-b border-neutral-200/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-neutral-900 hover:text-emerald-700 transition">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white font-black text-base shadow-sm">
                    K
                </span>
                <span class="text-base tracking-tight">Kelola Uang Anda</span>
            </a>
        </div>

        <div class="flex grow flex-col gap-y-5 overflow-y-auto px-4 py-4">
            @include('layouts.navigation-links')
        </div>

        <div class="p-4 border-t border-neutral-200/80">
            <div class="flex items-center justify-between gap-3 rounded-lg p-2 hover:bg-neutral-50 transition">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-neutral-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-neutral-500">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-lg p-1.5 text-neutral-400 hover:text-rose-600 hover:bg-rose-50 transition"
                        title="Keluar"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content wrapper -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-neutral-200/80 bg-white/95 px-4 backdrop-blur-xs sm:gap-x-6 sm:px-6 lg:px-8">
            <button
                type="button"
                @click="sidebarOpen = true"
                class="rounded-lg p-2 text-neutral-700 hover:bg-neutral-100 lg:hidden"
                aria-label="Buka navigasi"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex flex-1 items-center justify-between gap-x-4">
                <div>
                    @if (isset($header))
                        <h1 class="text-lg font-semibold tracking-tight text-neutral-900 sm:text-xl">
                            {{ $header }}
                        </h1>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-2.5 py-1.5 text-xs font-medium text-neutral-700 shadow-2xs hover:bg-neutral-50 transition sm:px-3"
                        aria-label="Buka profil {{ auth()->user()->name }}"
                    >
                        <svg class="h-4 w-4 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main slot -->
        <main class="flex-1 px-4 pt-6 pb-28 sm:px-6 sm:pt-8 lg:px-8 lg:pb-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))
                    <div class="mb-6">
                        <x-alert type="success">
                            {{ session('status') }}
                        </x-alert>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6">
                        <x-alert type="danger">
                            {{ session('error') }}
                        </x-alert>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    <div
        x-data="{ open: false, message: '', form: null }"
        x-on:open-delete-modal.window="open = true; message = $event.detail.message; form = $event.detail.form; $nextTick(() => $refs.confirmButton.focus())"
        x-on:keydown.escape.window="open = false; form = null"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-modal-title"
    >
        <div
            class="absolute inset-0 bg-neutral-900/60 backdrop-blur-xs"
            x-on:click="open = false; form = null"
        ></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v4m0 4h.01M10.29 3.86l-8.2 14.14A2 2 0 003.83 21h16.34a2 2 0 001.74-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 id="delete-modal-title" class="text-base font-semibold text-neutral-900">Hapus data ini?</h2>
                    <p class="mt-1 text-sm leading-relaxed text-neutral-600" x-text="message || 'Tindakan ini tidak bisa dibatalkan.'"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <x-button variant="secondary" type="button" x-on:click="open = false; form = null" class="w-full sm:w-auto">
                    Batal
                </x-button>
                <x-button
                    variant="danger"
                    type="button"
                    x-ref="confirmButton"
                    x-on:click="if (form) { form.dataset.confirmed = 'true'; form.requestSubmit(); }"
                    class="w-full sm:w-auto"
                >
                    Ya, hapus
                </x-button>
            </div>
        </div>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-neutral-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden" aria-label="Navigasi utama">
        @php
            $bottomNavItems = [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['label' => 'Transaksi', 'route' => 'transactions.index', 'active' => request()->routeIs('transactions.*', 'transfers.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>'],
                ['label' => 'Dompet', 'route' => 'wallets.index', 'active' => request()->routeIs('wallets.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'],
                ['label' => 'Anggaran', 'route' => 'budgets.index', 'active' => request()->routeIs('budgets.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                ['label' => 'Scan', 'route' => 'receipts.index', 'active' => request()->routeIs('receipts.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ];
        @endphp

        <div class="grid grid-cols-5">
            @foreach ($bottomNavItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    @if ($item['active']) aria-current="page" @endif
                    class="flex min-h-14 flex-col items-center justify-center gap-1 px-1 text-[11px] font-medium transition {{ $item['active'] ? 'text-emerald-700' : 'text-neutral-500 hover:text-neutral-700' }}"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        {!! $item['icon'] !!}
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
</body>
</html>
