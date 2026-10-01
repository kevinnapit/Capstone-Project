<?php

namespace App\Http\Controllers;

use App\Models\PrintMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrintModeController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('code-masters.index', ['items' => PrintMode::withCount('servicePrices')->orderBy('name')->paginate(10), 'title' => 'Mode Cetak', 'routePrefix' => 'print-modes']);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('code-masters.create', ['title' => 'Mode Cetak', 'routePrefix' => 'print-modes']);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        PrintMode::create($this->validated($request));

        return to_route('print-modes.index')->with('status', 'Mode cetak berhasil dibuat.');
    }

    public function edit(PrintMode $printMode): View
    {
        $this->authorize('master-data.manage');

        return view('code-masters.edit', ['item' => $printMode, 'title' => 'Mode Cetak', 'routePrefix' => 'print-modes']);
    }

    public function update(Request $request, PrintMode $printMode): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $printMode->update($this->validated($request, $printMode));

        return to_route('print-modes.index')->with('status', 'Mode cetak berhasil diperbarui.');
    }

    public function destroy(PrintMode $printMode): RedirectResponse
    {
        $this->authorize('master-data.manage');
        if ($printMode->servicePrices()->exists()) {
            return back()->withErrors(['item' => 'Data sudah memiliki tarif dan tidak dapat dihapus.']);
        }
        $printMode->delete();

        return to_route('print-modes.index')->with('status', 'Mode cetak berhasil dihapus.');
    }

    private function validated(Request $request, ?PrintMode $item = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code)), 'is_active' => $request->boolean('is_active')]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('print_modes')->ignore($item)],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
