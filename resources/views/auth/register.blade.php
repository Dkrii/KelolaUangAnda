<x-guest-layout>
    <x-slot:title>Daftar - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Buat akun baru</x-slot:header>
    <x-slot:subtext>
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-700">
            masuk sekarang
        </a>
    </x-slot:subtext>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-label for="name" value="Nama Lengkap" />
            <x-input
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                :error="$errors->has('name')"
                placeholder="Budi Santoso"
                class="mt-1"
            />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-label for="email" value="Email" />
            <x-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
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
                autocomplete="new-password"
                :error="$errors->has('password')"
                placeholder="Minimal 8 karakter"
                class="mt-1"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                :error="$errors->has('password_confirmation')"
                placeholder="Ulangi kata sandi"
                class="mt-1"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div>
            <x-button type="submit" class="w-full">
                Daftar Akun
            </x-button>
        </div>
    </form>
</x-guest-layout>
