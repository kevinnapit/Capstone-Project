<?php

namespace Database\Seeders;

use App\Enums\SideMode;
use App\Models\PaperType;
use App\Models\PrintMode;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServicePrice;
use App\Models\ServiceType;
use App\Models\Unit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class ServiceMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $category = ProductCategory::query()->where('name', 'Bahan Fotokopi')->firstOrFail();
        $unit = Unit::query()->where('symbol', 'lbr')->firstOrFail();

        $paperTypes = collect(['A4', 'A3', 'B5', 'B4', 'F4'])->mapWithKeys(function (string $code) use ($category, $unit): array {
            $product = Product::query()->updateOrCreate(
                ['sku' => "PAPER-{$code}"],
                [
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => "Kertas {$code}",
                    'purchase_price' => 0,
                    'selling_price' => 0,
                    'minimum_stock' => 0,
                    'is_active' => true,
                ]
            );

            $paperType = PaperType::query()->updateOrCreate(
                ['code' => $code],
                ['inventory_product_id' => $product->id, 'name' => "Kertas {$code}", 'is_active' => true]
            );

            return [$code => $paperType];
        });

        $photocopy = ServiceType::query()->updateOrCreate(['code' => 'PHOTOCOPY'], ['name' => 'Fotokopi', 'is_active' => true]);
        $print = ServiceType::query()->updateOrCreate(['code' => 'PRINT'], ['name' => 'Cetak', 'is_active' => true]);

        $printModes = collect([
            'BW' => 'Hitam putih',
            'MEDIUM_COLOR' => 'Warna sedang',
            'FULL_COLOR' => 'Full warna',
        ])->mapWithKeys(fn (string $name, string $code): array => [
            $code => PrintMode::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]),
        ]);

        $effectiveFrom = '2026-01-01';
        $photocopyPrices = [
            'A4' => [200, 400], 'A3' => [400, 800], 'B5' => [175, 350],
            'B4' => [350, 700], 'F4' => [200, 400],
        ];

        foreach ($photocopyPrices as $paperCode => [$single, $duplex]) {
            $this->savePrice($photocopy, $paperTypes[$paperCode], null, SideMode::SingleSided, $single, $effectiveFrom);
            $this->savePrice($photocopy, $paperTypes[$paperCode], null, SideMode::Duplex, $duplex, $effectiveFrom);
        }

        $printPrices = [
            'A4' => ['BW' => 300, 'MEDIUM_COLOR' => 500, 'FULL_COLOR' => 1000],
            'F4' => ['BW' => 350, 'MEDIUM_COLOR' => 600, 'FULL_COLOR' => 1200],
        ];

        foreach ($printPrices as $paperCode => $prices) {
            foreach ($prices as $modeCode => $price) {
                $this->savePrice($print, $paperTypes[$paperCode], $printModes[$modeCode], SideMode::None, $price, $effectiveFrom);
            }
        }
    }

    private function savePrice(ServiceType $service, PaperType $paper, ?PrintMode $mode, SideMode $sideMode, int $price, string $effectiveFrom): void
    {
        $effectiveDate = CarbonImmutable::parse($effectiveFrom)->startOfDay();

        ServicePrice::query()->updateOrCreate([
            'service_type_id' => $service->id,
            'paper_type_id' => $paper->id,
            'print_mode_id' => $mode?->id,
            'side_mode' => $sideMode->value,
            'effective_from' => $effectiveDate,
        ], [
            'price' => $price,
            'effective_until' => null,
            'is_active' => true,
        ]);
    }
}
