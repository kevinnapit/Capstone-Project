<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-semibold text-gray-900">Audit Log</h1><p class="mt-1 text-sm text-gray-500">Catatan append-only untuk aktivitas penting pengguna.</p></div></x-slot>

    <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
        <x-searchable-select name="action" :options="$actions->mapWithKeys(fn ($action) => [$action => $action])" :selected="$selectedAction" placeholder="Semua aksi" />
        <x-searchable-select name="user_id" :options="$users->pluck('name', 'id')" :selected="$selectedUser" placeholder="Semua pengguna" />
        <button class="rounded-lg bg-gray-800 px-5 py-2 text-sm font-medium text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="w-full text-left text-sm text-gray-600"><thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Pengguna</th><th class="px-5 py-3">Aksi</th><th class="px-5 py-3">Entitas</th><th class="px-5 py-3">Keterangan</th><th class="px-5 py-3">Perubahan</th></tr></thead><tbody class="divide-y divide-gray-200">
        @forelse ($logs as $log)
            <tr class="align-top"><td class="whitespace-nowrap px-5 py-4">{{ $log->created_at->format('d/m/Y H:i:s') }}</td><td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $log->user?->name ?? 'Sistem' }}</p><p class="text-xs text-gray-500">{{ $log->ip_address }}</p></td><td class="px-5 py-4"><span class="rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">{{ $log->action }}</span></td><td class="px-5 py-4"><p>{{ class_basename($log->auditable_type) }}</p><p class="text-xs text-gray-500">ID {{ $log->auditable_id ?? '-' }}</p></td><td class="max-w-xs px-5 py-4">{{ $log->description }}</td><td class="max-w-sm px-5 py-4"><details><summary class="cursor-pointer text-xs text-blue-700">Lihat data</summary><pre class="mt-2 max-h-48 overflow-auto rounded bg-gray-50 p-2 text-xs">{{ json_encode(['before' => $log->old_values, 'after' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></td></tr>
        @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada audit log.</td></tr>
        @endforelse
    </tbody></table></div><div class="border-t border-gray-200 px-5 py-4">{{ $logs->links() }}</div></div>
</x-app-layout>
