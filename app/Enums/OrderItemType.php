<?php

namespace App\Enums;

enum OrderItemType: string
{
    case Product = 'product';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Produk',
            self::Service => 'Layanan',
        };
    }
}
