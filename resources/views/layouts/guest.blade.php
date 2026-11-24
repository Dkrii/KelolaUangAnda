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
<body class="h-full font-sans antialiased text-neutral-900 bg-neutral-50">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xl font-bold tracking-tight text-neutral-900 hover:text-emerald-700 transition">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white font-black text-lg shadow-sm">
                    K
                </span>
                <span>{{ config('app.name', 'Kelola Uang Anda') }}</span>
            </a>
            @if (isset($header))
                <h2 class="mt-6 text-2xl font-semibold tracking-tight text-neutral-900">
                    {{ $header }}
                </h2>
            @endif
            @if (isset($subtext))
                <p class="mt-1 text-sm text-neutral-500">
                    {{ $subtext }}
                </p>
            @endif
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xs border border-neutral-200/80 sm:rounded-2xl sm:px-10">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
