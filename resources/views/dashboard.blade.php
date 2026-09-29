<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan aktivitas usaha fotokopi hari ini.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Pesanan Hari Ini', 'value' => '0'],
            ['label' => 'Sedang Diproses', 'value' => '0'],
            ['label' => 'Transaksi Selesai', 'value' => '0'],
            ['label' => 'Stok Menipis', 'value' => '0'],
        ] as $summary)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">{{ $summary['label'] }}</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">{{ $summary['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Selamat datang, {{ Auth::user()->name }}</h2>
        <p class="mt-2 text-sm leading-6 text-gray-600">
            Fondasi tampilan dashboard sudah siap. Menu berwarna abu-abu akan diaktifkan setelah route dan modulnya dibuat.
        </p>
    </div>
</x-app-layout>
