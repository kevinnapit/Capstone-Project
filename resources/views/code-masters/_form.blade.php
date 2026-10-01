@csrf
@if ($method !== 'POST')
    @method($method)
@endif
<div class="grid gap-5 sm:grid-cols-2">
    <div><x-input-label for="code" value="Kode" /><x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code', $item->code ?? '')" required autofocus /><x-input-error :messages="$errors->get('code')" class="mt-2" /></div>
    <div><x-input-label for="name" value="Nama" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $item->name ?? '')" required /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div>
</div>
<input type="hidden" name="is_active" value="0">
<label class="mt-5 inline-flex items-center"><input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500" @checked((bool) old('is_active', $item->is_active ?? true))><span class="ml-2 text-sm text-gray-700">Aktif</span></label>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route($routePrefix.'.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700">Batal</a><button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white">Simpan</button></div>
