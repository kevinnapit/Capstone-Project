<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('code-masters.index', ['items' => ServiceType::withCount('servicePrices')->orderBy('name')->paginate(10), 'title' => 'Jenis Layanan', 'routePrefix' => 'service-types']);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('code-masters.create', ['title' => 'Jenis Layanan', 'routePrefix' => 'service-types']);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        ServiceType::create($this->validated($request));

        return to_route('service-types.index')->with('status', 'Jenis layanan berhasil dibuat.');
    }

    public function edit(ServiceType $serviceType): View
    {
        $this->authorize('master-data.manage');

        return view('code-masters.edit', ['item' => $serviceType, 'title' => 'Jenis Layanan', 'routePrefix' => 'service-types']);
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $serviceType->update($this->validated($request, $serviceType));

        return to_route('service-types.index')->with('status', 'Jenis layanan berhasil diperbarui.');
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        $this->authorize('master-data.manage');
        if ($serviceType->servicePrices()->exists()) {
            return back()->withErrors(['item' => 'Data sudah memiliki tarif dan tidak dapat dihapus.']);
        }
        $serviceType->delete();

        return to_route('service-types.index')->with('status', 'Jenis layanan berhasil dihapus.');
    }

    private function validated(Request $request, ?ServiceType $item = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code)), 'is_active' => $request->boolean('is_active')]);

        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('service_types')->ignore($item)],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
