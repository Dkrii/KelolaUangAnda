<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Kelola Uang Anda') }} - Personal Finance Management</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-neutral-900 bg-white">
    <!-- Navigation -->
    <header class="border-b border-neutral-200/80 bg-white sticky top-0 z-30">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white font-black text-base shadow-sm">
                    K
                </span>
                <span class="text-base font-bold tracking-tight text-neutral-900">Kelola Uang Anda</span>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition"
                    >
                        Buka Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="text-sm font-medium text-neutral-700 hover:text-neutral-900 px-3 py-2"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition"
                    >
                        Daftar Gratis
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main>
        <section class="py-20 sm:py-28 bg-gradient-to-b from-emerald-50/40 to-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 mb-6">
                    Personal Finance Management
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-neutral-900 sm:text-6xl max-w-3xl mx-auto">
                    Kendalikan kondisi finansial Anda secara terstruktur.
                </h1>
                <p class="mt-6 text-base sm:text-lg text-neutral-600 max-w-2xl mx-auto">
                    Catat pemasukan, pengeluaran, saldo berbagai dompet, pantau anggaran bulanan, dan pindai struk belanja otomatis dengan OCR.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        Mulai Sekarang
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-neutral-300 bg-white px-6 py-3 text-sm font-semibold text-neutral-700 shadow-2xs hover:bg-neutral-50 transition"
                    >
                        Masuk ke Akun
                    </a>
                </div>
            </div>
        </section>

        <!-- Feature Highlights -->
        <section class="py-16 border-t border-neutral-200/80 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="rounded-2xl border border-neutral-200/80 p-6 bg-white shadow-xs">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 mb-4">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900">Multi-Dompet Terintegrasi</h3>
                        <p class="mt-2 text-sm text-neutral-500">Kelola uang tunai, rekening bank, dan e-wallet dalam satu tampilan tanpa kebingungan mutasi.</p>
                    </div>

                    <div class="rounded-2xl border border-neutral-200/80 p-6 bg-white shadow-xs">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 mb-4">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900">Kontrol Anggaran Ketat</h3>
                        <p class="mt-2 text-sm text-neutral-500">Tetapkan batas pengeluaran per kategori. Dapatkan peringatan saat mendekati batas anggaran.</p>
                    </div>

                    <div class="rounded-2xl border border-neutral-200/80 p-6 bg-white shadow-xs">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 mb-4">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-neutral-900">Pemindaian Struk (OCR)</h3>
                        <p class="mt-2 text-sm text-neutral-500">Ekstraksi struk belanja otomatis didukung FastAPI + PaddleOCR untuk pencatatan lebih cepat.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Minimal Footer -->
    <footer class="border-t border-neutral-200/80 py-8 bg-neutral-50 text-center text-xs text-neutral-500">
        <p>&copy; {{ date('Y') }} Kelola Uang Anda. Seluruh hak cipta dilindungi.</p>
    </footer>
</body>
</html>
