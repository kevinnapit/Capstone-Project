<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'from_status' => OrderStatus::Draft,
            'to_status' => OrderStatus::Confirmed,
            'changed_by' => User::factory(),
            'reason' => null,
        ];
    }
}
