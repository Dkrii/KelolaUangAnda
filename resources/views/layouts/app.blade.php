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
                        class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-xs font-medium text-neutral-700 shadow-2xs hover:bg-neutral-50 transition"
                    >
                        <svg class="h-4 w-4 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main slot -->
        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))
                    <div class="mb-6">
                        <x-alert type="success">
                            {{ session('status') }}
                        </x-alert>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
