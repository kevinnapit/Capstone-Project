<?php

namespace App\Actions\Inventory;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustStockAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function execute(Product $product, User $user, string $direction, float $quantity, string $notes): StockMovement
    {
        return DB::transaction(function () use ($product, $user, $direction, $quantity, $notes): StockMovement {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $before = round((float) $lockedProduct->current_stock, 2);
            $quantity = round($quantity, 2);
            $after = $direction === 'in' ? $before + $quantity : $before - $quantity;

            if ($quantity <= 0 || $after < 0) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah penyesuaian tidak valid atau menyebabkan stok negatif.']);
            }

            $lockedProduct->update(['current_stock' => $after]);

            $movement = StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'movement_type' => 'adjustment',
                'direction' => $direction,
                'quantity' => $quantity,
                'unit_cost' => $lockedProduct->purchase_price,
                'stock_before' => $before,
                'stock_after' => $after,
                'reference_type' => 'manual_adjustment',
                'notes' => trim($notes),
                'created_by' => $user->id,
                'occurred_at' => now(),
            ]);

            $this->audit->log($user, 'stock.adjusted', $lockedProduct, [
                'current_stock' => $before,
            ], [
                'current_stock' => $after,
            ], trim($notes));

            return $movement;
        });
    }
}
