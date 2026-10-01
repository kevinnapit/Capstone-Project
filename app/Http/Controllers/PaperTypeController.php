<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaperTypeRequest;
use App\Http\Requests\UpdatePaperTypeRequest;
use App\Models\PaperType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaperTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('paper-types.index', ['paperTypes' => PaperType::with('inventoryProduct')->withCount('servicePrices')->orderBy('code')->paginate(10)]);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('paper-types.create', ['products' => $this->availableProducts()]);
    }

    public function store(StorePaperTypeRequest $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        PaperType::create($request->validated());

        return to_route('paper-types.index')->with('status', 'Jenis kertas berhasil dibuat.');
    }

    public function edit(PaperType $paperType): View
    {
        $this->authorize('master-data.manage');

        return view('paper-types.edit', ['paperType' => $paperType, 'products' => $this->availableProducts($paperType)]);
    }

    public function update(UpdatePaperTypeRequest $request, PaperType $paperType): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $paperType->update($request->validated());

        return to_route('paper-types.index')->with('status', 'Jenis kertas berhasil diperbarui.');
    }

    public function destroy(PaperType $paperType): RedirectResponse
    {
        $this->authorize('master-data.manage');

        if ($paperType->servicePrices()->exists()) {
            return back()->withErrors(['paper_type' => 'Jenis kertas sudah memiliki tarif dan tidak dapat dihapus.']);
        }

        $paperType->delete();

        return to_route('paper-types.index')->with('status', 'Jenis kertas berhasil dihapus.');
    }

    private function availableProducts(?PaperType $paperType = null): Collection
    {
        return Product::query()->where('is_active', true)
            ->where(function ($query) use ($paperType): void {
                $query->whereDoesntHave('paperType');
                if ($paperType) {
                    $query->orWhere('id', $paperType->inventory_product_id);
                }
            })->orderBy('name')->get();
    }
}
