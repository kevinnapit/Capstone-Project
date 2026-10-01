<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\DecreaseStockAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeOrderStatusAction
{
    public function __construct(private readonly DecreaseStockAction $decreaseStock, private readonly AuditLogger $audit) {}

    public function execute(Order $order, User $user, OrderStatus $targetStatus, ?string $reason = null, array $consumedSheets = []): Order
    {
        return DB::transaction(function () use ($order, $user, $targetStatus, $reason, $consumedSheets): Order {
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

            if ($targetStatus === OrderStatus::Completed) {
                if ($lockedOrder->balanceDue() > 0) {
                    throw ValidationException::withMessages([
                        'status' => 'Pesanan hanya dapat diselesaikan setelah pembayaran lunas.',
                    ]);
                }

                $this->consumeCompletedOrderStock($lockedOrder, $user);
            }

            if ($targetStatus === OrderStatus::Cancelled) {
                $this->recordCancellationUsage($lockedOrder, $user, $reason, $consumedSheets);
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

            $this->audit->log($user, 'order.status_changed', $lockedOrder, [
                'status' => $currentStatus->value,
            ], [
                'status' => $targetStatus->value,
            ], "Status {$lockedOrder->order_number} diubah menjadi {$targetStatus->label()}.");

            return $lockedOrder->fresh(['statusHistories.changedBy']);
        });
    }

    private function consumeCompletedOrderStock(Order $order, User $user): void
    {
        $order->load(['items.product', 'items.serviceDetail.paperType.inventoryProduct']);

        foreach ($order->items as $item) {
            if ($item->product_id) {
                $this->decreaseStock->execute(
                    $item->product,
                    (float) $item->quantity_billed,
                    'sale',
                    $user,
                    'order_item',
                    $item->id,
                    "Penjualan {$order->order_number}"
                );
            }

            if ($item->serviceDetail) {
                $quantity = (float) $item->serviceDetail->sheets_billed;
                $this->decreaseStock->execute(
                    $item->serviceDetail->paperType->inventoryProduct,
                    $quantity,
                    'service_usage',
                    $user,
                    'order_item',
                    $item->id,
                    "Pemakaian layanan {$order->order_number}"
                );
                $item->serviceDetail->update(['sheets_consumed' => $quantity]);
            }
        }
    }

    private function recordCancellationUsage(Order $order, User $user, string $reason, array $consumedSheets): void
    {
        $order->load(['items.serviceDetail.paperType.inventoryProduct']);
        $validItemIds = $order->items->pluck('id')->map(fn ($id) => (string) $id);

        foreach ($consumedSheets as $itemId => $quantity) {
            if (! $validItemIds->contains((string) $itemId)) {
                throw ValidationException::withMessages(['consumed_sheets' => 'Item pemakaian bahan tidak valid.']);
            }

            $item = $order->items->firstWhere('id', (int) $itemId);
            if ((int) $quantity === 0) {
                continue;
            }

            if (! $item?->serviceDetail) {
                throw ValidationException::withMessages(['consumed_sheets' => 'Pemakaian lembar hanya berlaku untuk item layanan.']);
            }

            if ((int) $quantity > $item->serviceDetail->sheets_billed) {
                throw ValidationException::withMessages([
                    "consumed_sheets.{$itemId}" => 'Pemakaian tidak boleh melebihi jumlah lembar pesanan.',
                ]);
            }

            $this->decreaseStock->execute(
                $item->serviceDetail->paperType->inventoryProduct,
                (float) $quantity,
                'waste',
                $user,
                'order_item',
                $item->id,
                "Bahan terpakai pada pembatalan {$order->order_number}"
            );
            $item->serviceDetail->update(['sheets_consumed' => $quantity]);
        }

        $order->adjustments()->create([
            'type' => 'cancellation',
            'reason' => $reason,
            'charge_amount' => 0,
            'created_by' => $user->id,
        ]);
    }
}
