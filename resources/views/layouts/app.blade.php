<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Fotokopi') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-50 font-sans text-gray-900 antialiased">
        <nav class="fixed left-0 right-0 top-0 z-50 border-b border-gray-200 bg-white">
            <div class="flex h-16 items-center justify-between px-4 lg:px-6">
                <div class="flex items-center">
                    <button type="button" data-drawer-target="app-sidebar" data-drawer-toggle="app-sidebar" aria-controls="app-sidebar" class="mr-3 inline-flex rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 sm:hidden">
                        <span class="sr-only">Buka menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-700 text-lg font-bold text-white">FC</span>
                        <span class="hidden text-lg font-semibold sm:block">Sistem Fotokopi</span>
                    </a>
                </div>

                <div class="relative">
                    <button id="user-menu-button" data-dropdown-toggle="user-menu" type="button" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-700">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden text-left sm:block">
                            <span class="block font-medium text-gray-900">{{ Auth::user()->name }}</span>
                            <span class="block text-xs text-gray-500">{{ Auth::user()->email }}</span>
                        </span>
                    </button>

                    <div id="user-menu" class="z-50 hidden w-48 divide-y divide-gray-100 rounded-lg bg-white shadow">
                        <ul class="py-2 text-sm text-gray-700">
                            <li><a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Profil</a></li>
                        </ul>
                        <form method="POST" action="{{ route('logout') }}" class="py-2">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        @include('layouts.sidebar')

        <main class="min-h-screen pt-16 sm:ml-64">
            @isset($header)
                <header class="border-b border-gray-200 bg-white px-4 py-5 lg:px-8">
                    {{ $header }}
                </header>
            @endisset

            <div class="p-4 lg:p-8">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
