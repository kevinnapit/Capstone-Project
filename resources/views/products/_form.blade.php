@csrf
@if ($method !== 'POST')
    @method($method)
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <div><x-input-label for="sku" value="SKU" /><x-text-input id="sku" name="sku" class="mt-1 block w-full uppercase" :value="old('sku', $product->sku ?? '')" required autofocus /><x-input-error :messages="$errors->get('sku')" class="mt-2" /></div>
    <div><x-input-label for="name" value="Nama produk" /><x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $product->name ?? '')" required /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div>
    <div><x-input-label for="category_id" value="Kategori" /><select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required><option value="">Pilih kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><x-input-error :messages="$errors->get('category_id')" class="mt-2" /></div>
    <div><x-input-label for="unit_id" value="Satuan" /><select id="unit_id" name="unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required><option value="">Pilih satuan</option>@foreach ($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_id', $product->unit_id ?? '') === (string) $unit->id)>{{ $unit->name }} ({{ $unit->symbol }})</option>@endforeach</select><x-input-error :messages="$errors->get('unit_id')" class="mt-2" /></div>
    <div><x-input-label for="purchase_price" value="Harga beli" /><x-text-input id="purchase_price" name="purchase_price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('purchase_price', $product->purchase_price ?? 0)" required /><x-input-error :messages="$errors->get('purchase_price')" class="mt-2" /></div>
    <div><x-input-label for="selling_price" value="Harga jual" /><x-text-input id="selling_price" name="selling_price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('selling_price', $product->selling_price ?? 0)" required /><x-input-error :messages="$errors->get('selling_price')" class="mt-2" /></div>
    @if (! isset($product))
        <div><x-input-label for="current_stock" value="Stok awal" /><x-text-input id="current_stock" name="current_stock" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('current_stock', 0)" required /><p class="mt-1 text-xs text-gray-500">Setelah dibuat, perubahan stok dilakukan melalui penyesuaian stok.</p><x-input-error :messages="$errors->get('current_stock')" class="mt-2" /></div>
    @else
        <div><x-input-label value="Stok saat ini" /><div class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">{{ rtrim(rtrim($product->current_stock, '0'), '.') }} {{ $product->unit->symbol }}</div><p class="mt-1 text-xs text-gray-500">Stok tidak dapat diubah dari form produk.</p></div>
    @endif
    <div><x-input-label for="minimum_stock" value="Batas stok minimum" /><x-text-input id="minimum_stock" name="minimum_stock" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('minimum_stock', $product->minimum_stock ?? 0)" required /><x-input-error :messages="$errors->get('minimum_stock')" class="mt-2" /></div>
</div>

<input type="hidden" name="is_active" value="0">
<label class="mt-5 inline-flex items-center"><input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500" @checked((bool) old('is_active', $product->is_active ?? true))><span class="ml-2 text-sm text-gray-700">Produk aktif dan dapat digunakan dalam transaksi</span></label>
<x-input-error :messages="$errors->get('is_active')" class="mt-2" />

<div class="mt-6 flex justify-end gap-3"><a href="{{ route('products.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a><button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Simpan</button></div>
