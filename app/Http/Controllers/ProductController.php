<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('master-data.manage');

        $search = trim((string) $request->query('search'));
        $products = Product::query()
            ->with(['category', 'unit'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('products.create', $this->formOptions());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        Product::query()->create($request->validated());

        return redirect()->route('products.index')->with('status', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('master-data.manage');

        return view('products.edit', [
            ...$this->formOptions(),
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $product->update($request->validated());

        return redirect()->route('products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('master-data.manage');

        if ((float) $product->current_stock !== 0.0) {
            return back()->withErrors(['product' => 'Produk yang masih memiliki stok tidak dapat dihapus. Nonaktifkan produk sebagai gantinya.']);
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Produk berhasil dihapus.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
        ];
    }
}
