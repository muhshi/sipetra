<?php

namespace App\Enums;

enum ClientAccessPolicy: string
{
    case Open = 'open';
    case Restricted = 'restricted';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Restricted => 'Restricted',
        };
    }
}
