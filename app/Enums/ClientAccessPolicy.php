<?php

namespace App\Enums;

enum ClientAccessPolicy: string
{
    case Open = 'open';
    case Restricted = 'restricted';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Terbuka (semua user aktif bisa masuk)',
            self::Restricted => 'Terbatas (wajib ada rule yang cocok)',
        };
    }
}
