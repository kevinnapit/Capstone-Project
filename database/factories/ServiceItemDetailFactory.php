<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\PaperType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceItemDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory()->service(),
            'paper_type_id' => PaperType::factory(),
            'pages' => 2,
            'copies' => 1,
            'sheets_billed' => 2,
            'sheets_consumed' => 0,
        ];
    }
}
