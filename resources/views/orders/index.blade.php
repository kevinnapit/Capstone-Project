<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h1 class="text-xl font-semibold text-gray-900">Pesanan</h1><p class="mt-1 text-sm text-gray-500">Daftar pesanan langsung dan WhatsApp.</p></div>
            @can('orders.create')
                <a href="{{ route('orders.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-800">Buat pesanan</a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('orders.index') }}" class="mb-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
        <input name="search" value="{{ $search }}" placeholder="Cari nomor atau pelanggan..." class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-gray-800 px-5 py-2 text-sm font-medium text-white hover:bg-gray-900">Filter</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Nomor</th><th class="px-5 py-3">Pelanggan</th><th class="px-5 py-3">Channel</th><th class="px-5 py-3">Item</th><th class="px-5 py-3">Total</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Dibuat oleh</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    @php
                        $statusClass = match ($order->status) {
                            \App\Enums\OrderStatus::Draft => 'bg-gray-100 text-gray-700',
                            \App\Enums\OrderStatus::Confirmed => 'bg-blue-100 text-blue-800',
                            \App\Enums\OrderStatus::Processing => 'bg-amber-100 text-amber-800',
                            \App\Enums\OrderStatus::Completed => 'bg-green-100 text-green-800',
                            \App\Enums\OrderStatus::Cancelled => 'bg-red-100 text-red-800',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50"><td class="whitespace-nowrap px-5 py-4"><a href="{{ route('orders.show', $order) }}" class="font-medium text-blue-700 hover:underline">{{ $order->order_number }}</a><div class="text-xs text-gray-500">{{ $order->ordered_at->format('d/m/Y H:i') }}</div></td><td class="px-5 py-4">{{ $order->customer?->name ?? 'Umum' }}</td><td class="px-5 py-4">{{ $order->channel->name }}</td><td class="px-5 py-4">{{ $order->items_count }}</td><td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $order->status->label() }}</span></td><td class="px-5 py-4">{{ $order->creator->name }}</td></tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="border-t border-gray-200 px-6 py-4">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
