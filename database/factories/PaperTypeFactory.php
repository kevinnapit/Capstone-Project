<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaperTypeFactory extends Factory
{
    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->bothify('P##'));

        return [
            'inventory_product_id' => Product::factory(),
            'code' => $code,
            'name' => "Kertas {$code}",
            'is_active' => true,
        ];
    }
}
