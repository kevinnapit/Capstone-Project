<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;
use RuntimeException;

class OrderNumberGenerator
{
    public function generate(CarbonInterface $orderedAt): string
    {
        $prefix = 'ORD-'.$orderedAt->format('Ymd').'-';
        $lastNumber = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        if ($sequence > 9999) {
            throw new RuntimeException('Batas nomor pesanan harian telah tercapai.');
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
