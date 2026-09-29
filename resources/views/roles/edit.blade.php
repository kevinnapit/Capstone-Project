<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold text-gray-900">Edit Role</h1></x-slot>
    <form method="POST" action="{{ route('roles.update', $managedRole) }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('roles._form', ['method' => 'PUT'])
    </form>

    @if (! in_array($managedRole->name, ['admin', 'employee'], true))
        <form method="POST" action="{{ route('roles.destroy', $managedRole) }}" class="mt-4 text-right" onsubmit="return confirm('Hapus role ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Hapus role</button>
        </form>
    @endif
</x-app-layout>
