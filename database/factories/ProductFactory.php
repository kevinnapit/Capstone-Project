<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(2, 500, 100000);

        return [
            'category_id' => ProductCategory::factory(),
            'unit_id' => Unit::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'name' => fake()->words(3, true),
            'purchase_price' => $purchasePrice,
            'selling_price' => $purchasePrice * 1.2,
            'current_stock' => fake()->randomFloat(2, 0, 100),
            'minimum_stock' => fake()->randomFloat(2, 0, 10),
            'is_active' => true,
        ];
    }
}
