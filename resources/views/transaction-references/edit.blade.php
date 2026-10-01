<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold text-gray-900">Edit {{ $title }}</h1></x-slot>
    <form method="POST" action="{{ route($routePrefix.'.update', $item) }}" class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('transaction-references._form', ['method' => 'PUT'])
    </form>
    <form method="POST" action="{{ route($routePrefix.'.destroy', $item) }}" class="mt-4 max-w-3xl text-right" onsubmit="return confirm('{{ $supportsActive ? 'Nonaktifkan' : 'Hapus' }} data ini?')">
        @csrf
        @method('DELETE')
        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">{{ $supportsActive ? 'Nonaktifkan' : 'Hapus' }}</button>
    </form>
</x-app-layout>
