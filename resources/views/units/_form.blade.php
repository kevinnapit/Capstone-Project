@csrf
@if ($method !== 'POST')
    @method($method)
@endif
<div class="grid gap-5 sm:grid-cols-2">
    <div><x-input-label for="name" value="Nama satuan" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $unit->name ?? '')" required autofocus /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div>
    <div><x-input-label for="symbol" value="Simbol" /><x-text-input id="symbol" name="symbol" class="mt-1 block w-full" :value="old('symbol', $unit->symbol ?? '')" required /><p class="mt-1 text-xs text-gray-500">Contoh: pcs, rim, lbr.</p><x-input-error :messages="$errors->get('symbol')" class="mt-2" /></div>
</div>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('units.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a><button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Simpan</button></div>
