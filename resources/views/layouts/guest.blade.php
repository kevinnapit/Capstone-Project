<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Fotokopi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-gray-900 antialiased">
        <main class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1.05fr)_minmax(30rem,0.95fr)]">
            <section class="relative hidden overflow-hidden bg-gradient-to-br from-blue-950 via-blue-800 to-cyan-700 p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16">
                <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full border border-white/10 bg-white/5"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full border border-white/10 bg-cyan-300/10"></div>
                <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rotate-12 rounded-[3rem] border border-white/10"></div>

                <a href="/" class="relative flex w-fit items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-bold text-blue-800 shadow-lg shadow-blue-950/20">FC</span>
                    <span><span class="block text-lg font-bold">Sistem Fotokopi</span><span class="block text-xs text-blue-100">Operasional usaha dalam satu tempat</span></span>
                </a>

                <div class="relative max-w-xl">
                    <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-100">Workspace operasional</span>
                    <h1 class="mt-6 text-4xl font-bold leading-tight xl:text-5xl">Kelola pesanan, pembayaran, dan stok dengan lebih rapi.</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-blue-100">Satu sistem untuk membantu tim mencatat transaksi, memantau pekerjaan, dan memperoleh laporan usaha yang dapat dipercaya.</p>

                    <div class="mt-8 max-w-lg rounded-3xl border border-white/15 bg-white/10 p-3 shadow-2xl shadow-blue-950/20 backdrop-blur-md">
                        <div class="rounded-2xl bg-white p-5 text-gray-900">
                            <div class="flex items-center justify-between"><div><p class="text-xs font-medium uppercase tracking-wider text-gray-400">Preview workspace</p><p class="mt-1 text-xl font-bold">Operasional terkendali</p></div><span class="flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Terintegrasi</span></div>
                            <div class="mt-5 grid grid-cols-3 gap-3">
                                <div class="rounded-xl bg-blue-50 p-3"><p class="text-xs text-blue-600">Pesanan</p><p class="mt-1 text-xl font-bold text-blue-900">24</p></div>
                                <div class="rounded-xl bg-amber-50 p-3"><p class="text-xs text-amber-600">Diproses</p><p class="mt-1 text-xl font-bold text-amber-900">7</p></div>
                                <div class="rounded-xl bg-emerald-50 p-3"><p class="text-xs text-emerald-600">Selesai</p><p class="mt-1 text-xl font-bold text-emerald-900">17</p></div>
                            </div>
                            <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-4 text-xs text-gray-500"><svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V9m5 10V5m5 14v-7m5 7V3" /></svg>Pesanan, stok, dan laporan tersinkron dalam satu alur.</div>
                        </div>
                    </div>
                </div>

                <p class="relative text-xs text-blue-200">&copy; {{ now()->year }} Sistem Manajemen Usaha Fotokopi</p>
            </section>

            <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10 lg:px-14 xl:px-24">
                <div class="w-full max-w-md">
                    <a href="/" class="mb-9 flex items-center gap-3 lg:hidden">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-700 text-lg font-bold text-white shadow-lg shadow-blue-200">FC</span>
                        <span><span class="block font-bold text-gray-900">Sistem Fotokopi</span><span class="block text-xs text-gray-500">Manajemen operasional usaha</span></span>
                    </a>

                    <div class="rounded-3xl border border-gray-200/80 bg-white p-6 shadow-2xl shadow-slate-200/70 sm:p-9">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs leading-5 text-gray-500">Akses terbatas untuk pengguna yang telah didaftarkan oleh administrator.</p>
                </div>
            </section>
        </main>
    </body>
</html>
