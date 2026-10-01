<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPaymentAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function execute(Order $order, User $receiver, array $data): Payment
    {
        return DB::transaction(function () use ($order, $receiver, $data): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (in_array($lockedOrder->status, [OrderStatus::Completed, OrderStatus::Cancelled], true)) {
                throw ValidationException::withMessages(['amount' => 'Pembayaran tidak dapat ditambahkan pada pesanan yang sudah final.']);
            }

            $method = PaymentMethod::query()->whereKey($data['payment_method_id'])->where('is_active', true)->first();
            if (! $method) {
                throw ValidationException::withMessages(['payment_method_id' => 'Metode pembayaran tidak aktif atau tidak valid.']);
            }

            $amount = round((float) $data['amount'], 2);
            $balance = $lockedOrder->balanceDue();
            if ($amount > $balance) {
                throw ValidationException::withMessages(['amount' => 'Jumlah pembayaran melebihi sisa tagihan.']);
            }

            $payment = $lockedOrder->payments()->create([
                'payment_method_id' => $method->id,
                'received_by' => $receiver->id,
                'amount' => $amount,
                'status' => 'paid',
                'reference_number' => $data['reference_number'] ?? null,
                'paid_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $lockedOrder->update(['paid_amount' => round((float) $lockedOrder->paid_amount + $amount, 2)]);

            $this->audit->log($receiver, 'payment.created', $payment, null, [
                'order_id' => $lockedOrder->id,
                'amount' => $amount,
                'payment_method_id' => $method->id,
            ], "Pembayaran {$lockedOrder->order_number} dicatat.");

            return $payment->load(['paymentMethod', 'receiver']);
        });
    }
}
