<?php

namespace App\Http\Controllers;

use App\Actions\Inventory\AdjustStockAction;
use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(): View
    {
        $this->authorize('stock.adjust');

        return view('stock-movements.index', [
            'products' => Product::query()->with('unit')->orderBy('name')->get(),
            'movements' => StockMovement::query()->with(['product.unit', 'creator'])->latest('occurred_at')->paginate(20),
        ]);
    }

    public function store(StoreStockAdjustmentRequest $request, AdjustStockAction $action): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::query()->findOrFail($data['product_id']);
        $action->execute($product, $request->user(), $data['direction'], (float) $data['quantity'], $data['notes']);

        return to_route('stock-movements.index')->with('status', 'Penyesuaian stok berhasil dicatat.');
    }
}
