<?php

namespace App\Enums;

enum IdentityType: string
{
    case Pegawai = 'pegawai';
    case Mitra = 'mitra';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Pegawai => 'Pegawai BPS (PNS/PPPK)',
            self::Mitra => 'Mitra Statistik',
            self::Admin => 'Administrator',
        };
    }

    /**
     * Identifier field name for this identity type.
     */
    public function identifierField(): string
    {
        return match ($this) {
            self::Pegawai => 'nip',
            self::Mitra => 'sobat_id',
            self::Admin => 'email',
        };
    }
}
