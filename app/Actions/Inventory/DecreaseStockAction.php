<?php

namespace App\Actions\Inventory;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DecreaseStockAction
{
    public function execute(Product $product, float $quantity, string $type, User $user, string $referenceType, int $referenceId, ?string $notes = null): StockMovement
    {
        $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->getKey());
        $before = round((float) $lockedProduct->current_stock, 2);
        $quantity = round($quantity, 2);

        if ($quantity <= 0) {
            throw ValidationException::withMessages(['stock' => 'Jumlah pemakaian stok harus lebih besar dari nol.']);
        }

        if ($quantity > $before) {
            throw ValidationException::withMessages([
                'stock' => "Stok {$lockedProduct->name} tidak mencukupi. Tersedia {$before}, diperlukan {$quantity}.",
            ]);
        }

        $after = round($before - $quantity, 2);
        $lockedProduct->update(['current_stock' => $after]);

        return StockMovement::query()->create([
            'product_id' => $lockedProduct->id,
            'movement_type' => $type,
            'direction' => 'out',
            'quantity' => $quantity,
            'unit_cost' => $lockedProduct->purchase_price,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $user->id,
            'occurred_at' => now(),
        ]);
    }
}
