<?php

namespace App\Http\Controllers;

use App\Models\OrderChannel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderChannelController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('transaction-references.index', [
            'items' => OrderChannel::query()->orderBy('name')->paginate(10),
            'title' => 'Channel Pesanan',
            'description' => 'Sumber pesanan yang diterima oleh usaha.',
            'routePrefix' => 'order-channels',
            'supportsActive' => false,
        ]);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('transaction-references.create', $this->viewData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        OrderChannel::query()->create($this->validated($request));

        return to_route('order-channels.index')->with('status', 'Channel pesanan berhasil dibuat.');
    }

    public function edit(OrderChannel $orderChannel): View
    {
        $this->authorize('master-data.manage');

        return view('transaction-references.edit', [...$this->viewData(), 'item' => $orderChannel]);
    }

    public function update(Request $request, OrderChannel $orderChannel): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $orderChannel->update($this->validated($request, $orderChannel));

        return to_route('order-channels.index')->with('status', 'Channel pesanan berhasil diperbarui.');
    }

    public function destroy(OrderChannel $orderChannel): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $orderChannel->delete();

        return to_route('order-channels.index')->with('status', 'Channel pesanan berhasil dihapus.');
    }

    private function validated(Request $request, ?OrderChannel $item = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('order_channels')->ignore($item)],
            'name' => ['required', 'string', 'max:100'],
        ]);
    }

    private function viewData(): array
    {
        return ['title' => 'Channel Pesanan', 'routePrefix' => 'order-channels', 'supportsActive' => false];
    }
}
