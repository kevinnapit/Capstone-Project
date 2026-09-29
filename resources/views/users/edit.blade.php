<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold text-gray-900">Edit Pengguna</h1></x-slot>
    <form method="POST" action="{{ route('users.update', $managedUser) }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @include('users._form', ['method' => 'PUT'])
    </form>
</x-app-layout>
