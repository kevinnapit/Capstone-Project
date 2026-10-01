<x-guest-layout>
    <div class="mb-7">
        <div class="mb-5 flex items-center justify-between">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm4 14v-2a6 6 0 0 0-6-6H9a6 6 0 0 0-6 6v2m15-8v6m-3-3h6" /></svg></span>
            <span class="flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6" /></svg>Koneksi aman</span>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Selamat datang kembali</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">Masuk menggunakan akun yang telah diberikan administrator.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-lg bg-green-50 p-3 text-sm text-green-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false, capsLock: false, submitting: false }" @submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat email" class="font-medium text-gray-700" />
            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6.5 12 13l9-6.5M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" /></svg></span>
                <x-text-input id="email" class="block w-full py-3 pl-10" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5">
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Password" class="font-medium text-gray-700" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-blue-700 hover:text-blue-800 hover:underline" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <div class="relative mt-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V8a5 5 0 0 1 10 0v2m-11 0h12v10H6V10Z" /></svg></span>
                <x-text-input id="password" class="block w-full py-3 pl-10 pr-11" x-bind:type="showPassword ? 'text' : 'password'" @keyup="capsLock = $event.getModifierState('CapsLock')" @keydown="capsLock = $event.getModifierState('CapsLock')" name="password" placeholder="Masukkan password" required autocomplete="current-password" />
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-700" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a16.7 16.7 0 0 1-2.1 2.8M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 6 9.5 6a9.6 9.6 0 0 0 3.1-.5" /></svg>
                </button>
            </div>
            <p x-show="capsLock" x-cloak class="mt-2 flex items-center gap-1.5 text-xs font-medium text-amber-700"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3 7 7h-4v7H9v-7H5l7-7Z" /></svg>Caps Lock sedang aktif</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ml-2 text-sm text-gray-600">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <button type="submit" :disabled="submitting" class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-75">
            <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3Z"></path></svg>
            <span x-text="submitting ? 'Memverifikasi...' : 'Masuk ke sistem'"></span>
            <svg x-show="!submitting" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" /></svg>
        </button>

        <div class="mt-6 flex items-center justify-center gap-2 border-t border-gray-100 pt-5 text-xs text-gray-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 22s8-3 8-10V5l-8-3-8 3v7c0 7 8 10 8 10Z" /></svg>Sesi dan kredensial Anda dilindungi</div>
    </form>
</x-guest-layout>
