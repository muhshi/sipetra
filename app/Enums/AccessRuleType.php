<?php

namespace App\Enums;

enum AccessRuleType: string
{
    case User = 'user';
    case SipetraRole = 'sipetra_role';
    case IdentityType = 'identity_type';

    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::SipetraRole => 'Sipetra Role',
            self::IdentityType => 'Identity Type',
        };
    }
}
