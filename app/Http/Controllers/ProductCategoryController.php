<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('categories.index', [
            'categories' => ProductCategory::query()->withCount('products')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('categories.create');
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        ProductCategory::query()->create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil dibuat.');
    }

    public function edit(ProductCategory $category): View
    {
        $this->authorize('master-data.manage');

        return view('categories.edit', ['category' => $category]);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $this->authorize('master-data.manage');

        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih digunakan oleh produk dan tidak dapat dihapus.']);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Kategori berhasil dihapus.');
    }
}
