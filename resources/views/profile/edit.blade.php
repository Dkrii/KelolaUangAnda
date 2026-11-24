<x-app-layout>
    <x-slot:title>Profil Pengguna - {{ config('app.name') }}</x-slot:title>
    <x-slot:header>Profil & Preferensi</x-slot:header>

    <div class="space-y-6 max-w-4xl">
        <!-- Informasi Profil -->
        <x-card>
            <div class="border-b border-neutral-200/80 pb-4 mb-6">
                <h2 class="text-base font-semibold text-neutral-900">Informasi Pribadi</h2>
                <p class="mt-1 text-xs text-neutral-500">Perbarui nama, email, dan preferensi regional akun Anda.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5 max-w-xl">
                @csrf
                @method('PATCH')

                <div>
                    <x-label for="name" value="Nama Lengkap" />
                    <x-input
                        id="name"
                        type="text"
                        name="name"
                        :value="old('name', $user->name)"
                        required
                        autofocus
                        autocomplete="name"
                        :error="$errors->has('name')"
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
                        :value="old('email', $user->email)"
                        required
                        autocomplete="username"
                        :error="$errors->has('email')"
                        class="mt-1"
                    />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-label for="currency" value="Mata Uang Default" />
                        <x-input
                            id="currency"
                            type="text"
                            name="currency"
                            :value="old('currency', $user->currency ?? 'IDR')"
                            maxlength="3"
                            :error="$errors->has('currency')"
                            class="mt-1 uppercase"
                            placeholder="IDR"
                        />
                        <x-input-error :messages="$errors->get('currency')" />
                    </div>

                    <div>
                        <x-label for="timezone" value="Zona Waktu" />
                        <x-input
                            id="timezone"
                            type="text"
                            name="timezone"
                            :value="old('timezone', $user->timezone ?? 'Asia/Jakarta')"
                            :error="$errors->has('timezone')"
                            class="mt-1"
                            placeholder="Asia/Jakarta"
                        />
                        <x-input-error :messages="$errors->get('timezone')" />
                    </div>
                </div>

                <div class="pt-2">
                    <x-button type="submit">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </x-card>

        <!-- Perbarui Kata Sandi -->
        <x-card>
            <div class="border-b border-neutral-200/80 pb-4 mb-6">
                <h2 class="text-base font-semibold text-neutral-900">Perbarui Kata Sandi</h2>
                <p class="mt-1 text-xs text-neutral-500">Pastikan akun menggunakan kata sandi yang panjang dan aman.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5 max-w-xl">
                @csrf
                @method('PUT')

                <div>
                    <x-label for="current_password" value="Kata Sandi Saat Ini" />
                    <x-input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        :error="$errors->has('current_password')"
                        placeholder="••••••••"
                        class="mt-1"
                    />
                    <x-input-error :messages="$errors->get('current_password')" />
                </div>

                <div>
                    <x-label for="password" value="Kata Sandi Baru" />
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
                    <x-label for="password_confirmation" value="Konfirmasi Kata Sandi Baru" />
                    <x-input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        :error="$errors->has('password_confirmation')"
                        placeholder="Ulangi kata sandi baru"
                        class="mt-1"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <div class="pt-2">
                    <x-button type="submit">
                        Ganti Kata Sandi
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
