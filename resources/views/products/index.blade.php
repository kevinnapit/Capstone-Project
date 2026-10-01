<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h1 class="text-xl font-semibold text-gray-900">Produk</h1><p class="mt-1 text-sm text-gray-500">Kelola barang ATK dan bahan habis pakai.</p></div>
            <a href="{{ route('products.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-800">Tambah produk</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->has('product'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('product') }}</div>
    @endif

    <form method="GET" action="{{ route('products.index') }}" class="mb-4 flex max-w-lg gap-2">
        <label for="search" class="sr-only">Cari produk</label>
        <input id="search" name="search" value="{{ $search }}" placeholder="Cari nama atau SKU..." class="block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">Cari</button>
        @if ($search !== '')
            <a href="{{ route('products.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Produk</th><th class="px-5 py-3">Kategori</th><th class="px-5 py-3">Harga jual</th><th class="px-5 py-3">Stok</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4"><div class="font-medium text-gray-900">{{ $product->name }}</div><div class="text-xs text-gray-500">{{ $product->sku }}</div></td>
                        <td class="px-5 py-4">{{ $product->category->name }}</td>
                        <td class="whitespace-nowrap px-5 py-4">Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-5 py-4"><span class="font-medium {{ $product->current_stock <= $product->minimum_stock ? 'text-red-600' : 'text-gray-900' }}">{{ rtrim(rtrim($product->current_stock, '0'), '.') }} {{ $product->unit->symbol }}</span></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('products.edit', $product) }}" class="font-medium text-blue-700 hover:underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Produk tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="border-t border-gray-200 px-6 py-4">{{ $products->links() }}</div>
    </div>
</x-app-layout>
