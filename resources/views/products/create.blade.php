<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-900">Tambah Produk</h1>
    </x-slot>

    @if ($categories->isEmpty() || $units->isEmpty())
        <div class="mb-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
            Tambahkan minimal satu kategori dan satu satuan sebelum membuat produk.
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('products._form', ['method' => 'POST'])
    </form>
</x-app-layout>
