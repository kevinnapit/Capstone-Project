<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Manajemen Pengguna</h1>
                <p class="mt-1 text-sm text-gray-500">Akun baru hanya dapat dibuat oleh pengguna berwenang.</p>
            </div>
            <a href="{{ route('users.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Tambah pengguna</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr><th class="px-6 py-3">Nama</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Role</th><th class="px-6 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @forelse ($user->roles as $role)
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">{{ $role->name }}</span>
                                @empty
                                    <span class="text-gray-400">Belum ada role</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('users.edit', $user) }}" class="font-medium text-blue-700 hover:underline">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-6 py-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
