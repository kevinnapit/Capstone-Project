<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1><p class="mt-1 text-sm text-gray-500">Kelola referensi yang digunakan pada tarif layanan.</p></div>
            <a href="{{ route($routePrefix.'.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Tambah data</a>
        </div>
    </x-slot>
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->has('item'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('item') }}</div>
    @endif
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-6 py-3">Kode</th><th class="px-6 py-3">Nama</th><th class="px-6 py-3">Tarif</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($items as $item)
                    <tr><td class="px-6 py-4 font-mono text-xs">{{ $item->code }}</td><td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td><td class="px-6 py-4">{{ $item->service_prices_count }}</td><td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="px-6 py-4 text-right"><a href="{{ route($routePrefix.'.edit', $item) }}" class="font-medium text-blue-700 hover:underline">Edit</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-gray-200 px-6 py-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
