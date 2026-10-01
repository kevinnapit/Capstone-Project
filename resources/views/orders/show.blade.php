<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-gray-900">Pesanan</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-sm text-gray-700">{{ $order->order_number }}</span>
                </div>
                <h1 class="mt-1 text-xl font-semibold text-gray-900">Detail pesanan</h1>
            </div>
            @php
                $statusClass = match ($order->status) {
                    \App\Enums\OrderStatus::Draft => 'bg-gray-100 text-gray-700',
                    \App\Enums\OrderStatus::Confirmed => 'bg-blue-100 text-blue-800',
                    \App\Enums\OrderStatus::Processing => 'bg-amber-100 text-amber-800',
                    \App\Enums\OrderStatus::Completed => 'bg-green-100 text-green-800',
                    \App\Enums\OrderStatus::Cancelled => 'bg-red-100 text-red-800',
                };
            @endphp
            <span class="w-fit rounded-full px-3 py-1.5 text-sm font-medium {{ $statusClass }}">{{ $order->status->label() }}</span>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-5">
            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs uppercase text-gray-500">Nomor</p><p class="mt-1 font-semibold text-gray-900">{{ $order->order_number }}</p></div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs uppercase text-gray-500">Pelanggan</p><p class="mt-1 font-semibold text-gray-900">{{ $order->customer?->name ?? 'Pelanggan umum' }}</p></div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs uppercase text-gray-500">Channel</p><p class="mt-1 font-semibold text-gray-900">{{ $order->channel->name }}</p></div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs uppercase text-gray-500">Waktu pesan</p><p class="mt-1 font-semibold text-gray-900">{{ $order->ordered_at->format('d/m/Y H:i') }}</p></div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4"><h2 class="font-semibold text-gray-900">Item pesanan</h2></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700"><tr><th class="px-5 py-3">Item</th><th class="px-5 py-3">Detail</th><th class="px-5 py-3 text-right">Qty tagih</th><th class="px-5 py-3 text-right">Harga</th><th class="px-5 py-3 text-right">Subtotal</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $item->item_name_snapshot }}</p><p class="text-xs text-gray-500">{{ $item->type->label() }}</p></td>
                                    <td class="px-5 py-4">
                                        @if ($item->serviceDetail)
                                            <p>{{ $item->serviceDetail->pages }} halaman × {{ $item->serviceDetail->copies }} copy</p>
                                            <p class="text-xs text-gray-500">{{ $item->serviceDetail->paperType->code }} · {{ $item->serviceDetail->sheets_billed }} lembar ditagih</p>
                                        @else
                                            <p>{{ $item->product?->sku ?? '-' }} · {{ $item->product?->unit?->symbol ?? 'unit' }}</p>
                                        @endif
                                        @if ($item->notes)
                                            <p class="mt-1 text-xs italic text-gray-500">{{ $item->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">{{ number_format((float) $item->quantity_billed, 2, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">Rp {{ number_format((float) $item->unit_price_snapshot, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 bg-gray-50 px-5 py-4">
                    <div class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Diskon</span><span>Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-semibold text-gray-900"><span>Total</span><span>Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </section>

            @if ($order->notes)
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"><h2 class="font-semibold text-gray-900">Catatan</h2><p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $order->notes }}</p></section>
            @endif

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <h2 class="font-semibold text-gray-900">Riwayat pembayaran</h2>
                    @php
                        $paymentClass = match ($order->paymentStatus()) {
                            'paid' => 'bg-green-100 text-green-800',
                            'partial' => 'bg-amber-100 text-amber-800',
                            default => 'bg-red-100 text-red-800',
                        };
                        $paymentLabel = match ($order->paymentStatus()) {
                            'paid' => 'Lunas',
                            'partial' => 'Sebagian',
                            default => 'Belum dibayar',
                        };
                    @endphp
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $paymentClass }}">{{ $paymentLabel }}</span>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse ($order->payments as $payment)
                        <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="font-medium text-gray-900">{{ $payment->paymentMethod->name }}</p><p class="text-xs text-gray-500">{{ $payment->paid_at->format('d/m/Y H:i') }} · {{ $payment->receiver->name }}{{ $payment->reference_number ? ' · '.$payment->reference_number : '' }}</p></div>
                            <p class="font-semibold text-gray-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-gray-500">Belum ada pembayaran.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            @can('payments.create')
                @if (! in_array($order->status, [\App\Enums\OrderStatus::Completed, \App\Enums\OrderStatus::Cancelled], true) && $order->balanceDue() > 0)
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-gray-900">Catat pembayaran</h2>
                        <p class="mt-1 text-sm text-gray-500">Sisa tagihan: <strong class="text-gray-900">Rp {{ number_format($order->balanceDue(), 0, ',', '.') }}</strong></p>
                        <form method="POST" action="{{ route('orders.payments.store', $order) }}" class="mt-4 space-y-3">
                            @csrf
                            <x-searchable-select name="payment_method_id" :options="$paymentMethods->pluck('name', 'id')" :selected="old('payment_method_id')" placeholder="Pilih metode" required />
                            <input type="number" name="amount" value="{{ old('amount', $order->balanceDue()) }}" min="0.01" max="{{ $order->balanceDue() }}" step="0.01" required placeholder="Jumlah pembayaran" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" maxlength="100" placeholder="Nomor referensi (opsional)" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <button class="w-full rounded-lg bg-green-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-800">Simpan pembayaran</button>
                        </form>
                    </section>
                @endif
            @endcan

            @if ($availableStatuses->isNotEmpty())
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm" x-data="{ target: '{{ old('status', $availableStatuses->first()->value) }}' }">
                    <h2 class="font-semibold text-gray-900">Ubah status</h2>
                    <p class="mt-1 text-sm text-gray-500">Pilih tahap berikutnya untuk pesanan ini.</p>
                    <form method="POST" action="{{ route('orders.status.update', $order) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status berikutnya</label>
                            <x-searchable-select name="status" :options="$availableStatuses->mapWithKeys(fn ($status) => [$status->value => $status->label()])" model="target" placeholder="Pilih status" required />
                        </div>
                        <div x-show="target === 'cancelled'" x-cloak>
                            <label for="reason" class="mb-1 block text-sm font-medium text-gray-700">Alasan pembatalan <span class="text-red-600">*</span></label>
                            <textarea id="reason" name="reason" rows="3" :required="target === 'cancelled'" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('reason') }}</textarea>
                            @if ($order->items->contains(fn ($item) => $item->serviceDetail !== null))
                                <div class="mt-3 rounded-lg bg-amber-50 p-3">
                                    <p class="text-xs font-medium text-amber-900">Bahan yang sudah terpakai</p>
                                    <p class="mb-2 text-xs text-amber-700">Isi nol jika belum ada kertas yang digunakan.</p>
                                    @foreach ($order->items->filter(fn ($item) => $item->serviceDetail !== null) as $item)
                                        <label class="mb-2 block text-xs text-gray-700">{{ $item->item_name_snapshot }} (maks. {{ $item->serviceDetail->sheets_billed }} lembar)
                                            <input type="number" name="consumed_sheets[{{ $item->id }}]" value="{{ old('consumed_sheets.'.$item->id, 0) }}" min="0" max="{{ $item->serviceDetail->sheets_billed }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <button class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800">Simpan perubahan</button>
                    </form>
                </section>
            @endif

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-gray-900">Riwayat status</h2>
                <ol class="mt-4 space-y-4 border-l border-gray-200 pl-4">
                    @foreach ($order->statusHistories as $history)
                        <li class="relative">
                            <span class="absolute -left-[1.3rem] top-1 h-2.5 w-2.5 rounded-full bg-blue-600 ring-4 ring-white"></span>
                            <p class="text-sm font-medium text-gray-900">{{ $history->from_status?->label() ?? 'Pesanan dibuat' }} → {{ $history->to_status->label() }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $history->changedBy->name }} · {{ $history->created_at->format('d/m/Y H:i') }}</p>
                            @if ($history->reason)
                                <p class="mt-1 rounded-md bg-gray-50 p-2 text-xs text-gray-600">{{ $history->reason }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm">
                <h2 class="font-semibold text-gray-900">Informasi</h2>
                <dl class="mt-3 space-y-2"><div class="flex justify-between gap-3"><dt class="text-gray-500">Dibuat oleh</dt><dd class="text-right text-gray-900">{{ $order->creator->name }}</dd></div><div class="flex justify-between gap-3"><dt class="text-gray-500">Dibayar</dt><dd class="text-right text-gray-900">Rp {{ number_format((float) $order->paid_amount, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-3"><dt class="text-gray-500">Sisa</dt><dd class="text-right font-medium text-gray-900">Rp {{ number_format($order->balanceDue(), 0, ',', '.') }}</dd></div></dl>
            </section>
        </aside>
    </div>
</x-app-layout>
