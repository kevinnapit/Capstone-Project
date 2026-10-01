<?php

namespace App\Http\Controllers;

use App\Actions\Orders\RecordPaymentAction;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Order $order, RecordPaymentAction $action): RedirectResponse
    {
        $action->execute($order, $request->user(), $request->validated());

        return to_route('orders.show', $order)->with('status', 'Pembayaran berhasil dicatat.');
    }
}
