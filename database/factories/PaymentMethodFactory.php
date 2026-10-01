<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PAY-###')),
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ];
    }
}
