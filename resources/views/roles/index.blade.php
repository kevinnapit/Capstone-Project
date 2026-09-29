<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h1 class="text-xl font-semibold text-gray-900">Role & Permission</h1><p class="mt-1 text-sm text-gray-500">Atur kumpulan hak akses untuk setiap jenis pengguna.</p></div>
            <a href="{{ route('roles.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Tambah role</a>
        </div>
    </x-slot>

    @if (session('status'))<div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
    @if ($errors->has('role'))<div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('role') }}</div>@endif

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($roles as $role)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div><h2 class="text-lg font-semibold text-gray-900">{{ $role->name }}</h2><p class="text-sm text-gray-500">{{ $role->users_count }} pengguna</p></div>
                    <a href="{{ route('roles.edit', $role) }}" class="text-sm font-medium text-blue-700 hover:underline">Edit</a>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($role->permissions as $permission)
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-700">{{ $permission->name }}</span>
                    @empty
                        <span class="text-sm text-gray-400">Belum memiliki permission.</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
