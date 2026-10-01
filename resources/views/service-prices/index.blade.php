<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><div><h1 class="text-xl font-semibold text-gray-900">Tarif Layanan</h1><p class="mt-1 text-sm text-gray-500">Tarif bertanggal menjaga histori harga transaksi.</p></div><a href="{{ route('service-prices.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white">Tambah tarif</a></div></x-slot>
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Layanan</th><th class="px-5 py-3">Kertas</th><th class="px-5 py-3">Varian</th><th class="px-5 py-3">Harga</th><th class="px-5 py-3">Berlaku</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($prices as $price)
                <tr><td class="px-5 py-4 font-medium text-gray-900">{{ $price->serviceType->name }}</td><td class="px-5 py-4">{{ $price->paperType->code }}</td><td class="px-5 py-4">{{ $price->printMode?->name ?? $price->side_mode->label() }}</td><td class="whitespace-nowrap px-5 py-4">Rp {{ number_format((float) $price->price, 0, ',', '.') }}</td><td class="whitespace-nowrap px-5 py-4">{{ $price->effective_from->format('d/m/Y') }}<br><span class="text-xs text-gray-400">s.d. {{ $price->effective_until?->format('d/m/Y') ?? 'seterusnya' }}</span></td><td class="px-5 py-4">{{ $price->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="px-5 py-4 text-right"><a href="{{ route('service-prices.edit', $price) }}" class="font-medium text-blue-700">Edit</a></td></tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Belum ada tarif.</td></tr>
            @endforelse
        </tbody>
    </table></div><div class="border-t px-6 py-4">{{ $prices->links() }}</div></div>
</x-app-layout>
