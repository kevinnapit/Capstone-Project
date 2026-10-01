<?php

namespace Database\Factories;

use App\Enums\OrderItemType;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServicePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => OrderItemType::Product,
            'product_id' => Product::factory(),
            'service_price_id' => null,
            'item_name_snapshot' => fake()->words(3, true),
            'quantity_billed' => 2,
            'unit_price_snapshot' => 1000,
            'subtotal' => 2000,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function service(): static
    {
        return $this->state(fn (): array => [
            'type' => OrderItemType::Service,
            'product_id' => null,
            'service_price_id' => ServicePrice::factory(),
        ]);
    }
}
