<?php

namespace App\Services;

use App\Enums\OrderItemType;
use App\Enums\SideMode;
use App\Models\Product;
use App\Models\ServicePrice;
use Illuminate\Validation\ValidationException;

class OrderPricingService
{
    public function productLine(Product $product, mixed $quantity): array
    {
        $quantity = $this->positiveDecimal($quantity, 'items.quantity');

        if (! $product->is_active) {
            throw ValidationException::withMessages(['items' => "Produk {$product->name} sedang tidak aktif."]);
        }

        return [
            'item' => [
                'type' => OrderItemType::Product,
                'product_id' => $product->id,
                'service_price_id' => null,
                'item_name_snapshot' => $product->name,
                'quantity_billed' => $quantity,
                'unit_price_snapshot' => $product->selling_price,
                'subtotal' => $this->multiply($product->selling_price, $quantity),
            ],
            'service_detail' => null,
        ];
    }

    public function serviceLine(ServicePrice $servicePrice, mixed $pages, mixed $copies): array
    {
        $pages = $this->positiveInteger($pages, 'items.pages');
        $copies = $this->positiveInteger($copies, 'items.copies');

        $servicePrice->loadMissing(['serviceType', 'paperType', 'printMode']);

        if (! $servicePrice->is_active || ! $servicePrice->serviceType->is_active || ! $servicePrice->paperType->is_active) {
            throw ValidationException::withMessages(['items' => 'Tarif atau master layanan sedang tidak aktif.']);
        }

        $today = today();
        if ($servicePrice->effective_from->isAfter($today) || ($servicePrice->effective_until && $servicePrice->effective_until->isBefore($today))) {
            throw ValidationException::withMessages(['items' => 'Tarif layanan tidak berlaku pada tanggal pesanan.']);
        }

        $sheets = $servicePrice->side_mode === SideMode::Duplex
            ? (int) ceil($pages / 2) * $copies
            : $pages * $copies;

        $variant = $servicePrice->printMode?->name ?? $servicePrice->side_mode->label();
        $name = "{$servicePrice->serviceType->name} - {$servicePrice->paperType->code} - {$variant}";

        return [
            'item' => [
                'type' => OrderItemType::Service,
                'product_id' => null,
                'service_price_id' => $servicePrice->id,
                'item_name_snapshot' => $name,
                'quantity_billed' => $sheets,
                'unit_price_snapshot' => $servicePrice->price,
                'subtotal' => $this->multiply($servicePrice->price, $sheets),
            ],
            'service_detail' => [
                'paper_type_id' => $servicePrice->paper_type_id,
                'pages' => $pages,
                'copies' => $copies,
                'sheets_billed' => $sheets,
                'sheets_consumed' => 0,
            ],
        ];
    }

    public function sum(array $amounts): string
    {
        return number_format(array_sum(array_map('floatval', $amounts)), 2, '.', '');
    }

    private function multiply(mixed $price, mixed $quantity): string
    {
        return number_format(round((float) $price * (float) $quantity, 2), 2, '.', '');
    }

    private function positiveDecimal(mixed $value, string $field): string
    {
        if (! is_numeric($value) || (float) $value <= 0) {
            throw ValidationException::withMessages([$field => 'Kuantitas harus lebih besar dari 0.']);
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function positiveInteger(mixed $value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value <= 0) {
            throw ValidationException::withMessages([$field => 'Nilai harus berupa bilangan bulat lebih besar dari 0.']);
        }

        return (int) $value;
    }
}
