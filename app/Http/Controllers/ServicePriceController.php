<?php

namespace App\Http\Controllers;

use App\Enums\SideMode;
use App\Http\Requests\StoreServicePriceRequest;
use App\Http\Requests\UpdateServicePriceRequest;
use App\Models\PaperType;
use App\Models\PrintMode;
use App\Models\ServicePrice;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServicePriceController extends Controller
{
    public function index(): View
    {
        $this->authorize('master-data.manage');

        return view('service-prices.index', [
            'prices' => ServicePrice::with(['serviceType', 'paperType', 'printMode'])
                ->orderByDesc('effective_from')->orderBy('service_type_id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('master-data.manage');

        return view('service-prices.create', $this->formOptions());
    }

    public function store(StoreServicePriceRequest $request): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $data = $request->validated();
        $this->validateCombination($data);
        ServicePrice::create($data);

        return to_route('service-prices.index')->with('status', 'Tarif layanan berhasil dibuat.');
    }

    public function edit(ServicePrice $servicePrice): View
    {
        $this->authorize('master-data.manage');

        return view('service-prices.edit', [...$this->formOptions(), 'servicePrice' => $servicePrice]);
    }

    public function update(UpdateServicePriceRequest $request, ServicePrice $servicePrice): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $data = $request->validated();
        $this->validateCombination($data, $servicePrice);
        $servicePrice->update($data);

        return to_route('service-prices.index')->with('status', 'Tarif layanan berhasil diperbarui.');
    }

    public function destroy(ServicePrice $servicePrice): RedirectResponse
    {
        $this->authorize('master-data.manage');
        $servicePrice->update([
            'is_active' => false,
            'effective_until' => $servicePrice->effective_until ?? today(),
        ]);

        return to_route('service-prices.index')->with('status', 'Tarif layanan berhasil dinonaktifkan.');
    }

    private function validateCombination(array $data, ?ServicePrice $except = null): void
    {
        $service = ServiceType::findOrFail($data['service_type_id']);

        if ($service->code === 'PHOTOCOPY' && ($data['print_mode_id'] !== null || $data['side_mode'] === SideMode::None->value)) {
            throw ValidationException::withMessages(['service_type_id' => 'Fotokopi harus memilih mode sisi dan tidak memakai mode cetak.']);
        }

        if ($service->code === 'PRINT' && ($data['print_mode_id'] === null || $data['side_mode'] !== SideMode::None->value)) {
            throw ValidationException::withMessages(['service_type_id' => 'Cetak harus memilih mode cetak dengan mode sisi Tidak berlaku.']);
        }

        $duplicate = ServicePrice::query()
            ->where('service_type_id', $data['service_type_id'])
            ->where('paper_type_id', $data['paper_type_id'])
            ->where('side_mode', $data['side_mode'])
            ->whereDate('effective_from', $data['effective_from'])
            ->when($data['print_mode_id'] === null, fn ($query) => $query->whereNull('print_mode_id'), fn ($query) => $query->where('print_mode_id', $data['print_mode_id']))
            ->when($except, fn ($query) => $query->where($except->getKeyName(), '!=', $except->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['effective_from' => 'Tarif untuk kombinasi dan tanggal efektif tersebut sudah ada.']);
        }
    }

    private function formOptions(): array
    {
        return [
            'serviceTypes' => ServiceType::where('is_active', true)->orderBy('name')->get(),
            'paperTypes' => PaperType::where('is_active', true)->orderBy('code')->get(),
            'printModes' => PrintMode::where('is_active', true)->orderBy('name')->get(),
            'sideModes' => SideMode::cases(),
        ];
    }
}
