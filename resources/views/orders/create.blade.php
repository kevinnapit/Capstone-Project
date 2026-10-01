<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-semibold text-gray-900">Buat Pesanan Draft</h1></x-slot>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">
            <p class="font-medium">Periksa kembali data pesanan:</p>
            <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('orders.store') }}"
        x-data="{
            products: {{ Js::from($productOptions) }},
            services: {{ Js::from($serviceOptions) }},
            items: {{ Js::from(old('items', [['type' => 'product', 'quantity' => 1, 'pages' => 1, 'copies' => 1]])) }},
            addItem() { this.items.push({ type: 'product', product_id: '', service_price_id: '', quantity: 1, pages: 1, copies: 1, notes: '' }); },
            selectedProduct(item) { return this.products.find(product => product.id == item.product_id); },
            selectedService(item) { return this.services.find(service => service.id == item.service_price_id); },
            sheets(item) { const service = this.selectedService(item); if (!service) return 0; return service.side_mode === 'duplex' ? Math.ceil(Number(item.pages || 0) / 2) * Number(item.copies || 0) : Number(item.pages || 0) * Number(item.copies || 0); },
            lineTotal(item) { if (item.type === 'product') return Number(this.selectedProduct(item)?.price || 0) * Number(item.quantity || 0); return Number(this.selectedService(item)?.price || 0) * this.sheets(item); },
            total() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0); },
            rupiah(value) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0); }
        }"
        class="space-y-6"
    >
        @csrf

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Informasi Pesanan</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div><x-input-label for="channel_id" value="Channel pesanan" /><div class="mt-1"><x-searchable-select name="channel_id" :options="$channels->pluck('name', 'id')" :selected="old('channel_id')" placeholder="Pilih channel" required /></div></div>
                <div><x-input-label for="customer_id" value="Pelanggan tersimpan (opsional)" /><div class="mt-1"><x-searchable-select name="customer_id" :options="$customers->mapWithKeys(fn ($customer) => [$customer->id => $customer->name.($customer->phone ? ' — '.$customer->phone : '')])" :selected="old('customer_id')" placeholder="Pelanggan umum / baru" /></div></div>
            </div>
            <div class="mt-5 rounded-lg border border-dashed border-gray-300 p-4">
                <p class="text-sm font-medium text-gray-800">Atau buat pelanggan baru</p>
                <div class="mt-3 grid gap-4 sm:grid-cols-3"><div><x-input-label for="customer_name" value="Nama" /><x-text-input id="customer_name" name="customer[name]" class="mt-1 block w-full" :value="old('customer.name')" /></div><div><x-input-label for="customer_phone" value="Telepon" /><x-text-input id="customer_phone" name="customer[phone]" class="mt-1 block w-full" :value="old('customer.phone')" /></div><div><x-input-label for="customer_address" value="Alamat" /><x-text-input id="customer_address" name="customer[address]" class="mt-1 block w-full" :value="old('customer.address')" /></div></div>
            </div>
            <div class="mt-5"><x-input-label for="notes" value="Catatan pesanan" /><textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300">{{ old('notes') }}</textarea></div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between"><div><h2 class="text-base font-semibold text-gray-900">Item Pesanan</h2><p class="mt-1 text-sm text-gray-500">Harga final dihitung ulang oleh server.</p></div><button type="button" @click="addItem()" class="rounded-lg border border-blue-700 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">Tambah item</button></div>

            <div class="mt-5 space-y-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-4 flex items-center justify-between"><span class="text-sm font-semibold text-gray-700" x-text="`Item ${index + 1}`"></span><button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="text-sm text-red-600 hover:underline">Hapus</button></div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div><label class="text-sm font-medium text-gray-700">Tipe</label><div class="mt-1"><x-searchable-select name-expression="`items[${index}][type]`" :options="['product' => 'Produk', 'service' => 'Layanan']" model="item.type" placeholder="Pilih tipe" required /></div></div>
                            <template x-if="item.type === 'product'"><div class="sm:col-span-2"><label class="text-sm font-medium text-gray-700">Produk</label><div class="mt-1"><x-searchable-select name-expression="`items[${index}][product_id]`" dynamic-options="products" model="item.product_id" placeholder="Pilih produk" required /></div></div></template>
                            <template x-if="item.type === 'product'"><div><label class="text-sm font-medium text-gray-700">Kuantitas</label><input type="number" min="0.01" step="0.01" x-model="item.quantity" :name="`items[${index}][quantity]`" class="mt-1 block w-full rounded-md border-gray-300" required></div></template>
                            <template x-if="item.type === 'service'"><div class="sm:col-span-2"><label class="text-sm font-medium text-gray-700">Tarif layanan</label><div class="mt-1"><x-searchable-select name-expression="`items[${index}][service_price_id]`" dynamic-options="services" model="item.service_price_id" placeholder="Pilih layanan" required /></div></div></template>
                            <template x-if="item.type === 'service'"><div><label class="text-sm font-medium text-gray-700">Halaman</label><input type="number" min="1" step="1" x-model="item.pages" :name="`items[${index}][pages]`" class="mt-1 block w-full rounded-md border-gray-300" required></div></template>
                            <template x-if="item.type === 'service'"><div><label class="text-sm font-medium text-gray-700">Copy</label><input type="number" min="1" step="1" x-model="item.copies" :name="`items[${index}][copies]`" class="mt-1 block w-full rounded-md border-gray-300" required></div></template>
                            <div class="sm:col-span-2"><label class="text-sm font-medium text-gray-700">Catatan item</label><input type="text" maxlength="500" x-model="item.notes" :name="`items[${index}][notes]`" class="mt-1 block w-full rounded-md border-gray-300"></div>
                            <div class="flex items-end"><div><p class="text-xs text-gray-500" x-show="item.type === 'service'">Lembar tagihan: <span x-text="sheets(item)"></span></p><p class="text-sm font-semibold text-gray-900" x-text="rupiah(lineTotal(item))"></p></div></div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5"><span class="font-medium text-gray-700">Estimasi total</span><span class="text-2xl font-bold text-gray-900" x-text="rupiah(total())"></span></div>
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route('orders.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700">Batal</a><button type="submit" class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800">Simpan draft</button></div>
    </form>
</x-app-layout>
