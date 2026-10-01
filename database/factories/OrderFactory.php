<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\OrderChannel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(fake()->unique()->bothify('??##')),
            'customer_id' => fake()->boolean(70) ? Customer::factory() : null,
            'channel_id' => OrderChannel::factory(),
            'created_by' => User::factory(),
            'status' => OrderStatus::Draft,
            'ordered_at' => now(),
            'subtotal' => 0,
            'discount_amount' => 0,
            'grand_total' => 0,
            'paid_amount' => 0,
            'notes' => fake()->optional()->sentence(),
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }
}
