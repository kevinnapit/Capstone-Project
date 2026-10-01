<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan aktivitas usaha fotokopi hari ini.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Pesanan Hari Ini', 'value' => number_format($ordersToday)],
            ['label' => 'Sedang Diproses', 'value' => number_format($processingOrders)],
            ['label' => 'Selesai Hari Ini', 'value' => number_format($completedToday)],
            ['label' => 'Stok Menipis', 'value' => number_format($lowStockCount)],
        ] as $summary)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500">{{ $summary['label'] }}</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">{{ $summary['value'] }}</p>
            </div>
        @endforeach
        @can('reports.view')
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-gray-500">Penjualan Hari Ini</p><p class="mt-3 text-2xl font-bold text-green-700">Rp {{ number_format($salesToday, 0, ',', '.') }}</p></div>
        @endcan
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4"><h2 class="font-semibold text-gray-900">Pesanan terbaru</h2><a href="{{ route('orders.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Lihat semua</a></div>
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-600"><tr><th class="px-5 py-3">Nomor</th><th class="px-5 py-3">Pelanggan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Total</th></tr></thead><tbody class="divide-y divide-gray-200">
                @forelse ($recentOrders as $order)
                    <tr><td class="px-5 py-4"><a href="{{ route('orders.show', $order) }}" class="font-medium text-blue-700 hover:underline">{{ $order->order_number }}</a><p class="text-xs text-gray-500">{{ $order->ordered_at->format('d/m H:i') }}</p></td><td class="px-5 py-4">{{ $order->customer?->name ?? 'Umum' }}</td><td class="px-5 py-4">{{ $order->status->label() }}</td><td class="px-5 py-4 text-right font-medium">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h2 class="font-semibold text-gray-900">Stok menipis</h2><div class="mt-4 divide-y divide-gray-100">
            @forelse ($lowStockProducts as $product)
                <div class="flex items-center justify-between gap-3 py-3"><div><p class="text-sm font-medium text-gray-900">{{ $product->name }}</p><p class="text-xs text-gray-500">Minimum {{ number_format((float) $product->minimum_stock, 2, ',', '.') }}</p></div><span class="text-sm font-semibold text-red-700">{{ number_format((float) $product->current_stock, 2, ',', '.') }} {{ $product->unit->symbol }}</span></div>
            @empty
                <p class="py-6 text-center text-sm text-gray-500">Semua stok aman.</p>
            @endforelse
        </div></section>
    </div>
</x-app-layout>
