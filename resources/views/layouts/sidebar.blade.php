<aside id="app-sidebar" class="fixed left-0 top-0 z-40 h-screen w-64 -translate-x-full border-r border-gray-200 bg-white pt-16 transition-transform sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full overflow-y-auto px-3 py-5">
        <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Menu utama</p>

        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('dashboard') }}" class="group flex items-center rounded-lg p-3 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5Z" /></svg>
                    <span class="ml-3">Dashboard</span>
                </a>
            </li>
            <li>
                <span class="flex cursor-not-allowed items-center rounded-lg p-3 text-gray-400" title="Modul belum tersedia">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7V5a3 3 0 0 1 6 0v2m-7 0h14l-1 14H6L5 7Z" /></svg>
                    <span class="ml-3">Pesanan</span>
                </span>
            </li>
            <li>
                <span class="flex cursor-not-allowed items-center rounded-lg p-3 text-gray-400" title="Modul belum tersedia">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    <span class="ml-3">Master Data</span>
                </span>
            </li>
            <li>
                <span class="flex cursor-not-allowed items-center rounded-lg p-3 text-gray-400" title="Modul belum tersedia">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V9m5 10V5m5 14v-7m5 7V3" /></svg>
                    <span class="ml-3">Laporan</span>
                </span>
            </li>
        </ul>

        <div class="my-5 border-t border-gray-200"></div>
        @canany(['users.manage', 'roles.manage'])
            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Administrasi</p>
        @endcanany

        <ul class="space-y-2 font-medium">
            @can('users.manage')
                <li>
                    <a href="{{ route('users.index') }}" class="flex items-center rounded-lg p-3 {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8 0 2 2 4-4" /></svg>
                        <span class="ml-3">Pengguna</span>
                    </a>
                </li>
            @endcan
            @can('roles.manage')
                <li>
                    <a href="{{ route('roles.index') }}" class="flex items-center rounded-lg p-3 {{ request()->routeIs('roles.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3 4 7v5c0 5 3.4 8.7 8 10 4.6-1.3 8-5 8-10V7l-8-4Zm-2 9 1.5 1.5L15 10" /></svg>
                        <span class="ml-3">Role & Permission</span>
                    </a>
                </li>
            @endcan
            <li>
                <a href="{{ route('profile.edit') }}" class="flex items-center rounded-lg p-3 text-gray-700 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.4 0a1.7 1.7 0 0 0 .34 1.88l.06.06-2.36 2.36-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1 1.56V21h-5v-.04a1.7 1.7 0 0 0-1-1.56 1.7 1.7 0 0 0-1.88.34l-.06.06-2.36-2.36.06-.06a1.7 1.7 0 0 0 .34-1.88A1.7 1.7 0 0 0 3.04 14H3v-4h.04A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06L6.56 4.7l.06.06A1.7 1.7 0 0 0 8.5 5.1 1.7 1.7 0 0 0 9.5 3.54V3h5v.54a1.7 1.7 0 0 0 1 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.36 2.36-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.56 1H21v4h-.04a1.7 1.7 0 0 0-1.56 1.5Z" /></svg>
                    <span class="ml-3">Profil</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
