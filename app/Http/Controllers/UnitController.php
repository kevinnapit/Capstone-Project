<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('units.index', [
            'units' => Unit::query()->withCount('products')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('units.create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        Unit::query()->create($request->validated());

        return redirect()->route('units.index')->with('status', 'Satuan berhasil dibuat.');
    }

    public function edit(Unit $unit): View
    {
        $this->authorize('master-data.manage');

        return view('units.edit', ['unit' => $unit]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $unit->update($request->validated());

        return redirect()->route('units.index')->with('status', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $this->authorize('master-data.manage');

        if ($unit->products()->exists()) {
            return back()->withErrors(['unit' => 'Satuan masih digunakan oleh produk dan tidak dapat dihapus.']);
        }

        $unit->delete();

        return redirect()->route('units.index')->with('status', 'Satuan berhasil dihapus.');
    }
}
