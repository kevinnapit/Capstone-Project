@csrf
@if ($method !== 'POST')
    @method($method)
@endif
<div class="grid gap-5 sm:grid-cols-2">
    <div><x-input-label for="code" value="Kode kertas" /><x-text-input id="code" name="code" class="mt-1 block w-full uppercase" :value="old('code', $paperType->code ?? '')" required /><x-input-error :messages="$errors->get('code')" class="mt-2" /></div>
    <div><x-input-label for="name" value="Nama" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $paperType->name ?? '')" required /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div>
    <div class="sm:col-span-2"><x-input-label for="inventory_product_id" value="Produk persediaan" /><select id="inventory_product_id" name="inventory_product_id" class="mt-1 block w-full rounded-md border-gray-300" required><option value="">Pilih produk</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected((string) old('inventory_product_id', $paperType->inventory_product_id ?? '') === (string) $product->id)>{{ $product->name }} — {{ $product->sku }}</option>@endforeach</select><x-input-error :messages="$errors->get('inventory_product_id')" class="mt-2" /></div>
</div>
<input type="hidden" name="is_active" value="0"><label class="mt-5 inline-flex items-center"><input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-700" @checked((bool) old('is_active', $paperType->is_active ?? true))><span class="ml-2 text-sm">Aktif</span></label>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('paper-types.index') }}" class="rounded-lg border px-4 py-2 text-sm">Batal</a><button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white">Simpan</button></div>
