<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-semibold text-gray-900">Laporan Penjualan</h1><p class="mt-1 text-sm text-gray-500">Pesanan selesai berdasarkan tanggal penyelesaian.</p></div></x-slot>

    <form method="GET" class="mb-5 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_auto]">
        <div><label class="mb-1 block text-sm font-medium text-gray-700">Dari tanggal</label><input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
        <div><label class="mb-1 block text-sm font-medium text-gray-700">Sampai tanggal</label><input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
        <button class="self-end rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800">Tampilkan</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Total Penjualan</p><p class="mt-2 text-2xl font-bold text-green-700">Rp {{ number_format($totalSales, 0, ',', '.') }}</p></div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Transaksi Selesai</p><p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($transactionCount) }}</p></div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">Rata-rata Transaksi</p><p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ number_format($averageSale, 0, ',', '.') }}</p></div>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600"><thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Pesanan</th><th class="px-5 py-3">Pelanggan</th><th class="px-5 py-3">Channel</th><th class="px-5 py-3">Selesai</th><th class="px-5 py-3 text-right">Total</th></tr></thead><tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    <tr><td class="px-5 py-4"><a href="{{ route('orders.show', $order) }}" class="font-medium text-blue-700 hover:underline">{{ $order->order_number }}</a></td><td class="px-5 py-4">{{ $order->customer?->name ?? 'Umum' }}</td><td class="px-5 py-4">{{ $order->channel->name }}</td><td class="whitespace-nowrap px-5 py-4">{{ $order->completed_at->format('d/m/Y H:i') }}</td><td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-900">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Tidak ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody></table></div><div class="border-t border-gray-200 px-5 py-4">{{ $orders->links() }}</div>
        </section>
        <section class="h-fit rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h2 class="font-semibold text-gray-900">Metode pembayaran</h2><div class="mt-3 divide-y divide-gray-100">
            @forelse ($paymentBreakdown as $row)
                <div class="flex justify-between gap-3 py-3 text-sm"><span class="text-gray-600">{{ $row->paymentMethod->name }}</span><span class="font-medium text-gray-900">Rp {{ number_format((float) $row->total, 0, ',', '.') }}</span></div>
            @empty
                <p class="py-5 text-center text-sm text-gray-500">Belum ada pembayaran.</p>
            @endforelse
        </div></section>
    </div>
</x-app-layout>
