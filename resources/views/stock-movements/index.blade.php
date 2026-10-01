<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-semibold text-gray-900">Pergerakan stok</h1><p class="mt-1 text-sm text-gray-500">Riwayat mutasi dan penyesuaian stok produk.</p></div></x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <section class="h-fit rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900">Penyesuaian manual</h2>
            <p class="mt-1 text-sm text-gray-500">Alasan wajib dicatat dan stok tidak boleh negatif.</p>
            <form method="POST" action="{{ route('stock-movements.store') }}" class="mt-4 space-y-3">
                @csrf
                <x-searchable-select name="product_id" :options="$products->mapWithKeys(fn ($product) => [$product->id => $product->name.' — '.number_format((float) $product->current_stock, 2, ',', '.').' '.$product->unit->symbol])" :selected="old('product_id')" placeholder="Pilih produk" required />
                <x-searchable-select name="direction" :options="['in' => 'Stok masuk', 'out' => 'Stok keluar']" :selected="old('direction', 'in')" placeholder="Pilih arah" required />
                <input type="number" name="quantity" value="{{ old('quantity') }}" min="0.01" step="0.01" required placeholder="Jumlah" class="w-full rounded-lg border-gray-300 text-sm">
                <textarea name="notes" rows="3" maxlength="500" required placeholder="Alasan penyesuaian" class="w-full rounded-lg border-gray-300 text-sm">{{ old('notes') }}</textarea>
                <button class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800">Simpan penyesuaian</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Produk</th><th class="px-5 py-3">Jenis</th><th class="px-5 py-3 text-right">Jumlah</th><th class="px-5 py-3 text-right">Saldo</th><th class="px-5 py-3">Catatan</th></tr></thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr><td class="whitespace-nowrap px-5 py-4">{{ $movement->occurred_at->format('d/m/Y H:i') }}</td><td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $movement->product->name }}</p><p class="text-xs">{{ $movement->creator->name }}</p></td><td class="px-5 py-4">{{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}</td><td class="whitespace-nowrap px-5 py-4 text-right {{ $movement->direction === 'in' ? 'text-green-700' : 'text-red-700' }}">{{ $movement->direction === 'in' ? '+' : '-' }}{{ number_format((float) $movement->quantity, 2, ',', '.') }}</td><td class="whitespace-nowrap px-5 py-4 text-right">{{ number_format((float) $movement->stock_before, 2, ',', '.') }} → {{ number_format((float) $movement->stock_after, 2, ',', '.') }}</td><td class="max-w-xs px-5 py-4">{{ $movement->notes }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada pergerakan stok.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="border-t border-gray-200 px-6 py-4">{{ $movements->links() }}</div>
        </section>
    </div>
</x-app-layout>
