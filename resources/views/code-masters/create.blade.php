<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold text-gray-900">Tambah {{ $title }}</h1></x-slot>
    <form method="POST" action="{{ route($routePrefix.'.store') }}" class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">@include('code-masters._form', ['method' => 'POST'])</form>
</x-app-layout>
