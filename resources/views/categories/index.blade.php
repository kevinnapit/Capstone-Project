<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><div><h1 class="text-xl font-semibold text-gray-900">Kategori Produk</h1><p class="mt-1 text-sm text-gray-500">Kelompokkan barang ATK dan bahan operasional.</p></div><a href="{{ route('categories.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Tambah kategori</a></div></x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->has('category'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('category') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-6 py-3">Nama</th><th class="px-6 py-3">Deskripsi</th><th class="px-6 py-3">Produk</th><th class="px-6 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($categories as $category)
                    <tr><td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td><td class="px-6 py-4">{{ $category->description ?: '-' }}</td><td class="px-6 py-4">{{ $category->products_count }}</td><td class="px-6 py-4 text-right"><a href="{{ route('categories.edit', $category) }}" class="font-medium text-blue-700 hover:underline">Edit</a></td></tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="border-t border-gray-200 px-6 py-4">{{ $categories->links() }}</div>
    </div>
</x-app-layout>
