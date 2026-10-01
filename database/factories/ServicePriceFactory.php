<?php

namespace Database\Factories;

use App\Enums\SideMode;
use App\Models\PaperType;
use App\Models\PrintMode;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_type_id' => ServiceType::factory(),
            'paper_type_id' => PaperType::factory(),
            'print_mode_id' => PrintMode::factory(),
            'side_mode' => SideMode::None,
            'price' => fake()->randomElement([200, 300, 500, 1000]),
            'effective_from' => today(),
            'effective_until' => null,
            'is_active' => true,
        ];
    }
}
