<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeOrderStatusAction
{
    public function execute(Order $order, User $user, OrderStatus $targetStatus, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $user, $targetStatus, $reason): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $currentStatus = $lockedOrder->status;

            if (! $currentStatus->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => "Status {$currentStatus->label()} tidak dapat diubah menjadi {$targetStatus->label()}.",
                ]);
            }

            if ($targetStatus === OrderStatus::Cancelled && blank($reason)) {
                throw ValidationException::withMessages([
                    'reason' => 'Alasan pembatalan wajib diisi.',
                ]);
            }

            $lockedOrder->update([
                'status' => $targetStatus,
                'completed_at' => $targetStatus === OrderStatus::Completed ? now() : $lockedOrder->completed_at,
                'cancelled_at' => $targetStatus === OrderStatus::Cancelled ? now() : $lockedOrder->cancelled_at,
            ]);

            $lockedOrder->statusHistories()->create([
                'from_status' => $currentStatus,
                'to_status' => $targetStatus,
                'changed_by' => $user->getKey(),
                'reason' => filled($reason) ? trim($reason) : null,
            ]);

            return $lockedOrder->fresh(['statusHistories.changedBy']);
        });
    }
}
