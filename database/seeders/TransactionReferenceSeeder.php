<?php

namespace Database\Seeders;

use App\Models\OrderChannel;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class TransactionReferenceSeeder extends Seeder
{
    public function run(): void
    {
        OrderChannel::query()->updateOrCreate(['code' => 'WALK_IN'], ['name' => 'Datang langsung']);
        OrderChannel::query()->updateOrCreate(['code' => 'WHATSAPP'], ['name' => 'WhatsApp']);

        PaymentMethod::query()->updateOrCreate(['code' => 'CASH'], ['name' => 'Tunai', 'is_active' => true]);
        PaymentMethod::query()->updateOrCreate(['code' => 'TRANSFER'], ['name' => 'Transfer bank', 'is_active' => true]);
        PaymentMethod::query()->updateOrCreate(['code' => 'QRIS'], ['name' => 'QRIS', 'is_active' => true]);
    }
}
