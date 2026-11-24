<x-guest-layout>
    <x-slot:title>Masuk - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Masuk ke akun Anda</x-slot:header>
    <x-slot:subtext>
        Atau
        <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-700">
            daftar akun baru
        </a>
    </x-slot:subtext>

    @if (session('status'))
        <div class="mb-4">
            <x-alert type="success">{{ session('status') }}</x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-label for="email" value="Email" />
            <x-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                :error="$errors->has('email')"
                placeholder="nama@email.com"
                class="mt-1"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-label for="password" value="Kata Sandi" />
            <x-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                :error="$errors->has('password')"
                placeholder="••••••••"
                class="mt-1"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center gap-2">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    class="h-4 w-4 rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500"
                >
                <span class="text-xs text-neutral-600">Ingat saya</span>
            </label>
        </div>

        <div>
            <x-button type="submit" class="w-full">
                Masuk
            </x-button>
        </div>
    </form>
</x-guest-layout>
