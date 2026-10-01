<?php

namespace App\Enums;

enum SideMode: string
{
    case None = 'none';
    case SingleSided = 'single_sided';
    case Duplex = 'duplex';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Tidak berlaku',
            self::SingleSided => 'Satu sisi',
            self::Duplex => 'Timbal balik',
        };
    }
}
