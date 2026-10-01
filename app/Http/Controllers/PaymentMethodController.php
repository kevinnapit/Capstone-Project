<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('transaction-references.index', [
            'items' => PaymentMethod::query()->orderBy('name')->paginate(10),
            'title' => 'Metode Pembayaran',
            'description' => 'Cara pembayaran yang dapat dipilih pada transaksi.',
            'routePrefix' => 'payment-methods',
            'supportsActive' => true,
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
        PaymentMethod::query()->create($this->validated($request));

        return to_route('payment-methods.index')->with('status', 'Metode pembayaran berhasil dibuat.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        $this->authorize('master-data.manage');

        return view('transaction-references.edit', [...$this->viewData(), 'item' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $paymentMethod->update($this->validated($request, $paymentMethod));

        return to_route('payment-methods.index')->with('status', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $paymentMethod->update(['is_active' => false]);

        return to_route('payment-methods.index')->with('status', 'Metode pembayaran berhasil dinonaktifkan.');
    }

    private function validated(Request $request, ?PaymentMethod $item = null): array
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->code)),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('payment_methods')->ignore($item)],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function viewData(): array
    {
        return ['title' => 'Metode Pembayaran', 'routePrefix' => 'payment-methods', 'supportsActive' => true];
    }
}
